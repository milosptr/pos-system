<?php

namespace Services;

use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use Illuminate\Support\Collection;

class SupplierPriceService
{
    /**
     * How many observations travel to the browser per article. The row shows
     * the last few; the rest are there for the expanded history.
     */
    public const HISTORY_LENGTH = 24;

    /**
     * The scale of unit_price and unit_price_gross in client_invoice_items.
     * Two prices that agree to this many places are the same price.
     */
    private const PRICE_SCALE = 4;

    private const PERCENT_SCALE = 1;

    public const SORT_CHANGE = 'change';
    public const SORT_NAME = 'name';

    /**
     * Ranks below every real percentage, so the articles whose price actually
     * moved come first and the rest fall into a sensible order behind them.
     */
    private const RANK_ROSE_FROM_ZERO = -1.0;
    private const RANK_UNCHANGED = -2.0;
    private const RANK_NO_HISTORY = -3.0;

    public static function history(string $supplierId, ?string $search = null, bool $changedOnly = false, string $sort = self::SORT_CHANGE): array
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

        return self::sorted($articles, $sort);
    }

    /**
     * Biggest mover first by default: alphabetical order is the one order that
     * cannot answer "what got more expensive" across two hundred articles.
     */
    private static function sorted(array $articles, string $sort): array
    {
        usort($articles, function (array $a, array $b) use ($sort) {
            $byRank = $sort === self::SORT_NAME ? 0 : self::rank($b) <=> self::rank($a);

            return $byRank !== 0 ? $byRank : strcmp(self::normalize($a['name']), self::normalize($b['name']));
        });

        return $articles;
    }

    private static function rank(array $article): float
    {
        if ($article['change'] !== null) {
            return abs($article['change']);
        }

        if ($article['changed']) {
            return self::RANK_ROSE_FROM_ZERO;
        }

        return count($article['entries']) > 1 ? self::RANK_UNCHANGED : self::RANK_NO_HISTORY;
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
        $observations = self::observations($lines);
        $oldest = end($observations);
        $latest = $observations[0]['unit_price'];
        $previous = count($observations) > 1 ? $observations[1]['unit_price'] : null;

        return [
            'name' => $lines[0]->name,
            'unit' => $lines[0]->unit,
            'levels' => self::levels($observations),
            'entries' => array_slice($observations, 0, self::HISTORY_LENGTH),
            'observations' => count($observations),
            'first' => $oldest,
            'change' => self::changePercent($latest, $previous),
            'changed' => $previous !== null && !self::samePrice($latest, $previous),
            // What the price has done over the whole history, not just since
            // the invoice before this one.
            'total_change' => count($observations) > 1 ? self::changePercent($latest, $oldest['unit_price']) : null,
        ];
    }

    /**
     * A price the supplier charged, and for how long. Invoicing weekly at a
     * steady price is one level, not twelve: without this a stable article
     * fills its whole history with the same number and the change that
     * matters falls off the end.
     *
     * Oldest first, each level running from the first invoice that charged it
     * until the first invoice that did not.
     */
    private static function levels(array $observations): array
    {
        $levels = [];

        foreach (array_reverse($observations) as $observation) {
            $current = count($levels) > 0 ? $levels[count($levels) - 1] : null;

            if ($current !== null && self::samePrice($current['unit_price'], $observation['unit_price'])) {
                $levels[count($levels) - 1]['to'] = $observation['date'];
                $levels[count($levels) - 1]['invoices']++;
                continue;
            }

            $levels[] = [
                'unit_price' => $observation['unit_price'],
                'unit_price_gross' => $observation['unit_price_gross'],
                'from' => $observation['date'],
                'to' => $observation['date'],
                'invoices' => 1,
                'change' => $current === null ? null : self::changePercent($observation['unit_price'], $current['unit_price']),
            ];
        }

        return $levels;
    }

    /**
     * One price per invoice: the first line wins, and the query has already
     * put the highest position first so a correction beats what it corrects.
     */
    private static function observations(array $lines): array
    {
        $observations = [];
        $invoicesSeen = [];

        foreach ($lines as $line) {
            if (isset($invoicesSeen[$line->client_invoice_id])) {
                continue;
            }

            $invoicesSeen[$line->client_invoice_id] = true;

            $observations[] = [
                'date' => $line->issue_date,
                'invoice_number' => $line->invoice_number,
                'unit_price' => (float) $line->unit_price,
                'unit_price_gross' => $line->unit_price_gross === null ? null : (float) $line->unit_price_gross,
            ];
        }

        return $observations;
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
