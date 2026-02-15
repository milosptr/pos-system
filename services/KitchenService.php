<?php

namespace Services;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use App\Models\Order;
use App\Models\ThirdPartyOrder;
use Illuminate\Support\Facades\Cache;

class KitchenService
{
    /**
     * Get category IDs that belong to KUHINJA (parent_id = 1).
     *
     * @return array
     */
    public static function getKitchenCategoryIds(): array
    {
        return Cache::remember('kitchen-category-ids', 3600, function () {
            return Category::where('parent_id', 1)->pluck('id')->toArray();
        });
    }

    /**
     * Match an inventory item by SKU or name.
     *
     * @param string|null $sku
     * @param string|null $name
     * @return int|null
     */
    public static function matchInventory(?string $sku, ?string $name): ?int
    {
        // First, try to match by SKU as integer comparison
        if ($sku !== null && $sku !== '') {
            $skuInt = (int) $sku;
            $inventory = Inventory::whereRaw('sku + 0 = ?', [$skuInt])->first();
            if ($inventory) {
                return $inventory->id;
            }
        }

        // Fallback: try to match by name (case-insensitive)
        if ($name) {
            $inventory = Inventory::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
            if ($inventory) {
                return $inventory->id;
            }
        }

        return null;
    }

    /**
     * Process a POS order and create/update a kitchen order for kitchen items.
     *
     * @param Order $order
     * @return KitchenOrder|null
     */
    public static function processOrder(Order $order): ?KitchenOrder
    {
        $kitchenCategoryIds = self::getKitchenCategoryIds();
        $orderItems = $order->order ?? [];

        $kitchenItems = [];
        foreach ($orderItems as $item) {
            if (isset($item['category_id']) && in_array($item['category_id'], $kitchenCategoryIds)) {
                $kitchenItems[] = [
                    'name' => $item['name'] ?? '',
                    'qty' => $item['qty'] ?? 1,
                    'modifier' => $item['modifier'] ?? null,
                ];
            }
        }

        if (empty($kitchenItems)) {
            return null;
        }

        $kitchenOrder = KitchenOrder::updateOrCreate(
            [
                'orderable_type' => 'order',
                'orderable_id' => $order->id,
            ],
            [
                'table_name' => $order->table->name,
            ]
        );

        // POS orders are created once, no is_done to preserve
        $kitchenOrder->items()->delete();
        foreach ($kitchenItems as $item) {
            $kitchenOrder->items()->create($item);
        }

        try {
            app(Pusher::class)->trigger('broadcasting', 'kitchen-update', []);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return $kitchenOrder;
    }

    /**
     * Process a third-party order and create/update a kitchen order.
     * Uses print_station_id == 2 to detect kitchen items.
     *
     * @param ThirdPartyOrder $order
     * @return KitchenOrder|null
     */
    public static function processThirdPartyOrder(ThirdPartyOrder $order): ?KitchenOrder
    {
        $order->load('items');

        // Filter to kitchen items using print_station_id == 2
        $kitchenItems = $order->items->filter(function ($item) {
            return $item->print_station_id == 2;
        });

        if ($kitchenItems->isEmpty()) {
            return null;
        }

        // Check if existing record was already marked ready
        $existing = KitchenOrder::where('orderable_type', 'third_party_order')
            ->where('orderable_id', $order->id)
            ->first();

        $shouldResetReady = $existing && $existing->ready_at !== null;

        $kitchenOrder = KitchenOrder::updateOrCreate(
            [
                'orderable_type' => 'third_party_order',
                'orderable_id' => $order->id,
            ],
            [
                'table_name' => $order->table_name,
            ]
        );

        // Sync items using external_item_id to preserve is_done state
        $incomingExternalIds = [];
        foreach ($kitchenItems as $item) {
            $incomingExternalIds[] = $item->external_item_id;

            $kitchenOrder->items()->updateOrCreate(
                ['external_item_id' => $item->external_item_id],
                [
                    'name' => $item->name,
                    'qty' => $item->qty,
                    'modifier' => $item->modifier,
                    'storno' => !$item->active,
                ]
            );
        }

        // Remove items no longer present in the order
        $kitchenOrder->items()
            ->whereNotIn('external_item_id', $incomingExternalIds)
            ->delete();

        // If it was ready but new items appeared, reset ready_at
        if ($shouldResetReady) {
            $kitchenOrder->update(['ready_at' => null]);
        }

        try {
            app(Pusher::class)->trigger('broadcasting', 'kitchen-update', []);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return $kitchenOrder;
    }
}
