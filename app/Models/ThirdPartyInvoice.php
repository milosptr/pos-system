<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Models\Traits\ThirdPartyInvoiceFilters;
use Illuminate\Database\Eloquent\Model;

class ThirdPartyInvoice extends Model
{
    use HasUuid;
    use ThirdPartyInvoiceFilters;

    const STATUS_STORNO = 0;
    const STATUS_PAYED = 1;
    const STATUS_ON_THE_HOUSE = 2;

    const PAYMENT_CASH = 1;
    const PAYMENT_CARD = 2;
    const PAYMENT_TRANSFER = 3;
    const PAYMENT_KASA_I = 4;

    protected $fillable = [
        'external_invoice_id',
        'invoice_number',
        'is_duplicate',
        'external_order_id',
        'table_name',
        'status',
        'order',
        'total',
        'payment_type',
        'discount',
        'invoiced_at',
    ];

    protected $casts = [
        'order' => 'array',
        'is_duplicate' => 'boolean',
        'invoiced_at' => 'datetime',
    ];

    /**
     * Check if an invoice with the given number already exists.
     *
     * @param string $invoiceNumber
     * @return bool
     */
    public static function isDuplicate(string $invoiceNumber): bool
    {
        return static::where('invoice_number', $invoiceNumber)->exists();
    }

    /**
     * Find invoice by external invoice ID (racunid).
     *
     * @param int $externalInvoiceId
     * @return self|null
     */
    public static function findByExternalId(int $externalInvoiceId): ?self
    {
        return static::where('external_invoice_id', $externalInvoiceId)->first();
    }

    /**
     * Check if this invoice is already marked as storno.
     *
     * @return bool
     */
    public function isStorno(): bool
    {
        return $this->status === self::STATUS_STORNO;
    }

    /**
     * Check if this invoice is marked as on the house.
     *
     * @return bool
     */
    public function isOnTheHouse(): bool
    {
        return $this->status === self::STATUS_ON_THE_HOUSE;
    }

    /**
     * Mark invoice as storno and delete related sales/warehouse records.
     * This method is transaction-safe and idempotent.
     *
     * @return bool True if storno was processed, false if already stornoed
     */
    public function markAsStorno(): bool
    {
        // Skip if already stornoed (idempotent)
        if ($this->isStorno()) {
            return false;
        }

        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->update(['status' => self::STATUS_STORNO]);

            // Delete related warehouse status records
            \App\Models\WarehouseStatus::where('batch_id', $this->id)->delete();

            // Delete related sales records
            \App\Models\Sales::where('batch_id', $this->id)->delete();
        });

        return true;
    }

    /**
     * Mark invoice as on the house and delete related sales records.
     * Warehouse records are kept because items were still consumed.
     * This method is transaction-safe.
     *
     * @return bool True if processed, false if already on-the-house or stornoed
     */
    public function markAsOnTheHouse(): bool
    {
        if ($this->isOnTheHouse() || $this->isStorno()) {
            return false;
        }

        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->update(['status' => self::STATUS_ON_THE_HOUSE]);

            // Only delete sales — warehouse records stay (items were consumed)
            \App\Models\Sales::where('batch_id', $this->id)->delete();
        });

        return true;
    }
}
