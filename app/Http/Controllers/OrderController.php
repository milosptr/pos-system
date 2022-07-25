<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderCollection;
use App\Http\Requests\OrderStoreRequest;
use Services\Pusher;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return OrderResource::collection(Order::all());
    }

    /**
     * Display a listing for table of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexForTable($id)
    {
        return new OrderCollection(Order::where('table_id', $id)->orderBy('id', 'DESC')->get());
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
    public function store(OrderStoreRequest $request)
    {
        $order = Order::create($request->all());

        if($order)
            app(Pusher::class)->trigger('broadcasting', 'tables-update', []);

        return new OrderResource($order);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $order = Order::find($id);
      $order->update($request->only(['order', 'total', 'table_id']));
      app(Pusher::class)->trigger('broadcasting', 'tables-update', []);
      return $order;
    }

    public function move($fromId, $toId)
    {
      $orders = Order::where('table_id', $fromId)->get();
      // foreach order move from table to table
      foreach($orders as $order) {
        $order->table_id = $toId;
        $order->save();
      }
      app(Pusher::class)->trigger('broadcasting', 'tables-update', []);
      return response('Order moved!', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
