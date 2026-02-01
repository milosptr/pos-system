<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ThirdPartyOrder extends Model
{
    use HasUuid;

    protected $fillable = [
        'external_order_id',
        'table_id',
        'table_name',
        'total',
    ];

    protected $casts = [
        'total' => 'integer',
    ];

    /**
     * Get all items for this order.
     */
    public function items()
    {
        return $this->hasMany(ThirdPartyOrderItem::class);
    }

    /**
     * Get only active items for this order.
     */
    public function activeItems()
    {
        return $this->hasMany(ThirdPartyOrderItem::class)->where('active', 1);
    }

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

    /**
     * Delete all orders for a given table ID.
     *
     * @param int $tableId
     * @return int Number of orders deleted
     */
    public static function deleteByTableId(int $tableId): int
    {
        return static::where('table_id', $tableId)->delete();
    }
}
