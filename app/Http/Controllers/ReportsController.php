<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Sales;
use App\Models\ThirdPartyInvoice;
use Illuminate\Http\Request;
use Services\WorkingDay;

class ReportsController extends Controller
{
    public function index(Request $request, $type)
    {
        $stats = [];
        $invoices = [];
        $sales = [];
        if($type == 0) {
            // Stats: ThirdPartyInvoice breakdown
            $tpStats = ThirdPartyInvoice::filter($request)
                ->selectRaw('sum(case when payment_type = 1 then total else 0 end) as gotovina')
                ->selectRaw('sum(case when payment_type = 2 then total else 0 end) as kartica')
                ->selectRaw('sum(case when payment_type = 3 then total else 0 end) as prenos')
                ->selectRaw('sum(case when payment_type = 4 then total else 0 end) as kasa_i')
                ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
                ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
                ->first();

            // Stats: Invoice (→ Kasa I bucket)
            $invStats = Invoice::filter($request)
                ->selectRaw('sum(total) as kasa_i')
                ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
                ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
                ->first();

            $stats = [
                'gotovina' => (int) ($tpStats->gotovina ?? 0),
                'kartica' => (int) ($tpStats->kartica ?? 0),
                'prenos' => (int) ($tpStats->prenos ?? 0),
                'kasa_i' => (int) ($tpStats->kasa_i ?? 0) + (int) ($invStats->kasa_i ?? 0),
                'onthehouse' => (int) ($tpStats->onthehouse ?? 0) + (int) ($invStats->onthehouse ?? 0),
                'refund' => (int) ($tpStats->refund ?? 0) + (int) ($invStats->refund ?? 0),
            ];
            $stats['income'] = $stats['gotovina'] + $stats['kartica'] + $stats['prenos'] + $stats['kasa_i'] - $stats['onthehouse'] - $stats['refund'];

            // Daily breakdown: ThirdPartyInvoice by date
            $tpByDate = ThirdPartyInvoice::filter($request)
                ->selectRaw('DATE(DATE_SUB(COALESCE(invoiced_at, created_at), INTERVAL 4 HOUR)) as date')
                ->selectRaw('sum(case when payment_type = 1 then total else 0 end) as gotovina')
                ->selectRaw('sum(case when payment_type = 2 then total else 0 end) as kartica')
                ->selectRaw('sum(case when payment_type = 3 then total else 0 end) as prenos')
                ->selectRaw('sum(case when payment_type = 4 then total else 0 end) as kasa_i')
                ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
                ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
                ->groupBy('date')
                ->get()->keyBy('date');

            // Daily breakdown: Invoice by date (→ Kasa I bucket)
            $invByDate = Invoice::filter($request)
                ->selectRaw('DATE(DATE_SUB(created_at, INTERVAL 4 HOUR)) as date')
                ->selectRaw('sum(total) as kasa_i')
                ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
                ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
                ->groupBy('date')
                ->get()->keyBy('date');

            // Merge by date
            $allDates = $tpByDate->keys()->merge($invByDate->keys())->unique()->sortDesc();
            $invoices = $allDates->map(function ($date) use ($tpByDate, $invByDate) {
                $tp = $tpByDate->get($date);
                $inv = $invByDate->get($date);
                $row = [
                    'date' => $date,
                    'gotovina' => (int) ($tp->gotovina ?? 0),
                    'kartica' => (int) ($tp->kartica ?? 0),
                    'prenos' => (int) ($tp->prenos ?? 0),
                    'kasa_i' => (int) ($tp->kasa_i ?? 0) + (int) ($inv->kasa_i ?? 0),
                    'onthehouse' => (int) ($tp->onthehouse ?? 0) + (int) ($inv->onthehouse ?? 0),
                    'refund' => (int) ($tp->refund ?? 0) + (int) ($inv->refund ?? 0),
                ];
                $row['income'] = $row['gotovina'] + $row['kartica'] + $row['prenos'] + $row['kasa_i'] - $row['onthehouse'] - $row['refund'];
                return $row;
            })->values();
        }

        if($type == 1) {
            $stats = Sales::filterStats($request)->first();
            $sales = Sales::filter($request)
              ->selectRaw('sales.inventory_id,
                sum(case when sales.type = 1 then sales.qty else 0 end) as epos,
                sum(case when sales.type = 2 then sales.qty else 0 end) as ebar,
                sum(sales.qty) as qty,
                sum(sales.total) as total,
                sales.category_name as category,
                inventory.name as name')
              ->leftJoin('inventory', 'inventory.id', '=', 'sales.inventory_id')
              ->groupBy('sales.inventory_id', 'inventory.name', 'sales.category_name', 'inventory.category_id')
              ->orderBy('inventory.category_id', 'ASC')
              ->orderBy('qty', 'DESC')
              ->get();
        }

        return [
          'stats' => $stats,
          'invoices' => $invoices,
          'sales' => $sales,
        ];
    }
}
