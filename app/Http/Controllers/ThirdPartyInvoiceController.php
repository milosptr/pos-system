<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyInvoice;
use App\Models\ThirdPartyOrder;
use App\Http\Requests\ThirdPartyInvoiceStoreRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThirdPartyInvoiceController extends Controller
{
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

        // Extract invoice-level data from first row
        $invoiceNumber = $firstRow['brojracuna'];
        $tableName = $firstRow['sto'] ?? null;
        $externalOrderId = $firstRow['porudzbinaid'] ?? null;
        $discount = $firstRow['popust'] ?? 0;

        // Detect storno
        $isStorno = isset($firstRow['stornoporudzbine']) && $firstRow['stornoporudzbine'] == 1;
        $status = $isStorno ? ThirdPartyInvoice::STATUS_STORNO : ThirdPartyInvoice::STATUS_PAYED;

        // Determine payment type
        $paymentType = null;
        if (!$isStorno) {
            $gotovina = $firstRow['gotovina'] ?? 0;
            $kartica = $firstRow['kartica'] ?? 0;
            $prenosNaRacun = $firstRow['prenosnaracun'] ?? 0;

            if ($gotovina > 0) {
                $paymentType = ThirdPartyInvoice::PAYMENT_CASH;
            } elseif ($kartica > 0) {
                $paymentType = ThirdPartyInvoice::PAYMENT_CARD;
            } elseif ($prenosNaRacun > 0) {
                $paymentType = ThirdPartyInvoice::PAYMENT_TRANSFER;
            }
        }

        // Transform rows into items array
        $items = [];
        $totalCents = 0;

        foreach ($rows as $row) {
            $qty = $row['kolicina'];
            $price = $row['cena'];
            $itemTotalCents = (int) round($qty * $price * 100);
            $totalCents += $itemTotalCents;

            $item = [
                'name' => $row['naziv'],
                'qty' => $qty,
                'price' => (int) round($price * 100),
                'unit' => $row['jm'] ?? 'kom',
            ];

            // Add optional fields for storno invoices
            if ($isStorno && isset($row['originalnacena'])) {
                $item['original_price'] = (int) round($row['originalnacena'] * 100);
            }

            $items[] = $item;
        }

        // Check for duplicate
        $isDuplicate = ThirdPartyInvoice::isDuplicate($invoiceNumber);

        $orderDeleted = false;

        try {
            // Use transaction for invoice creation + order deletion
            $invoice = DB::transaction(function () use (
                $invoiceNumber,
                $isDuplicate,
                $externalOrderId,
                $tableName,
                $status,
                $items,
                $totalCents,
                $paymentType,
                $discount,
                &$orderDeleted
            ) {
                // Create invoice
                $invoice = ThirdPartyInvoice::create([
                    'invoice_number' => $invoiceNumber,
                    'is_duplicate' => $isDuplicate,
                    'external_order_id' => $externalOrderId,
                    'table_name' => $tableName,
                    'status' => $status,
                    'order' => $items,
                    'total' => $totalCents,
                    'payment_type' => $paymentType,
                    'discount' => $discount,
                ]);

                // Delete matching order if external_order_id is present
                if ($externalOrderId) {
                    $orderDeleted = ThirdPartyOrder::deleteByExternalOrderId($externalOrderId);
                }

                return $invoice;
            });

            $message = 'Invoice stored successfully';
            if ($orderDeleted) {
                $message .= ', matching order deleted';
            }
            if ($isDuplicate) {
                $message .= ' (duplicate detected)';
            }

            Log::info('[ThirdPartyInvoice] Invoice stored successfully', [
                'id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
                'is_duplicate' => $isDuplicate,
                'status' => $status,
                'total' => $totalCents,
                'order_deleted' => $orderDeleted,
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
                    'order_deleted' => $orderDeleted,
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
}
