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
        $invoices = InvoiceResource::collection(Invoice::filter($request)->orderBy('id', 'desc')->get());
      }

      if($type == 1) {
        $stats = Sales::filterStats($request)->first();
        $sales = Sales::filter($request)
          ->selectRaw('sales.inventory_id, sum(sales.qty) as qty, sum(sales.total) as total, inventory.name as name')
          ->leftJoin('inventory', 'inventory.id', '=', 'sales.inventory_id')
          ->groupBy('sales.inventory_id', 'inventory.name')
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
