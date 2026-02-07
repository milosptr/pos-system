<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Sales;
use App\Models\ThirdPartyInvoice;
use App\Models\ThirdPartyOrder;
use App\Models\WarehouseStatus;
use App\Http\Requests\ThirdPartyInvoiceStoreRequest;
use App\Http\Resources\ThirdPartyInvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Services\Pusher;
use Services\SalesService;

class ThirdPartyInvoiceController extends Controller
{
    /**
     * List all third-party invoices with filters and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function all(Request $request)
    {
        $invoices = ThirdPartyInvoice::filter($request)
            ->orderByRaw('COALESCE(invoiced_at, created_at) DESC')
            ->paginate(20);

        return ThirdPartyInvoiceResource::collection($invoices);
    }

    /**
     * Match inventory item by sifraArtikla (SKU) or naziv (name).
     *
     * @param array $row
     * @return int|null
     */
    private function matchInventory(array $row): ?int
    {
        // First, try to match by sifraArtikla against inventory SKU
        // Compare as integers to handle any leading zeros on either side
        // e.g., SKU "000220" matches sifraArtikla 220, "220", or "000220"
        if (isset($row['sifraArtikla']) && $row['sifraArtikla'] !== '') {
            $sifraInt = (int) $row['sifraArtikla'];
            // Use sku + 0 to cast varchar to integer in SQL (works in MySQL and SQLite)
            $inventory = Inventory::whereRaw('sku + 0 = ?', [$sifraInt])->first();
            if ($inventory) {
                return $inventory->id;
            }
        }

        // Fallback: try to match by item name (case-insensitive)
        $itemName = $row['naziv'] ?? null;
        if ($itemName) {
            $inventory = Inventory::whereRaw('LOWER(name) = ?', [strtolower($itemName)])->first();
            if ($inventory) {
                return $inventory->id;
            }
        }

        return null;
    }

