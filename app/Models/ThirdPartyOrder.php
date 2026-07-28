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

        // 2. Delete the specific kitchen order items, but only for orders the
        //    kitchen already handed out. Food still being prepared stays on the
        //    display even though the bill was just paid.
        $readyKitchenOrderIds = KitchenOrder::where('orderable_type', 'third_party_order')
            ->whereIn('orderable_id', $affectedOrderIds)
            ->ready()
            ->pluck('id')
            ->toArray();

        if (!empty($readyKitchenOrderIds)) {
            KitchenOrderItem::whereIn('kitchen_order_id', $readyKitchenOrderIds)
                ->whereIn('external_item_id', $externalItemIds)
                ->delete();
        }

        // 3. Delete the specific third-party order items
        ThirdPartyOrderItem::whereIn('external_item_id', $externalItemIds)->delete();

        // 4. Clean up empty orders and their kitchen orders
        $ordersDeleted = 0;
        foreach ($affectedOrderIds as $orderId) {
            $order = static::find($orderId);
            if (!$order) continue;

            $kitchenOrder = KitchenOrder::where('orderable_type', 'third_party_order')
                ->where('orderable_id', $orderId)
                ->first();

            if ($order->items()->count() === 0) {
                // Whole order paid: drop the kitchen order if it was already
                // handed out, otherwise let the kitchen finish it and mark it
                // as invoiced so it disappears the moment they do.
                if ($kitchenOrder) {
                    if ($kitchenOrder->ready_at !== null) {
                        $kitchenOrder->items()->delete();
                        $kitchenOrder->delete();
                    } elseif ($kitchenOrder->invoiced_at === null) {
                        $kitchenOrder->update(['invoiced_at' => now()]);
                    }
                }
                $order->delete();
                $ordersDeleted++;
            } else {
                // Order still has un-invoiced items — clean up the kitchen order
                // only if it ran out of kitchen items
                if ($kitchenOrder && $kitchenOrder->items()->count() === 0) {
                    $kitchenOrder->delete();
                }
            }
        }

        return $ordersDeleted;
    }
}
