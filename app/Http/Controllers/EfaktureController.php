<?php

namespace App\Http\Controllers;

use App\Http\Requests\EfaktureStoreRequest;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Services\EfaktureService;

class EfaktureController extends Controller
{
    /**
     * Upper bound on invoices per request. A full backlog is a few hundred;
     * this only guards against runaway payloads.
     */
    public const MAX_ROWS = 1000;

    /**
     * Payloads up to this many invoices are logged in full; line items make
     * larger ones too fat for the log.
     */
    private const LOG_FULL_BODY_MAX_ROWS = 20;

    /**
     * Import supplier invoices pulled from SEF (e-fakture).
     *
     * @param EfaktureStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EfaktureStoreRequest $request)
    {
        $rows = $request->all();

        if (count($rows) <= self::LOG_FULL_BODY_MAX_ROWS) {
            Log::info('[Efakture] Incoming request', [
                'body' => $rows,
                'ip' => $request->ip(),
            ]);
        } else {
            Log::info('[Efakture] Incoming request (large payload)', [
                'rows' => count($rows),
                'sef_ids' => collect($rows)->pluck('sef_id')->filter()->values()->toArray(),
                'ip' => $request->ip(),
            ]);
        }

        if (empty($rows)) {
            Log::warning('[Efakture] Empty data provided', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'No data provided',
            ], 422);
        }

        if (count($rows) > self::MAX_ROWS) {
            Log::warning('[Efakture] Payload too large', [
                'rows' => count($rows),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Too many invoices in one request (max ' . self::MAX_ROWS . ')',
            ], 422);
        }

        // The same backlog sent twice must not race itself into duplicate
        // suppliers; process one import at a time.
        try {
            return Cache::lock('efakture-import', 60)
                ->block(10, fn () => $this->processInvoices($rows));
        } catch (LockTimeoutException $e) {
            Log::warning('[Efakture] Another import is still running', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Another import is still being processed, retry shortly',
            ], 429);
        }
    }

    /**
     * Save each invoice in its own transaction so one bad invoice cannot cost
     * the owner the rest of his backlog.
     *
     * @param array $rows
     * @return \Illuminate\Http\JsonResponse
     */
    private function processInvoices(array $rows)
    {
        $suppliers = EfaktureService::loadSuppliers();
        $summary = [
            'processed' => 0,
            'created' => 0,
            'skipped' => 0,
            'items' => 0,
            'invalid_rows' => 0,
            'failed' => [],
        ];

        foreach ($rows as $index => $row) {
            try {
                if (!is_array($row)) {
                    throw new InvalidArgumentException('Invoice is not an object');
                }

                $data = EfaktureService::normalizeInvoice($row);

                // Already imported: leave it exactly as it is, whatever the
                // owner has done with it since.
                if (EfaktureService::alreadyImported($data['sef_id'])) {
                    $summary['skipped']++;
                    Log::info('[Efakture] Invoice already imported, ignored', [
                        'sef_id' => $data['sef_id'],
                    ]);
                    continue;
                }

                DB::transaction(function () use ($data, $suppliers, &$summary) {
                    $supplier = EfaktureService::resolveSupplier($data, $suppliers);
                    $invoice = EfaktureService::createInvoice($data, $supplier);
                    $summary['items'] += EfaktureService::saveItems($invoice, $data['items']);
                });

                $summary['processed']++;
                $summary['created']++;
            } catch (InvalidArgumentException $e) {
                $summary['invalid_rows']++;
                $summary['failed'][] = self::failure($index, $row, $e);
                Log::warning('[Efakture] Invalid invoice skipped', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                ]);
            } catch (\Throwable $e) {
                $summary['failed'][] = self::failure($index, $row, $e);
                Log::error('[Efakture] Failed to process invoice', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // The rolled-back transaction may have created or backfilled a
                // supplier that only exists in memory now; start the next
                // invoice from what the database actually holds. If even that
                // fails the database is gone, so stop and report what landed.
                try {
                    $suppliers = EfaktureService::loadSuppliers();
                } catch (\Throwable $reloadFailed) {
                    Log::error('[Efakture] Aborting import, database unreachable', [
                        'error' => $reloadFailed->getMessage(),
                    ]);
                    break;
                }
            }
        }

        $anyFailed = !empty($summary['failed']);

        Log::info('[Efakture] Invoices processed', [
            'summary' => array_merge($summary, [
                'failed' => collect($summary['failed'])->pluck('sef_id')->toArray(),
            ]),
        ]);

        $message = $summary['processed'] . ' invoice(s) imported';

        if ($summary['skipped'] > 0) {
            $message .= ', ' . $summary['skipped'] . ' already imported';
        }

        if ($anyFailed) {
            $message .= ', ' . count($summary['failed']) . ' failed';
        }

        // Every payload we could read answers 2xx, even when nothing landed:
        // the whole point of the failed list is that his script can print it,
        // and a 4xx hides it behind raise_for_status(). Only a payload we
        // could not read at all (empty, oversized) is a 422, and that is
        // decided before we get here.
        return response()->json([
            'success' => !$anyFailed,
            'message' => $message,
            'summary' => $summary,
        ], $summary['processed'] > 0 && !$anyFailed ? 201 : 200);
    }

    /**
     * @param int|string $index
     * @param mixed $row
     * @param \Throwable $e
     * @return array
     */
    private static function failure($index, $row, \Throwable $e): array
    {
        return [
            'index' => $index,
            'sef_id' => is_array($row) && isset($row['sef_id']) ? $row['sef_id'] : null,
            'broj_racuna' => is_array($row) && isset($row['broj_racuna']) ? $row['broj_racuna'] : null,
            'error' => $e->getMessage(),
        ];
    }
}
