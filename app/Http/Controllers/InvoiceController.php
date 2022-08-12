<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Services\Pusher;
use App\Models\Order;
use App\Models\Invoice;
use Services\WorkingDay;
use Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\InvoiceResource;
use App\Http\Requests\InvoiceStoreRequest;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return InvoiceResource::collection(Invoice::filter($request)->orderBy('id', 'DESC')->get());
    }

    public function allForToday()
    {
      return InvoiceResource::collection(Invoice::whereBetween('created_at', WorkingDay::getWorkingDay())->orderBy('id', 'DESC')->get());
    }

    public function dailyMaximum()
    {
      return Invoice::selectRaw('sum(case when status = 1 then total else 0 end) as total')->groupBy(DB::raw('Date(created_at)'))->orderBy('total', 'desc')->first();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceStoreRequest $request, $orderID = null)
    {
      $invoice = Invoice::create($request->all());
      if ($invoice) {
        if($orderID) Order::where('id', $orderID)->delete();
        if(!$orderID) Order::where('table_id', $request->get('table_id'))->delete();
        SalesService::parseAndSaveOrder($request->get('order'), $invoice);
        app(Pusher::class)->trigger('broadcasting', 'tables-update', []);
      }
      return new InvoiceResource($invoice);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $id
     * @return \Illuminate\Http\Response
     */
    public function refund($id, Request $request)
    {
      $invoice = Invoice::find($id);
      if($invoice)
        $invoice->update($request->all());
      return InvoiceResource::collection(Invoice::whereBetween('created_at', WorkingDay::getWorkingDay())->orderBy('id', 'DESC')->get());
    }

    public function todayTransactions()
    {
      return Invoice::selectRaw('sum(total) AS total')
        ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
        ->selectRaw('(sum(total) - sum(case when status = 0 then total else 0 end)) as income')
        ->selectRaw('70000 as maximum')
        ->whereBetween('created_at', WorkingDay::getWorkingDay())
        ->get()
        ->first();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
