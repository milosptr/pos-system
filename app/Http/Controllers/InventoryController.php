<?php

namespace App\Http\Controllers;

use Services\Pusher;
use App\Exports\InventoryExport;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\InventoryCollection;
use App\Http\Requests\InventoryStoreRequest;
use App\Http\Resources\InventoryBackofficeResource;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $inventory = Cache::remember('inventory-all', 60, function () use ($request) {
            return Inventory::filter($request)->get();
        });
        return InventoryResource::collection($inventory);
    }

    public function allBackoffice(Request $request)
    {
        return InventoryBackofficeResource::collection(Inventory::with('category')->filter($request)->get());
    }

    public function index(Request $request)
    {
        $inventory = Cache::remember('inventory', 60, function () use ($request) {
            return Inventory::filter($request)->get();
        });
        return InventoryResource::collection($inventory);
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
        $item = Inventory::create($request->all());
        Cache::forget('inventory-all');
        Cache::forget('inventory');
        $this->broadcastMenuUpdate();
        return new InventoryResource($item);
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
        Cache::forget('inventory-all');
        Cache::forget('inventory');
        $this->broadcastMenuUpdate();
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
        Cache::forget('inventory-all');
        Cache::forget('inventory');
        $this->broadcastMenuUpdate();
        return $inventory->delete();
    }

    public function export()
    {
        return Excel::download(new InventoryExport, 'inventory.xlsx');
    }

    /**
     * Notify the POS that the menu (items or categories) changed so it can
     * re-fetch instead of showing a stale menu until a manual reload.
     */
    private function broadcastMenuUpdate()
    {
        try {
            app(Pusher::class)->trigger('broadcasting', 'menu-update', []);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
