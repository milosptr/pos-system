<?php

namespace Services;

use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use Illuminate\Support\Collection;

class SupplierPriceService
{
    public const HISTORY_LENGTH = 5;

    /**
     * The scale of unit_price and unit_price_gross in client_invoice_items.
     * Two prices that agree to this many places are the same price.
     */
    private const PRICE_SCALE = 4;

    private const PERCENT_SCALE = 1;

    public static function history(string $supplierId, ?string $search = null, bool $changedOnly = false): array
    {
        $articles = [];

        foreach (self::loadLines($supplierId, $search) as $line) {
            $key = self::articleKey($line->name, $line->unit);
            $articles[$key][] = $line;
        }

        $articles = array_map([self::class, 'buildArticle'], array_values($articles));

        if ($changedOnly) {
            $articles = array_values(array_filter($articles, fn (array $article) => $article['changed']));
        }

        usort($articles, fn (array $a, array $b) => strcmp(self::normalize($a['name']), self::normalize($b['name'])));

        return $articles;
    }

    /**
     * Newest first, and within one invoice the last line of an article first,
     * so a correction line beats the line it corrects.
     */
    private static function loadLines(string $supplierId, ?string $search): Collection
    {
        $query = ClientInvoiceItem::query()
            ->join('client_invoices', 'client_invoices.id', '=', 'client_invoice_items.client_invoice_id')
            ->where('client_invoices.client_account', $supplierId)
            ->where('client_invoices.amount', '>', 0)
            ->where('client_invoices.status', '!=', ClientInvoice::STATUS_CANCELLED)
            ->where('client_invoice_items.quantity', '>', 0)
            ->where(function ($prices) {
                $prices->where('client_invoice_items.unit_price', '>', 0)
                    ->orWhere('client_invoice_items.unit_price_gross', '>', 0);
            });

        if ($search !== null && trim($search) !== '') {
            $query->where('client_invoice_items.name', 'like', '%' . addcslashes(trim($search), '%_\\') . '%');
        }

        return $query
            ->orderByDesc('client_invoices.issue_date')
            ->orderByDesc('client_invoices.sef_id')
            ->orderByDesc('client_invoice_items.position')
            ->get([
                'client_invoice_items.client_invoice_id',
                'client_invoice_items.name',
                'client_invoice_items.unit',
                'client_invoice_items.unit_price',
                'client_invoice_items.unit_price_gross',
                'client_invoices.invoice_number',
                'client_invoices.issue_date',
            ]);
    }

    private static function buildArticle(array $lines): array
    {
        $entries = [];
        $invoicesSeen = [];

        foreach ($lines as $line) {
            if (isset($invoicesSeen[$line->client_invoice_id])) {
                continue;
            }

            $invoicesSeen[$line->client_invoice_id] = true;

            $entries[] = [
                'date' => $line->issue_date,
                'invoice_number' => $line->invoice_number,
                'unit_price' => (float) $line->unit_price,
                'unit_price_gross' => $line->unit_price_gross === null ? null : (float) $line->unit_price_gross,
            ];

            if (count($entries) === self::HISTORY_LENGTH) {
                break;
            }
        }

        $latest = $entries[0]['unit_price'];
        $previous = count($entries) > 1 ? $entries[1]['unit_price'] : null;

        return [
            'name' => $lines[0]->name,
            'unit' => $lines[0]->unit,
            'entries' => $entries,
            'change' => self::changePercent($latest, $previous),
            'changed' => $previous !== null && !self::samePrice($latest, $previous),
        ];
    }

    /**
     * An earlier price of zero — the exporter sent only cena_sa_pdv — has no
     * percentage to give, and dividing by it would throw.
     */
    private static function changePercent(float $latest, ?float $previous): ?float
    {
        if ($previous === null || $previous <= 0 || self::samePrice($latest, $previous)) {
            return null;
        }

        return round((($latest - $previous) / $previous) * 100, self::PERCENT_SCALE);
    }

    private static function samePrice(float $first, float $second): bool
    {
        return round($first, self::PRICE_SCALE) === round($second, self::PRICE_SCALE);
    }

    /**
     * The unit is part of the identity: the same name sold by kilogram and by
     * komad is not one article whose price tripled. A null unit and an empty
     * one are the same absence.
     */
    private static function articleKey(string $name, ?string $unit): string
    {
        return self::normalize($name) . '|' . self::normalize($unit);
    }

    private static function normalize(?string $value): string
    {
        return preg_replace('/\s+/u', ' ', EfaktureService::normalizeName($value));
    }
}
