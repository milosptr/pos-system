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
        $invoices = InvoiceResource::collection(Invoice::filter($request)->get());
      }

      if($type == 1) {
        $stats = Sales::filterStats($request)->first();
        $sales = Sales::filter($request)->get();
      }

      return [
        'stats' => $stats,
        'invoices' => $invoices,
        'sales' => $sales,
      ];
    }
}
