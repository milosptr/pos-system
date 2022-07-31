<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Services\WorkingDay;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
      $stats = Invoice::filterStats($request)->first();
      $invoices = InvoiceResource::collection(Invoice::filter($request)->get());
      return [
        'stats' => $stats,
        'invoices' => $invoices,
      ];
    }
}
