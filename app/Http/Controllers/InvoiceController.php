<?php

namespace App\Http\Controllers;

use App\Models\WarehouseStatus;
use Exception;
use Carbon\Carbon;
use Services\Pusher;
use App\Models\Order;
use App\Models\Sales;
use App\Models\Invoice;
use App\Models\KitchenOrder;
use Services\WorkingDay;
use Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $invoices = Invoice::filter($request)->orderBy('id', 'DESC');
        if($request->get('paginate')) {
            return InvoiceResource::collection($invoices->paginate(20));
        }
        return InvoiceResource::collection($invoices->get());
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceStoreRequest $request, $orderID = null)
    {
        $invoice = Invoice::create($request->all());
        if ($invoice) {
            if($orderID) {
                Order::where('id', $orderID)->delete();
                KitchenOrder::where('orderable_type', 'order')->where('orderable_id', $orderID)->delete();
            }
            if(!$orderID) {
                $orderIds = Order::where('table_id', $request->get('table_id'))->pluck('id')->toArray();
                Order::where('table_id', $request->get('table_id'))->delete();
                if (!empty($orderIds)) {
                    KitchenOrder::where('orderable_type', 'order')->whereIn('orderable_id', $orderIds)->delete();
                }
            }
            if($invoice->status !== Invoice::STATUS_REFUNDED) {
                SalesService::parseAndSaveOrder($request->get('order'), $invoice);
            }
            try {
                app(Pusher::class)->trigger('broadcasting', 'tables-update', []);
                app(Pusher::class)->trigger('broadcasting', 'kitchen-update', []);
            } catch(Exception $e) {
                Log::error($e->getMessage());
            }
        }
        return new InvoiceResource($invoice);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function refund($id, Request $request)
    {
      $invoice = Invoice::find($id);
      if($invoice) {
          $invoice->update($request->all());
          try {
            Sales::where('invoice_id', $invoice->id)->delete();
            WarehouseStatus::where('batch_id', (string) $invoice->id)->delete();
          } catch(Exception $e) {
            Log::error($e->getMessage());
          }
      }
      return InvoiceResource::collection(Invoice::whereBetween('created_at', WorkingDay::getWorkingDay())->orderBy('id', 'DESC')->get());
    }

    public function todayTransactions()
    {
        return Invoice::selectRaw('sum(total) AS total')
          ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
          ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
          ->selectRaw('(sum(total) - sum(case when status = 0 then total else 0 end)) - sum(case when status = 2 then total else 0 end) as income')
          ->selectRaw('70000 as maximum')
          ->whereBetween('created_at', WorkingDay::getWorkingDay())
          ->get()
          ->first();
    }
}
