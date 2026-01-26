<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ThirdPartyOrder extends Model
{
    use HasUuid;

    protected $fillable = [
        'external_order_id',
        'table_name',
        'order',
        'total',
    ];

    protected $casts = [
        'order' => 'array',
    ];

    /**
     * Delete an order by its external order ID.
     *
     * @param int $orderId
     * @return bool
     */
    public static function deleteByExternalOrderId(int $orderId): bool
    {
        return static::where('external_order_id', $orderId)->delete() > 0;
    }
}
