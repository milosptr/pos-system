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
     * Format a table name with the appropriate prefix for kitchen display.
     *
     * @param string $tableName
     * @return string
     */
    public static function formatTableName(string $tableName): string
    {
        $trimmed = trim($tableName);

        if (strcasecmp($trimmed, 'N') === 0) {
            return 'Nošenje';
        }

        if (ctype_digit($trimmed)) {
            $n = (int) $trimmed;
            if (($n >= 11 && $n <= 25) || in_array($n, [40, 50, 60])) {
                return 'Bašta ' . $trimmed;
            }
            return 'Sala ' . $trimmed;
        }

        return $trimmed;
    }

    /**
     * Check if an order should be filtered out from the kitchen display.
     * Orders from table "Sima" containing only Kafa items are filtered out.
     *
     * @param string $tableName
     * @param array $items
     * @return bool
     */
    public static function shouldFilterOut(string $tableName, array $items): bool
    {
        if (strcasecmp(trim($tableName), 'sima') !== 0) {
            return false;
        }

        if (empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            $name = $item['name'] ?? '';
            if (stripos($name, 'kafa') === false) {
                return false;
            }
        }

        return true;
    }

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
                    'category_id' => $item['category_id'] ?? null,
                    'sku' => $item['sku'] ?? null,
                    'storno' => !empty($item['refund']),
                ];
            }
        }

        // Reverse to match tablet display order (last added shown first)
        $kitchenItems = array_reverse($kitchenItems);

        if (empty($kitchenItems)) {
            // Delete any existing kitchen order for this source order
            KitchenOrder::where('orderable_type', 'order')
                ->where('orderable_id', $order->id)
                ->delete();
            return null;
        }

        $tableName = $order->table->name;

        if (self::shouldFilterOut($tableName, $kitchenItems)) {
            KitchenOrder::where('orderable_type', 'order')
                ->where('orderable_id', $order->id)
                ->delete();
            return null;
        }

        $kitchenOrder = KitchenOrder::updateOrCreate(
            [
                'orderable_type' => 'order',
                'orderable_id' => $order->id,
            ],
            [
                'table_name' => self::formatTableName($tableName),
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

        $tableName = $order->table_name;
        $itemsArray = $kitchenItems->map(fn($item) => ['name' => $item->name])->toArray();

        if (self::shouldFilterOut($tableName, $itemsArray)) {
            $existing = KitchenOrder::where('orderable_type', 'third_party_order')
                ->where('orderable_id', $order->id)
                ->first();
            if ($existing) {
                $existing->items()->delete();
                $existing->delete();
            }
            return null;
        }

        $kitchenOrder = KitchenOrder::updateOrCreate(
            [
                'orderable_type' => 'third_party_order',
                'orderable_id' => $order->id,
            ],
            [
                'table_name' => self::formatTableName($tableName),
            ]
        );

        $existingExternalIds = $kitchenOrder->items()->pluck('external_item_id')->toArray();

        // Sync items using external_item_id to preserve is_done state
        $incomingExternalIds = [];
        foreach ($kitchenItems as $item) {
            $incomingExternalIds[] = $item->external_item_id;

            $inventoryId = self::matchInventory($item->sku, $item->name);
            $categoryId = $inventoryId ? Inventory::find($inventoryId)?->category_id : null;

            $kitchenOrder->items()->updateOrCreate(
                ['external_item_id' => $item->external_item_id],
                [
                    'name' => $item->name,
                    'qty' => $item->qty,
                    'modifier' => $item->modifier,
                    'storno' => !$item->active,
                    'category_id' => $categoryId,
                    'sku' => $item->sku,
                ]
            );
        }

        // Remove items no longer present in the order
        $kitchenOrder->items()
            ->whereNotIn('external_item_id', $incomingExternalIds)
            ->delete();

        // Only reset ready_at if new items were actually added
        $hasNewItems = !empty(array_diff($incomingExternalIds, $existingExternalIds));
        if ($kitchenOrder->ready_at !== null && $hasNewItems) {
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
