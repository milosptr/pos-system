<?php

namespace App\Http\Controllers;

use App\Models\WarehouseInventory;
use Illuminate\Http\Request;

class WarehouseInventoryController extends Controller
{
    public function index()
    {
        return WarehouseInventory::all();
    }
    public function indexForInventory($id)
    {
        return WarehouseInventory::where('inventory_id', $id)->get();
    }

    public function show($id)
    {
        return WarehouseInventory::findOrFail($id);
    }

    public function store(Request $request)
    {
        try {
          $data = $request->all();
          foreach($data as $d) {
            if($d['deleted'] === true && $d['id']) {
              $wi = WarehouseInventory::findOrFail($d['id']);
              $wi->delete();
              continue;
            }
            if($d['deleted'] === true)
              continue;

            if($d['id']) {
              $wi = WarehouseInventory::findOrFail($d['id']);
              $wi->warehouse_id = $d['warehouse_id'];
              $wi->inventory_id = $d['inventory_id'];
              $wi->norm = $d['norm'];
              $wi->save();
            } else {
              $wi = new WarehouseInventory();
              $wi->warehouse_id = $d['warehouse_id'];
              $wi->inventory_id = $d['inventory_id'];
              $wi->norm = $d['norm'];
              $wi->save();
            }
          }
            return response()->json([
                'status' => 'success',
                'message' => 'WarehouseInventory created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'WarehouseInventory creation failed',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $wi = WarehouseInventory::findOrFail($id);
            $wi->warehouse_id = $request->get('warehouse_id');
            $wi->inventory_id = $request->get('inventory_id');
            $wi->norm = $request->get('norm');
            $wi->save();
            return response()->json([
                'status' => 'success',
                'message' => 'WarehouseInventory updated successfully',
                'data' => $wi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'WarehouseInventory update failed',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        $wi = WarehouseInventory::findOrFail($id);
        $wi->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'WarehouseInventory deleted successfully',
            'data' => $wi
        ], 200);
    }
}
