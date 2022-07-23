<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Services\Pusher;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Resources\InvoiceResource;
use App\Http\Requests\InvoiceStoreRequest;
use Services\WorkingDay;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        return InvoiceResource::collection(Invoice::all());
    }

    public function allForToday()
    {
      return InvoiceResource::collection(Invoice::whereBetween('created_at', WorkingDay::getWorkingDay())->orderBy('id', 'DESC')->get());
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
        app(Pusher::class)->trigger('broadcasting', 'tables-update', []);
      }
      return $invoice;
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $id
     * @return \Illuminate\Http\Response
     */
    public function refund($id)
    {
      $invoice = Invoice::find($id);
      if($invoice)
        $invoice->update(['status' => Invoice::STATUS_REFUNDED]);
      return InvoiceResource::collection(Invoice::whereBetween('created_at', WorkingDay::getWorkingDay())->orderBy('id', 'DESC')->get());
    }

    public function todayTransactions()
    {
      return Invoice::selectRaw('sum(total) AS total')
        ->selectRaw('sum(case when status = 0 then total else 0 end) as storno')
        ->selectRaw('(sum(total) - sum(case when status = 0 then total else 0 end)) as neto')
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
