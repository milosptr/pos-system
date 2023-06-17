<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ClientInvoice;

class ClientInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->has('status') ? [(int) $request->get('status')] : [ClientInvoice::STATUS_PAID];
        $incomingInvoices = ClientInvoice::with('clientAccount');
        $historyInvoices = ClientInvoice::with('clientAccount');

        if($request->has('client_account')) {
            $incomingInvoices = $incomingInvoices->where('client_account', $request->get('client_account'));
            $historyInvoices = $historyInvoices->where('client_account', $request->get('client_account'));
        }

        if($request->has('date_from') && $request->has('date_to')) {
            $incomingInvoices = $incomingInvoices->whereBetween('payment_deadline', [$request->get('date_from'), $request->get('date_to')]);
            $historyInvoices = $historyInvoices->whereBetween('payment_deadline', [$request->get('date_from'), $request->get('date_to')]);
        } else {
            $incomingInvoices = $incomingInvoices->whereBetween('payment_deadline', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            $historyInvoices = $historyInvoices->whereBetween('payment_deadline', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }

        if($request->has('sort')) {
            $incomingInvoices = $incomingInvoices->orderBy($request->get('sort'), 'asc');
            $historyInvoices = $historyInvoices->orderBy($request->get('sort'), 'desc');
        } else {
            $incomingInvoices = $incomingInvoices->orderBy('payment_deadline', 'asc');
            $historyInvoices = $historyInvoices->orderBy('transaction_date', 'desc');
        }

        $incomingInvoices = $incomingInvoices->whereIn('status', [0])->get();
        $historyInvoices = $historyInvoices->whereIn('status', $status)->get();

        if($request->has('status') && (int) $request->get('status') === 0) {
            $historyInvoices = [];
        } elseif((int) $request->get('status') === 1 || (int) $request->get('status') === 2) {
            $incomingInvoices = [];
        }

        return [
          'incomingInvoices' => $incomingInvoices,
          'historyInvoices' => $historyInvoices
        ];
    }

    public function update(Request $request, $id)
    {
        $invoice = ClientInvoice::find($id);
        $data = $request->all();
        if($request->has('status') && (int) $request->get('status') === 1) {
            $data['processed_at'] = Carbon::now();
        } else {
            $data['processed_at'] = null;
        }
        $invoice->update($data);
        return $invoice;
    }

    public function store(Request $request)
    {
        $invoice = ClientInvoice::create($request->all());
        return $invoice;
    }
}
