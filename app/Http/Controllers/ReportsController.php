<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Sales;
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
            $stats = Invoice::filterStats($request)->first();
            $invoices = Invoice::filter($request)
              ->selectRaw('sum(total) as total, DATE(DATE_SUB(created_at, INTERVAL 4 HOUR)) as date')
              ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
              ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
              ->selectRaw('sum(total) - sum(case when status = 0 then total else 0 end) - sum(case when status = 2 then total else 0 end) as income')
              ->orderBy('date', 'desc')
              ->groupBy('date')
              ->get();
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
              ->groupBy('sales.inventory_id', 'inventory.name', 'sales.category_name', 'sales.name')
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
