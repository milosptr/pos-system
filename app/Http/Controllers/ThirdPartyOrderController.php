<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyOrder;
use App\Http\Requests\ThirdPartyOrderStoreRequest;
use Illuminate\Support\Facades\Log;

class ThirdPartyOrderController extends Controller
{
    /**
     * Store a third-party order from external system.
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

        $firstRow = $rows[0];

        // Extract order-level data from first row (with safe defaults)
        $externalOrderId = (int) ($firstRow['porudzbinaid'] ?? 0);
        $tableName = (string) ($firstRow['sto'] ?? 'Unknown');

        // Transform rows into items array
        $items = [];
        $totalCents = 0;

        foreach ($rows as $row) {
            $qty = (float) ($row['kolicina'] ?? 0);
            $price = (float) ($row['cena'] ?? 0);
            $itemTotal = (int) round($qty * $price);
            $totalCents += $itemTotal;

            $item = [
                'name' => (string) ($row['naziv'] ?? 'Unknown'),
                'qty' => $qty,
                'price' => (int) round($price),
                'unit' => (string) ($row['jm'] ?? 'kom'),
            ];

            // Add optional fields
            if (isset($row['modifikatorslobodan']) && $row['modifikatorslobodan']) {
                $item['modifier'] = (string) $row['modifikatorslobodan'];
            }
            if (isset($row['stampanjenalogid'])) {
                $item['print_station_id'] = (int) $row['stampanjenalogid'];
            }

            $items[] = $item;
        }

        try {
            // Check if order with same external_order_id exists
            $existingOrder = ThirdPartyOrder::where('external_order_id', $externalOrderId)->first();
            $updated = false;

            if ($existingOrder) {
                // Update existing order
                $existingOrder->update([
                    'table_name' => $tableName,
                    'order' => $items,
                    'total' => $totalCents,
                ]);
                $order = $existingOrder;
                $updated = true;
            } else {
                // Create new order
                $order = ThirdPartyOrder::create([
                    'external_order_id' => $externalOrderId,
                    'table_name' => $tableName,
                    'order' => $items,
                    'total' => $totalCents,
                ]);
            }

            Log::info('[ThirdPartyOrder] Order stored successfully', [
                'id' => $order->id,
                'external_order_id' => $externalOrderId,
                'table_name' => $tableName,
                'total' => $totalCents,
                'updated' => $updated,
            ]);

            return response()->json([
                'success' => true,
                'message' => $updated ? 'Order updated successfully' : 'Order stored successfully',
                'data' => [
                    'id' => $order->id,
                    'external_order_id' => $order->external_order_id,
                    'total' => $order->total,
                    'updated' => $updated,
                ],
            ], $updated ? 200 : 201);
        } catch (\Exception $e) {
            Log::error('[ThirdPartyOrder] Failed to store order', [
                'external_order_id' => $externalOrderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
