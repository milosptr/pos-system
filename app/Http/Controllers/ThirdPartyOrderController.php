<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyOrder;
use App\Models\ThirdPartyOrderItem;
use App\Http\Requests\ThirdPartyOrderStoreRequest;
use App\Http\Resources\ThirdPartyOrderResource;
use Illuminate\Support\Facades\Log;

class ThirdPartyOrderController extends Controller
{
    /**
     * Get all active third-party orders.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function all()
    {
        return ThirdPartyOrderResource::collection(
            ThirdPartyOrder::with('items')->get()
        );
    }

    /**
     * Store a third-party order from external system.
     * Handles multiple orders per request (grouped by porudzbinaid).
     *
     * @param ThirdPartyOrderStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ThirdPartyOrderStoreRequest $request)
    {
        $rows = $request->all();

        Log::info('[ThirdPartyOrder] Incoming request', [
            'body' => $rows,
            'ip' => $request->ip(),
        ]);

        if (empty($rows)) {
            Log::warning('[ThirdPartyOrder] Empty data provided', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'No data provided',
            ], 422);
        }

        try {
            // Group rows by porudzbinaid (multiple orders can be in one request)
            $orderGroups = collect($rows)->groupBy('porudzbinaid');
            $processedOrders = [];

            foreach ($orderGroups as $externalOrderId => $orderRows) {
                $firstRow = $orderRows->first();

                // Extract order-level data (lowercase field names)
                $tableId = isset($firstRow['stoid']) ? (int) $firstRow['stoid'] : null;
                $tableName = (string) ($firstRow['sto'] ?? 'Unknown');

                // Find or create order
                $order = ThirdPartyOrder::updateOrCreate(
                    ['external_order_id' => (int) $externalOrderId],
                    [
                        'table_id' => $tableId,
                        'table_name' => $tableName,
                        'total' => 0,
                    ]
                );

                $totalCents = 0;
                $incomingItemIds = [];

                foreach ($orderRows as $row) {
                    $externalItemId = (int) ($row['stavkaid'] ?? 0);
                    $qty = (float) ($row['kolicina'] ?? 0);
                    $price = (int) round((float) ($row['cena'] ?? 0));
                    $itemTotal = (int) round($qty * $price);
                    $totalCents += $itemTotal;

                    $incomingItemIds[] = $externalItemId;

                    // Find existing item to preserve active flag
                    $existingItem = $order->items()
                        ->where('external_item_id', $externalItemId)
                        ->first();

                    $itemData = [
                        'third_party_order_id' => $order->id,
                        'external_item_id' => $externalItemId,
                        'name' => (string) ($row['naziv'] ?? 'Unknown'),
                        'qty' => $qty,
                        'price' => $price,
                        'unit' => (string) ($row['jm'] ?? 'kom'),
                        'modifier' => $row['modifikatorslobodan'] ?? null,
                        'print_station_id' => isset($row['stampanjenalogaid']) ? (int) $row['stampanjenalogaid'] : null,
                    ];

                    if ($existingItem) {
                        // Update existing item - DO NOT touch active flag
                        $existingItem->update($itemData);
                    } else {
                        // New item - default active = 1
                        $itemData['active'] = 1;
                        ThirdPartyOrderItem::create($itemData);
                    }
                }

                // Remove items no longer in this order
                $order->items()
                    ->whereNotIn('external_item_id', $incomingItemIds)
                    ->delete();

                // Update order total
                $order->update(['total' => $totalCents]);
                $processedOrders[] = $order;
            }

            Log::info('[ThirdPartyOrder] Orders processed successfully', [
                'count' => count($processedOrders),
                'order_ids' => collect($processedOrders)->pluck('external_order_id')->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'message' => count($processedOrders) . ' order(s) processed',
                'data' => ThirdPartyOrderResource::collection(
                    collect($processedOrders)->map->load('items')
                ),
            ], 201);
        } catch (\Exception $e) {
            Log::error('[ThirdPartyOrder] Failed to process orders', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
