<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyInvoice;
use App\Models\ThirdPartyOrder;
use App\Models\ThirdPartyOrderItem;
use App\Http\Requests\ThirdPartyOrderStoreRequest;
use App\Http\Resources\ThirdPartyOrderResource;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Services\KitchenService;
use Services\Pusher;
use Services\WorkingDay;

class ThirdPartyOrderController extends Controller
{
    /**
     * Upper bound on rows per request. An all-tables startup sync from the
     * external system is ~600 rows; this only guards against runaway payloads.
     */
    public const MAX_ROWS = 5000;

    /**
     * Payloads up to this many rows are logged in full for debugging;
     * larger ones (startup syncs) are logged as a summary.
     */
    private const LOG_FULL_BODY_MAX_ROWS = 50;

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

        if (count($rows) <= self::LOG_FULL_BODY_MAX_ROWS) {
            Log::info('[ThirdPartyOrder] Incoming request', [
                'body' => $rows,
                'ip' => $request->ip(),
            ]);
        } else {
            Log::info('[ThirdPartyOrder] Incoming request (large payload)', [
                'rows' => count($rows),
                'order_ids' => collect($rows)->pluck('porudzbinaid')->unique()->values()->toArray(),
                'ip' => $request->ip(),
            ]);
        }

        if (empty($rows)) {
            Log::warning('[ThirdPartyOrder] Empty data provided', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'No data provided',
            ], 422);
        }

        if (count($rows) > self::MAX_ROWS) {
            Log::warning('[ThirdPartyOrder] Payload too large', [
                'rows' => count($rows),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Too many rows in one request (max ' . self::MAX_ROWS . ')',
            ], 422);
        }

        // A retry after a timeout must not interleave with the still-running
        // first request; process one sync at a time.
        try {
            return Cache::lock('third-party-order-store', 60)
                ->block(10, fn () => $this->processOrderRows($rows));
        } catch (LockTimeoutException $e) {
            Log::warning('[ThirdPartyOrder] Another sync is still running', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Another order sync is still being processed, retry shortly',
            ], 429);
        }
    }

    /**
     * Process the grouped order rows, each order in its own transaction so
     * one bad group cannot take down the rest of a multi-table payload.
     *
     * @param array $rows
     * @return \Illuminate\Http\JsonResponse
     */
    private function processOrderRows(array $rows)
    {
        // Split invalid rows out before grouping so they cannot collapse into
        // a bogus order 0 and overwrite each other's items.
        [$validRows, $invalidRows] = collect($rows)->partition(function ($row) {
            return is_array($row)
                && isset($row['porudzbinaid'])
                && is_numeric($row['porudzbinaid'])
                && (int) $row['porudzbinaid'] > 0;
        });

        if ($invalidRows->isNotEmpty()) {
            Log::warning('[ThirdPartyOrder] Skipped rows with missing/invalid porudzbinaid', [
                'count' => $invalidRows->count(),
                'sample' => $invalidRows->first(),
            ]);
        }

        // Group rows by porudzbinaid (multiple orders can be in one request)
        $orderGroups = $validRows->groupBy('porudzbinaid');
        $processedOrders = [];
        $summary = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped_invoiced' => [],
            'invalid_rows' => $invalidRows->count(),
            'failed' => [],
        ];

        if ($orderGroups->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid rows provided',
                'summary' => $summary,
            ], 422);
        }

        foreach ($orderGroups as $externalOrderId => $orderRows) {
            $externalOrderId = (int) $externalOrderId;

            try {
                $order = DB::transaction(function () use ($externalOrderId, $orderRows, &$summary) {
                    return $this->processOrderGroup($externalOrderId, $orderRows, $summary);
                });

                if ($order !== null) {
                    $processedOrders[] = $order;
                    $summary['processed']++;
                    $order->wasRecentlyCreated ? $summary['created']++ : $summary['updated']++;
                }
            } catch (\Exception $e) {
                Log::error('[ThirdPartyOrder] Failed to process order group', [
                    'external_order_id' => $externalOrderId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $summary['failed'][] = [
                    'external_order_id' => $externalOrderId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // One broadcast per request, not one per order
        if (!empty($processedOrders)) {
            try {
                app(Pusher::class)->trigger('broadcasting', 'tables-update', []);
                app(Pusher::class)->trigger('broadcasting', 'kitchen-update', []);
            } catch (\Exception $e) {
                Log::error('[ThirdPartyOrder] Pusher notification failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $allFailed = !empty($summary['failed']) && empty($processedOrders) && empty($summary['skipped_invoiced']);

        Log::info('[ThirdPartyOrder] Orders processed', [
            'summary' => array_merge($summary, [
                'failed' => collect($summary['failed'])->pluck('external_order_id')->toArray(),
            ]),
            'order_ids' => collect($processedOrders)->pluck('external_order_id')->toArray(),
        ]);

        return response()->json([
            'success' => !$allFailed,
            'message' => $allFailed
                ? 'Failed to process orders'
                : count($processedOrders) . ' order(s) processed',
            'data' => ThirdPartyOrderResource::collection(
                collect($processedOrders)->map->load('items')
            ),
            'summary' => $summary,
        ], $allFailed ? 500 : 201);
    }

    /**
     * Process one porudzbinaid group: resolve the order against soft-deleted
     * history, sync its items, and dispatch it to the kitchen.
     *
     * Returns null when the order was skipped because an invoice already
     * settled it during the current working day.
     *
     * @param int $externalOrderId
     * @param \Illuminate\Support\Collection $orderRows
     * @param array $summary
     * @return ThirdPartyOrder|null
     */
    private function processOrderGroup(int $externalOrderId, $orderRows, array &$summary): ?ThirdPartyOrder
    {
        $orderRows = self::mergeModifierRows($orderRows);
        $firstRow = $orderRows->first();

        // Extract order-level data (lowercase field names)
        $tableId = isset($firstRow['stoid']) ? (int) $firstRow['stoid'] : null;
        $tableName = (string) ($firstRow['sto'] ?? 'Unknown');
        $lastRow = $orderRows->last();
        $waiterName = $lastRow['konobar'] ?? null;

        // Parse order datetime from datum field
        $orderedAt = isset($firstRow['datum']) && !empty($firstRow['datum'])
            ? $firstRow['datum']
            : null;

        $attributes = [
            'table_id' => $tableId,
            'table_name' => $tableName,
            'total' => 0,
            'ordered_at' => $orderedAt,
        ];

        $order = ThirdPartyOrder::where('external_order_id', $externalOrderId)->first();

        if ($order) {
            $order->update($attributes);
        } else {
            // No live row. If the last one was closed by an invoice during the
            // current working day, this is a resend of a paid order — it must
            // not come back as a ghost table or reach the kitchen. A row
            // soft-deleted by the 4am cleanup, or from a previous working day
            // (the external system reuses ids across days), is treated as new.
            $lastTrashed = ThirdPartyOrder::onlyTrashed()
                ->where('external_order_id', $externalOrderId)
                ->orderByDesc('deleted_at')
                ->first();

            if ($lastTrashed && $this->wasSettledThisWorkingDay($lastTrashed, $externalOrderId)) {
                Log::warning('[ThirdPartyOrder] Skipping resent order already closed by an invoice', [
                    'external_order_id' => $externalOrderId,
                ]);
                $summary['skipped_invoiced'][] = $externalOrderId;
                return null;
            }

            $order = ThirdPartyOrder::create(
                ['external_order_id' => $externalOrderId] + $attributes
            );
        }

        // One lookup for all of this order's items, trashed included: an item
        // cleared by a partial invoice (listastavki) must not be resurrected
        // or duplicated when the external system resends the whole order.
        $existingItems = $order->items()->withTrashed()->get()->keyBy('external_item_id');

        $totalCents = 0;
        $liveItemIds = [];

        foreach ($orderRows as $row) {
            $externalItemId = (int) ($row['stavkaid'] ?? 0);
            $existingItem = $existingItems->get($externalItemId);

            if ($existingItem && $existingItem->trashed()) {
                // Already paid via a partial invoice — stays out of the order
                // and out of the total.
                continue;
            }

            $qty = (float) ($row['kolicina'] ?? 0);
            $price = (int) round((float) ($row['cena'] ?? 0));
            $totalCents += (int) round($qty * $price);

            $liveItemIds[] = $externalItemId;

            $itemData = [
                'third_party_order_id' => $order->id,
                'external_item_id' => $externalItemId,
                'name' => (string) ($row['naziv'] ?? 'Unknown'),
                'qty' => $qty,
                'price' => $price,
                'unit' => (string) ($row['jm'] ?? 'kom'),
                'modifier' => $row['modifikatorslobodan'] ?? null,
                'sku' => $row['sifraArtikla'] ?? null,
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
            ->whereNotIn('external_item_id', $liveItemIds)
            ->delete();

        // Update order total
        $order->update(['total' => $totalCents]);

        try {
            KitchenService::processThirdPartyOrder($order, $waiterName, false);
        } catch (\Exception $e) {
            Log::error('[Kitchen] ' . $e->getMessage());
        }

        return $order;
    }

    /**
     * Was this soft-deleted order closed by an invoice during the current
     * working day (4am-4am)? Bounded to the working day because the external
     * system reuses porudzbinaid on subsequent days — an older invoice says
     * nothing about today's order.
     *
     * @param ThirdPartyOrder $trashedOrder
     * @param int $externalOrderId
     * @return bool
     */
    private function wasSettledThisWorkingDay(ThirdPartyOrder $trashedOrder, int $externalOrderId): bool
    {
        [$dayStart, $dayEnd] = WorkingDay::getWorkingDay();

        // Exact signal: the invoice close paths stamp invoiced_at before
        // soft-deleting (the 4am cleanup does not).
        if ($trashedOrder->invoiced_at !== null
            && $trashedOrder->invoiced_at->between($dayStart, $dayEnd)) {
            return true;
        }

        // Retroactive signal for rows trashed before invoiced_at existed. A
        // stornoed invoice means the payment was cancelled, so it never
        // suppresses the order.
        return ThirdPartyInvoice::where('external_order_id', $externalOrderId)
            ->where('status', '!=', ThirdPartyInvoice::STATUS_STORNO)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->exists();
    }

    /**
     * Check if a row is a modifier-only row (not a real item).
     * Modifier rows have modifikatorslobodan set and cena = 0.
     */
    private static function isModifierOnlyRow(array $row): bool
    {
        return !empty($row['modifikatorslobodan'])
            && (int) ($row['cena'] ?? 0) === 0;
    }

    /**
     * Merge modifier-only rows into their parent items.
     * Ebar sends modifiers as separate rows with stavkaid = parent stavkaid + 1.
     */
    private static function mergeModifierRows($rows): \Illuminate\Support\Collection
    {
        $rows = collect($rows);
        $indexed = $rows->keyBy('stavkaid');
        $consumed = [];

        foreach ($rows as $row) {
            if (!self::isModifierOnlyRow($row)) {
                continue;
            }

            $modifierStavkaId = $row['stavkaid'];
            $parentStavkaId = $modifierStavkaId - 1;

            if (!$indexed->has($parentStavkaId)) {
                Log::warning('[ThirdPartyOrder] Orphan modifier row, no parent at stavkaid ' . $parentStavkaId, [
                    'stavkaid' => $modifierStavkaId,
                ]);
                continue;
            }

            $parent = $indexed->get($parentStavkaId);
            $modifierText = $row['modifikatorslobodan'];

            if (!empty($parent['modifikatorslobodan'])) {
                $parent['modifikatorslobodan'] .= ', ' . $modifierText;
            } else {
                $parent['modifikatorslobodan'] = $modifierText;
            }

            $indexed->put($parentStavkaId, $parent);
            $consumed[] = $modifierStavkaId;
        }

        if (empty($consumed)) {
            return $rows;
        }

        return $indexed->reject(function ($row, $stavkaid) use ($consumed) {
            return in_array($stavkaid, $consumed);
        })->values();
    }

    /**
     * Update storno status for order items.
     * Sets active = 0 for storno = 1, active = 1 for storno = 0.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storno(Request $request)
    {
        $rows = $request->all();

        Log::info('[ThirdPartyOrder] Storno request', [
            'body' => $rows,
            'ip' => $request->ip(),
        ]);

        if (empty($rows)) {
            Log::warning('[ThirdPartyOrder] Empty storno data provided', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'No data provided',
            ], 422);
        }

        try {
            $updated = 0;
            $notFound = 0;
            $results = [];

            foreach ($rows as $row) {
                // Support both stavkaId and stavkaid (case variations)
                $externalItemId = (int) ($row['stavkaId'] ?? $row['stavkaid'] ?? 0);
                $storno = (int) ($row['storno'] ?? 0);

                if ($externalItemId === 0) {
                    continue;
                }

                // Find item by external_item_id
                $item = ThirdPartyOrderItem::where('external_item_id', $externalItemId)->first();

                if ($item) {
                    // storno = 1 means cancelled, so active = 0
                    // storno = 0 means not cancelled, so active = 1
                    $item->update(['active' => $storno ? 0 : 1]);
                    $updated++;
                    $results[] = [
                        'external_item_id' => $externalItemId,
                        'storno' => $storno,
                        'active' => $storno ? 0 : 1,
                        'status' => 'updated',
                    ];
                } else {
                    $notFound++;
                    $results[] = [
                        'external_item_id' => $externalItemId,
                        'status' => 'not_found',
                    ];
                }
            }

            // Reprocess kitchen orders for affected third-party orders
            $affectedOrderIds = ThirdPartyOrderItem::whereIn('external_item_id',
                collect($results)->where('status', 'updated')->pluck('external_item_id')
            )->pluck('third_party_order_id')->unique();

            foreach ($affectedOrderIds as $orderId) {
                try {
                    $order = ThirdPartyOrder::find($orderId);
                    if ($order) {
                        KitchenService::processThirdPartyOrder($order, null, false);
                    }
                } catch (\Exception $e) {
                    Log::error('[Kitchen] ' . $e->getMessage());
                }
            }

            // Notify backoffice of update — one broadcast per request
            try {
                app(Pusher::class)->trigger('broadcasting', 'tables-update', []);
                if ($affectedOrderIds->isNotEmpty()) {
                    app(Pusher::class)->trigger('broadcasting', 'kitchen-update', []);
                }
            } catch (\Exception $e) {
                Log::error('[ThirdPartyOrder] Pusher notification failed', [
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('[ThirdPartyOrder] Storno processed', [
                'updated' => $updated,
                'not_found' => $notFound,
            ]);

            return response()->json([
                'success' => true,
                'message' => $updated . ' item(s) updated' . ($notFound > 0 ? ', ' . $notFound . ' not found' : ''),
                'data' => [
                    'updated' => $updated,
                    'not_found' => $notFound,
                    'results' => $results,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('[ThirdPartyOrder] Failed to process storno', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process storno',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