    /**
     * Store a third-party invoice from external system.
     *
     * @param ThirdPartyInvoiceStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ThirdPartyInvoiceStoreRequest $request)
    {
        $rows = $request->all();

        Log::info('[ThirdPartyInvoice] Incoming request', [
            'body' => $rows,
            'raw_content' => $request->getContent(),
            'ip' => $request->ip(),
        ]);

        if (empty($rows)) {
            Log::warning('[ThirdPartyInvoice] Empty data provided', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'No data provided',
            ], 422);
        }

        $firstRow = $rows[0];

        // Extract invoice-level data from first row (with safe defaults)
        $externalInvoiceId = isset($firstRow['racunid']) ? (int) $firstRow['racunid'] : null;
        $invoiceNumber = (string) ($firstRow['brojracuna'] ?? 'UNKNOWN-' . time());
        $tableName = isset($firstRow['sto']) ? (string) $firstRow['sto'] : null;
        $tableId = isset($firstRow['stoid']) ? (int) $firstRow['stoid'] : null;
        $externalOrderId = isset($firstRow['porudzbinaid']) ? (int) $firstRow['porudzbinaid'] : null;
        $discount = (float) ($firstRow['popust'] ?? 0);
        $stornoReferenceId = isset($firstRow['stornoreferenceid']) ? (int) $firstRow['stornoreferenceid'] : null;
        $invoicedAt = isset($firstRow['datum']) && !empty($firstRow['datum'])
            ? $firstRow['datum']
            : null;

        // Handle storno reference: mark the referenced invoice as storno and return early
        if ($stornoReferenceId) {
            try {
                $stornoedInvoice = ThirdPartyInvoice::findByExternalId($stornoReferenceId);

                if (!$stornoedInvoice) {
                    Log::warning('[ThirdPartyInvoice] Storno reference not found', [
                        'storno_reference_id' => $stornoReferenceId,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Referenced invoice not found',
                    ], 404);
                }

                if ($stornoedInvoice->isStorno()) {
                    Log::info('[ThirdPartyInvoice] Storno reference already processed (idempotent)', [
                        'storno_reference_id' => $stornoReferenceId,
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Invoice already stornoed',
                    ], 200);
                }

                $stornoedInvoice->markAsStorno();

                Log::info('[ThirdPartyInvoice] Storno reference processed', [
                    'storno_reference_id' => $stornoReferenceId,
                    'stornoed_invoice_id' => $stornoedInvoice->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice stornoed successfully',
                    'data' => [
                        'id' => $stornoedInvoice->id,
                        'invoice_number' => $stornoedInvoice->invoice_number,
                        'status' => ThirdPartyInvoice::STATUS_STORNO,
                    ],
                ], 200);
            } catch (\Exception $e) {
                Log::error('[ThirdPartyInvoice] Failed to process storno', [
                    'storno_reference_id' => $stornoReferenceId,
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

        // Determine payment type and total from payment fields
        $paymentType = null;
        $gotovina = (float) ($firstRow['gotovina'] ?? 0);
        $kartica = (float) ($firstRow['kartica'] ?? 0);
        $prenosNaRacun = (float) ($firstRow['prenosnaracun'] ?? 0);
        $stornoPorudzbine = (float) ($firstRow['stornoporudzbine'] ?? 0);

        if ($gotovina > 0) {
            $paymentType = ThirdPartyInvoice::PAYMENT_CASH;
            $totalCents = (int) round($gotovina);
        } elseif ($kartica > 0) {
            $paymentType = ThirdPartyInvoice::PAYMENT_CARD;
            $totalCents = (int) round($kartica);
        } elseif ($prenosNaRacun > 0) {
            $paymentType = ThirdPartyInvoice::PAYMENT_TRANSFER;
            $totalCents = (int) round($prenosNaRacun);
        } elseif ($stornoPorudzbine > 0) {
            $paymentType = ThirdPartyInvoice::PAYMENT_KASA_I;
            $totalCents = (int) round($stornoPorudzbine);
        } else {
            $totalCents = 0;
        }

        // Transform rows into items array
        $items = [];
        $matchedItems = []; // Track items with inventory matches

        foreach ($rows as $row) {
            $qty = (float) ($row['kolicina'] ?? 0);
            $price = (float) ($row['cena'] ?? 0);

            // Try to match inventory
            $inventoryId = $this->matchInventory($row);

            $item = [
                'name' => (string) ($row['naziv'] ?? 'Unknown'),
                'qty' => $qty,
                'price' => (int) round($price),
                'unit' => (string) ($row['jm'] ?? 'kom'),
                'storno' => !empty($row['stornirano']),
            ];

            // Store inventory_id if matched
            if ($inventoryId !== null) {
                $item['inventory_id'] = $inventoryId;
                $matchedItems[] = [
                    'inventory_id' => $inventoryId,
                    'qty' => $qty,
                    'name' => $item['name'],
                    'price' => $item['price'],
                ];
            }

            $items[] = $item;
        }

        // Check for duplicate
        $isDuplicate = ThirdPartyInvoice::isDuplicate($invoiceNumber);

        $ordersDeleted = 0;

        try {
            // Use transaction for invoice creation + order deletion
            $invoice = DB::transaction(function () use (
                $externalInvoiceId,
                $invoiceNumber,
                $isDuplicate,
                $externalOrderId,
                $tableId,
                $tableName,
                $items,
                $totalCents,
                $paymentType,
                $discount,
                $invoicedAt,
                &$ordersDeleted
            ) {
                // Create invoice
                $invoice = ThirdPartyInvoice::create([
                    'external_invoice_id' => $externalInvoiceId,
                    'invoice_number' => $invoiceNumber,
                    'is_duplicate' => $isDuplicate,
                    'external_order_id' => $externalOrderId,
                    'table_name' => $tableName,
                    'status' => ThirdPartyInvoice::STATUS_PAYED,
                    'order' => $items,
                    'total' => $totalCents,
                    'payment_type' => $paymentType,
                    'discount' => $discount,
                    'invoiced_at' => $invoicedAt,
                ]);

                // Delete all orders for this table if stoid is present
                if ($tableId) {
                    $ordersDeleted = ThirdPartyOrder::deleteByTableId($tableId);
                }

                return $invoice;
            });

            // Populate warehouse (skip duplicates to avoid double-deduction)
            if (!$isDuplicate && !empty($matchedItems)) {
                // Parse date from request if available
                $invoiceDate = null;
                if (isset($firstRow['datum']) && !empty($firstRow['datum'])) {
                    try {
                        $parsedDate = \Carbon\Carbon::parse($firstRow['datum'])
                            ->timezone('Europe/Belgrade');
                        // Apply working day logic: sales between midnight and 4am count as previous day
                        if ($parsedDate->hour < 4) {
                            $parsedDate = $parsedDate->subDay();
                        }
                        $invoiceDate = $parsedDate->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Fall back to default (current working day)
                        $invoiceDate = null;
                    }
                }

                foreach ($matchedItems as $matchedItem) {
                    try {
                        SalesService::populateWarehouseForThirdParty(
                            $matchedItem['inventory_id'],
                            $matchedItem['qty'],
                            $invoice->id,
                            $invoiceDate
                        );
                    } catch (\Exception $e) {
                        Log::error('[ThirdPartyInvoice] Warehouse population failed', [
                            'invoice_id' => $invoice->id,
                            'inventory_id' => $matchedItem['inventory_id'],
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Create sales records for matched items
                try {
                    SalesService::createSalesForThirdParty(
                        $matchedItems,
                        $invoice->id,
                        $invoiceDate
                    );
                } catch (\Exception $e) {
                    Log::error('[ThirdPartyInvoice] Sales creation failed', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Notify backoffice of new data
            try {
                app(Pusher::class)->trigger('broadcasting', 'tables-update', []);
            } catch (\Exception $e) {
                Log::error('[ThirdPartyInvoice] Pusher notification failed', [
                    'error' => $e->getMessage(),
                ]);
            }

            $message = 'Invoice stored successfully';
            if ($ordersDeleted > 0) {
                $message .= ', ' . $ordersDeleted . ' order(s) for table closed';
            }
            if ($isDuplicate) {
                $message .= ' (duplicate detected)';
            }

            Log::info('[ThirdPartyInvoice] Invoice stored successfully', [
                'id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
                'is_duplicate' => $isDuplicate,
                'total' => $totalCents,
                'table_id' => $tableId,
                'orders_deleted' => $ordersDeleted,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'is_duplicate' => $invoice->is_duplicate,
                    'status' => $invoice->status,
                    'total' => $invoice->total,
                    'orders_deleted' => $ordersDeleted,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('[ThirdPartyInvoice] Failed to store invoice', [
                'invoice_number' => $invoiceNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a third-party invoice and associated warehouse status records.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $invoice = ThirdPartyInvoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        try {
            DB::transaction(function () use ($invoice) {
                // Delete associated warehouse status records
                WarehouseStatus::where('batch_id', $invoice->id)->delete();

                // Delete associated sales records
                Sales::where('batch_id', $invoice->id)->delete();

                // Delete the invoice
                $invoice->delete();
            });

            Log::info('[ThirdPartyInvoice] Invoice deleted', [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('[ThirdPartyInvoice] Failed to delete invoice', [
                'id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
