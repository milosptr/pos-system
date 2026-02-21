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

    /**
     * Delete specific items by their external IDs, then clean up empty orders and kitchen entries.
     *
     * @param array $externalItemIds
     * @return int Number of orders fully deleted
     */
    public static function deleteByExternalItemIds(array $externalItemIds): int
    {
        // 1. Find affected order IDs before deleting items
        $affectedOrderIds = ThirdPartyOrderItem::whereIn('external_item_id', $externalItemIds)
            ->pluck('third_party_order_id')
            ->unique()
            ->toArray();

        if (empty($affectedOrderIds)) {
            return 0;
        }

        // 2. Delete the specific kitchen order items
        KitchenOrderItem::whereIn('external_item_id', $externalItemIds)->delete();

        // 3. Delete the specific third-party order items
        ThirdPartyOrderItem::whereIn('external_item_id', $externalItemIds)->delete();

        // 4. Clean up empty orders and their kitchen orders
        $ordersDeleted = 0;
        foreach ($affectedOrderIds as $orderId) {
            $order = static::find($orderId);
            if (!$order) continue;

            if ($order->items()->count() === 0) {
                KitchenOrder::where('orderable_type', 'third_party_order')
                    ->where('orderable_id', $orderId)
                    ->delete();
                $order->delete();
                $ordersDeleted++;
            } else {
                // Order still has items — clean up kitchen order if no kitchen items remain
                $kitchenOrder = KitchenOrder::where('orderable_type', 'third_party_order')
                    ->where('orderable_id', $orderId)
                    ->first();
                if ($kitchenOrder && $kitchenOrder->items()->count() === 0) {
                    $kitchenOrder->delete();
                }
            }
        }

        return $ordersDeleted;
    }
}
