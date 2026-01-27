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

    const PAYMENT_CASH = 1;
    const PAYMENT_CARD = 2;
    const PAYMENT_TRANSFER = 3;

    protected $fillable = [
        'invoice_number',
        'is_duplicate',
        'external_order_id',
        'table_name',
        'status',
        'order',
        'total',
        'payment_type',
        'discount',
    ];

    protected $casts = [
        'order' => 'array',
        'is_duplicate' => 'boolean',
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
}
