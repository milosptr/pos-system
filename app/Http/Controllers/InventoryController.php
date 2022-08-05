<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\InventoryCollection;
use App\Http\Requests\InventoryStoreRequest;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $inventory = Cache::remember('inventory', env('CACHE_TIME', 3600), function() use ($request) {
            return Inventory::filter($request)->get();
        });

        return new InventoryCollection($inventory);
    }

    public function index(Request $request)
    {
        $inventory = Cache::remember('active-inventory', env('CACHE_TIME', 3600), function() use($request) {
            return Inventory::filter($request)->where('active', 1)->get();
        });

        return new InventoryCollection($inventory);
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
    public function store(InventoryStoreRequest $request)
    {
        return new InventoryResource(Inventory::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function show(Inventory $inventory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function edit(Inventory $inventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $item = Inventory::find($id);
        $item->update($request->all());
        return new InventoryCollection(Inventory::all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $inventory = Inventory::find($id);
        return $inventory->delete();
    }
}
