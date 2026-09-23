<?php

namespace Services;

use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use Illuminate\Support\Collection;

class SupplierPriceService
{
    /**
     * How many observations travel to the browser per article. They feed the
     * price chart and the current price; the expanded history reads changes.
     */
    public const HISTORY_LENGTH = 24;

    /**
     * The scale of unit_price and unit_price_gross in client_invoice_items.
     * Two prices that agree to this many places are the same price.
     */
    private const PRICE_SCALE = 4;

    private const PERCENT = 100.0;

    /**
     * Money, as the exporter writes it: cena_sa_pdv arrives rounded to the
     * para, so a derived gross price has to land on the same number or the
     * two spellings of one price read as a change.
     */
    private const MONEY_SCALE = 2;

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

    /**
     * Without a supplier the whole book is answered at once, which is how the
     * screen opens: everything the restaurant buys, minus the suppliers whose
     * prices it has been told not to track. Ask for one of those by name and
     * it still answers, or there would be no way back.
     */
    public static function history(?string $supplierId = null, ?string $search = null, bool $changedOnly = false, string $sort = self::SORT_CHANGE): array
    {
        $articles = [];

        foreach (self::loadLines($supplierId, $search) as $line) {
            $key = self::articleKey($line->client_account, $line->name, $line->unit);
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
            $byName = strcmp(self::normalize($a['name']), self::normalize($b['name']));

            return $byRank ?: ($byName ?: strcmp(self::normalize($a['supplier']), self::normalize($b['supplier'])));
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
    private static function loadLines(?string $supplierId, ?string $search): Collection
    {
        $query = ClientInvoiceItem::query()
            ->join('client_invoices', 'client_invoices.id', '=', 'client_invoice_items.client_invoice_id')
            ->join('client_bank_accounts', 'client_bank_accounts.id', '=', 'client_invoices.client_account')
            ->where('client_invoices.amount', '>', 0)
            ->where('client_invoices.status', '!=', ClientInvoice::STATUS_CANCELLED)
            ->where('client_invoice_items.quantity', '>', 0)
            ->where(function ($prices) {
                $prices->where('client_invoice_items.unit_price', '>', 0)
                    ->orWhere('client_invoice_items.unit_price_gross', '>', 0);
            });

        if ($supplierId !== null) {
            $query->where('client_invoices.client_account', $supplierId);
        } else {
            $query->where('client_bank_accounts.track_prices', true);
        }

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
                'client_invoice_items.vat_rate',
                'client_invoices.invoice_number',
                'client_invoices.issue_date',
                'client_invoices.client_account',
                'client_bank_accounts.name as supplier',
            ]);
    }

    private static function buildArticle(array $lines): array
    {
        $observations = self::observations($lines);
        $latest = $observations[0]['unit_price_gross'];
        $previous = count($observations) > 1 ? $observations[1]['unit_price_gross'] : null;

        return [
            'name' => $lines[0]->name,
            'unit' => $lines[0]->unit,
            'supplier' => $lines[0]->supplier,
            'client_account' => $lines[0]->client_account,
            'entries' => array_slice($observations, 0, self::HISTORY_LENGTH),
            'observations' => count($observations),
            'changes' => self::priceChanges($observations),
            'change' => self::changePercent($latest, $previous),
            'changed' => $previous !== null && !self::samePrice($latest, $previous),
        ];
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
                'unit_price_gross' => self::grossPrice($line),
            ];
        }

        return $observations;
    }

    /**
     * Oldest first: the starting price, then every invoice whose price differs
     * from the invoice before it. Walks the whole history, not the capped
     * entries, so an old change is never lost.
     */
    private static function priceChanges(array $observations): array
    {
        $changes = [];
        $previous = null;

        foreach (array_reverse($observations) as $observation) {
            if ($previous === null || !self::samePrice($observation['unit_price_gross'], $previous['unit_price_gross'])) {
                $changes[] = $observation;
            }

            $previous = $observation;
        }

        return $changes;
    }

    /**
     * What the restaurant actually pays for one unit, and the price every
     * comparison is made on. A line that arrived without cena_sa_pdv still
     * carries its VAT rate, so the gross price is derived rather than left
     * missing and dropped out of the history.
     */
    private static function grossPrice(ClientInvoiceItem $line): float
    {
        if ($line->unit_price_gross !== null && (float) $line->unit_price_gross > 0) {
            return (float) $line->unit_price_gross;
        }

        return round((float) $line->unit_price * (1 + (float) $line->vat_rate / self::PERCENT), self::MONEY_SCALE);
    }

    /**
     * An earlier price of zero — the exporter sent neither price — has no
     * percentage to give, and dividing by it would throw.
     */
    private static function changePercent(float $latest, ?float $previous): ?float
    {
        if ($previous === null || $previous <= 0 || self::samePrice($latest, $previous)) {
            return null;
        }

        return round((($latest - $previous) / $previous) * self::PERCENT, self::PERCENT_SCALE);
    }

    private static function samePrice(float $first, float $second): bool
    {
        return round($first, self::PRICE_SCALE) === round($second, self::PRICE_SCALE);
    }

    /**
     * The supplier and the unit are part of the identity: two bakeries selling
     * hleb are not one article, and the same name sold by kilogram and by
     * komad is not one article whose price tripled. A null unit and an empty
     * one are the same absence.
     */
    private static function articleKey(string $supplierId, string $name, ?string $unit): string
    {
        return $supplierId . '|' . self::normalize($name) . '|' . self::normalize($unit);
    }

    private static function normalize(?string $value): string
    {
        return preg_replace('/\s+/u', ' ', EfaktureService::normalizeName($value));
    }
}
