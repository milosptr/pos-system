<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdPartyOrder extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'external_order_id',
        'table_id',
        'table_name',
        'total',
        'ordered_at',
    ];

    protected $casts = [
        'total' => 'integer',
        'ordered_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function (ThirdPartyOrder $order) {
            $order->items()->delete();
        });
    }

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
        $orderIds = static::where('external_order_id', $orderId)->pluck('id');
        ThirdPartyOrderItem::whereIn('third_party_order_id', $orderIds)->delete();
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
        $orderIds = static::where('table_id', $tableId)->pluck('id');
        ThirdPartyOrderItem::whereIn('third_party_order_id', $orderIds)->delete();
        return static::where('table_id', $tableId)->delete();
    }
}
