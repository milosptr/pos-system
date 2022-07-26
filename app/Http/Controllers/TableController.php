<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;
use App\Http\Resources\TableResource;
use App\Http\Resources\TableCollection;
use App\Http\Requests\TableStoreRequest;

class TableController extends Controller
{

    public function index($id)
    {
      return new TableResource(Table::find($id));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        return TableResource::collection(Table::all());
    }
    /**
     * Display a listing of the resource for area.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexForArea($id)
    {
        return  TableResource::collection(Table::where('area', $id)->get());
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
    public function store(TableStoreRequest $request)
    {
        return new TableResource(Table::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function show(Table $table)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function available()
    {
        $takenIds = array_unique(Order::all()->pluck('table_id')->toArray());
        return TableResource::collection(Table::whereNotIn('id', $takenIds)->get());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $table = Table::find($id);
        $table->update($request->all());
        return TableResource::collection(Table::all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function destroy(Table $table)
    {
        //
    }
}
