<?php

namespace Services;

use App\Models\ClientBankAccount;
use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EfaktureService
{
    private const ITEM_NAME_FALLBACK = '(bez naziva)';
    private const DATE_FORMAT = 'Y-m-d';

    /**
     * Column widths, so an overlong value costs a few characters instead of
     * the whole invoice (MySQL runs in strict mode).
     */
    private const MAX_SUPPLIER_NAME = 255;
    private const MAX_INVOICE_NUMBER = 64;
    private const MAX_PAYMENT_MODEL = 8;
    private const MAX_PIB = 20;
    private const MAX_BANK_ACCOUNT_SNAPSHOT = 64;
    private const MAX_ITEM_NAME = 255;
    private const MAX_ITEM_SKU = 64;
    private const MAX_ITEM_UNIT = 32;

    private const CURRENCY = 'RSD';

    public static function loadSuppliers(): Collection
    {
        return ClientBankAccount::all();
    }

    /**
     * Validate and coerce one raw SEF invoice into the shape the models expect.
     *
     * @throws InvalidArgumentException when the invoice cannot be saved at all.
     */
    public static function normalizeInvoice(array $row): array
    {
        $sefId = $row['sef_id'] ?? null;
        if (!is_numeric($sefId) || (int) $sefId <= 0) {
            throw new InvalidArgumentException('sef_id is required');
        }

        // The exporter only ever produces RSD; if that ever changes we want
        // to hear about it, not to file a EUR total as dinars.
        $currency = strtoupper(trim((string) ($row['valuta'] ?? '')));
        if ($currency !== '' && $currency !== self::CURRENCY) {
            throw new InvalidArgumentException('valuta ' . $currency . ' is not supported, only ' . self::CURRENCY);
        }

        $supplierName = trim((string) ($row['dobavljac'] ?? ''));
        if ($supplierName === '') {
            throw new InvalidArgumentException('dobavljac is required');
        }

        // Negative totals are knjižno odobrenje (credit notes), a document he
        // needs kept just as much as an ordinary invoice.
        $amount = self::toFloat($row['ukupno_za_uplatu'] ?? null);
        if ($amount === null) {
            throw new InvalidArgumentException('ukupno_za_uplatu is not a valid amount');
        }

        $issueDate = self::toDate($row['datum_izdavanja'] ?? null);
        if ($issueDate === null) {
            throw new InvalidArgumentException('datum_izdavanja is not a valid date');
        }

        $bankAccount = trim((string) ($row['tekuci_racun'] ?? ''));
        $invoiceNumber = self::clip(trim((string) ($row['broj_racuna'] ?? '')), self::MAX_INVOICE_NUMBER);

        return [
            'sef_id' => (int) $sefId,
            'invoice_number' => $invoiceNumber,
            'supplier_name' => self::clip($supplierName, self::MAX_SUPPLIER_NAME),
            'pib' => self::clip(self::digits($row['dobavljac_pib'] ?? null), self::MAX_PIB) ?? '',
            'bank_account' => $bankAccount !== '' ? $bankAccount : null,
            'reference_number' => self::referenceNumber($row['poziv_na_broj'] ?? null, $invoiceNumber),
            'payment_model' => self::clip(self::digits($row['model'] ?? null), self::MAX_PAYMENT_MODEL),
            'issue_date' => $issueDate,
            'payment_deadline' => self::optionalDate($row, 'rok_dospeca', $issueDate),
            'transaction_date' => self::optionalDate($row, 'datum_prometa', $issueDate),
            'amount' => $amount,
            'items' => self::normalizeItems($row['stavke'] ?? [], $sefId),
        ];
    }

    /**
     * SEF sends "--" when the supplier left the poziv na broj off the invoice.
     * The invoice number is then the only thing worth pasting into e-banking.
     */
    private static function referenceNumber($raw, ?string $invoiceNumber): ?string
    {
        $value = trim((string) $raw);

        return preg_match('/[\p{L}\p{N}]/u', $value) === 1 ? $value : $invoiceNumber;
    }

    public static function alreadyImported(int $sefId): bool
    {
        return ClientInvoice::where('sef_id', $sefId)->exists();
    }

    /**
     * A date we can live without, but not one we are willing to guess at:
     * absent means fall back, present but unreadable means the invoice is
     * wrong and he needs to see it.
     *
     * @throws InvalidArgumentException
     */
    private static function optionalDate(array $row, string $key, string $fallback): string
    {
        $raw = $row[$key] ?? null;

        if ($raw === null || trim((string) $raw) === '') {
            return $fallback;
        }

        $date = self::toDate($raw);

        if ($date === null) {
            throw new InvalidArgumentException($key . ' is not a valid date');
        }

        return $date;
    }

    /**
     * Find the supplier this invoice belongs to, or create it.
     *
     * Matching walks PIB, then bank account, then name, because the suppliers
     * entered by hand in the backoffice have nothing but a name.
     */
    public static function resolveSupplier(array $data, Collection $suppliers): ClientBankAccount
    {
        $pib = $data['pib'];
        $account = self::digits($data['bank_account']);
        $name = self::normalizeName($data['supplier_name']);

        // A supplier whose PIB is known and different is a different company,
        // whatever its name or account says. Without this, one wrong match
        // backfills the wrong PIB and every later invoice repeats it.
        $candidates = $suppliers->filter(function ($supplier) use ($pib) {
            $supplierPib = self::digits($supplier->pib);

            return $supplierPib === '' || $pib === '' || $supplierPib === $pib;
        });

        $match = $pib !== ''
            ? $candidates->first(fn ($supplier) => self::digits($supplier->pib) === $pib)
            : null;

        if (!$match && $account !== '') {
            $match = $candidates->first(fn ($supplier) => self::digits($supplier->bank_account) === $account);
        }

        if (!$match && $name !== '') {
            $match = $candidates->first(fn ($supplier) => self::normalizeName($supplier->name) === $name);
        }

        if ($match) {
            self::backfillSupplier($match, $data);
            return $match;
        }

        $created = ClientBankAccount::create([
            'name' => $data['supplier_name'],
            'pib' => $pib !== '' ? $pib : null,
            'bank_account' => $data['bank_account'],
            'active' => ClientBankAccount::STATUS_ACTIVE,
        ]);

        $suppliers->push($created);

        return $created;
    }

    /**
     * An invoice already carrying this sef_id is never touched again, so a
     * resend cannot disturb what the owner has since done with it. Correcting
     * a bad import means deleting it in the backoffice and sending it again.
     */
    public static function createInvoice(array $data, ClientBankAccount $supplier): ClientInvoice
    {
        return ClientInvoice::create([
            'sef_id' => $data['sef_id'],
            'invoice_number' => $data['invoice_number'],
            'client_account' => $supplier->id,
            'supplier_pib' => $data['pib'] !== '' ? $data['pib'] : null,
            'supplier_bank_account' => self::clip($data['bank_account'], self::MAX_BANK_ACCOUNT_SNAPSHOT),
            'reference_number' => $data['reference_number'],
            'payment_model' => $data['payment_model'],
            'issue_date' => $data['issue_date'],
            'payment_deadline' => $data['payment_deadline'],
            'transaction_date' => $data['transaction_date'],
            'amount' => $data['amount'],
            'status' => ClientInvoice::STATUS_PENDING,
        ]);
    }

    public static function saveItems(ClientInvoice $invoice, array $items): int
    {
        foreach ($items as $item) {
            ClientInvoiceItem::create($item + ['client_invoice_id' => $invoice->id]);
        }

        return count($items);
    }

    private static function backfillSupplier(ClientBankAccount $supplier, array $data): void
    {
        $fill = [];

        if (empty($supplier->pib) && $data['pib'] !== '') {
            $fill['pib'] = $data['pib'];
        }

        if (empty($supplier->bank_account) && $data['bank_account']) {
            $fill['bank_account'] = $data['bank_account'];
        }

        if ($fill) {
            $supplier->update($fill);
        }
    }

    /**
     * A junk line never costs us the invoice: the header carries the money.
     */
    private static function normalizeItems($items, $sefId): array
    {
        if (!is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['naziv'] ?? ''));
            $sku = trim((string) ($item['sifra'] ?? ''));
            $unit = trim((string) ($item['jedinica_mere'] ?? ''));
            $quantity = self::toFloat($item['kolicina'] ?? null);
            $unitPrice = self::toFloat($item['jedinicna_cena'] ?? null);
            $netAmount = self::toFloat($item['osnovica'] ?? null);
            $vatRate = self::toFloat($item['pdv_procenat'] ?? null);
            $unitPriceGross = self::toFloat($item['cena_sa_pdv'] ?? null);

            if ($name === '' || $quantity === null || $unitPrice === null || $netAmount === null) {
                Log::warning('[Efakture] Coerced malformed line item', [
                    'sef_id' => $sefId,
                    'item' => $item,
                ]);
            }

            $normalized[] = [
                'position' => is_numeric($item['rb'] ?? null) ? (int) $item['rb'] : null,
                'sku' => self::clip($sku, self::MAX_ITEM_SKU),
                'name' => $name !== '' ? self::clip($name, self::MAX_ITEM_NAME) : self::ITEM_NAME_FALLBACK,
                'quantity' => $quantity ?? 0.0,
                'unit' => self::clip($unit, self::MAX_ITEM_UNIT),
                'unit_price' => $unitPrice ?? 0.0,
                'unit_price_gross' => $unitPriceGross,
                'net_amount' => $netAmount ?? 0.0,
                'vat_rate' => $vatRate ?? 0.0,
            ];
        }

        return $normalized;
    }

    /**
     * Numbers arrive as ints, floats or plain decimal strings. Anything
     * locally formatted ("6.439,08") is rejected rather than guessed at,
     * because guessing wrong writes a silently wrong amount.
     */
    private static function toFloat($value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * ISO dates only, and the round trip has to match: Carbon happily rolls
     * "2026-13-45" over into 2027-02-14, and reads "09/03/2026" as a date
     * whose day and month are anyone's guess. A wrong date on an invoice is
     * worse than a rejected one.
     */
    private static function toDate($value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat(self::DATE_FORMAT, $value);
        } catch (\Exception $e) {
            return null;
        }

        return $date && $date->format(self::DATE_FORMAT) === $value ? $value : null;
    }

    private static function clip(?string $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private static function digits($value): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }

        return preg_replace('/\D/', '', (string) $value);
    }

    public static function normalizeName($value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}
