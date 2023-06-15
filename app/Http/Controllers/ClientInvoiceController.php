<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ClientInvoice;

class ClientInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->has('status') ? [$request->get('status')] : [ClientInvoice::STATUS_PAID, ClientInvoice::STATUS_CANCELLED];
        $incomingInvoices = ClientInvoice::with('clientAccount')
          ->where(function ($query) {
              $query->whereNull('transaction_date')
                ->orWhere('status', ClientInvoice::STATUS_PENDING);
          })
          ->whereNot('status', [ClientInvoice::STATUS_PAID, ClientInvoice::STATUS_CANCELLED])
          ->orderBy('transaction_date', 'asc')
          ->get();

        $historyInvoices = ClientInvoice::with('clientAccount')
        ->whereIn('status', $status);

        if($request->has('client_account')) {
            $historyInvoices = $historyInvoices->where('client_account', $request->get('client_account'));
        }
        if($request->has('date_from') && $request->has('date_to')) {
            $historyInvoices = $historyInvoices->whereBetween('transaction_date', [$request->get('date_from'), $request->get('date_to')]);
        } else {
            $historyInvoices = $historyInvoices->whereBetween('transaction_date', [Carbon::now()->startOfMonth(), Carbon::now()]);
        }

        $historyInvoices = $historyInvoices->orderBy('transaction_date', 'desc')
        ->get();

        return [
          'incomingInvoices' => $incomingInvoices,
          'historyInvoices' => $historyInvoices
        ];
    }

    public function update(Request $request, $id)
    {
        $invoice = ClientInvoice::find($id);
        $invoice->update($request->all());
        return $invoice;
    }

    public function store(Request $request)
    {
        $invoice = ClientInvoice::create($request->all());
        return $invoice;
    }
}
