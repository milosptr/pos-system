<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseResource;
use App\Models\WarehouseStatus;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('order', 'asc')->get();
        return WarehouseResource::collection($warehouses);
    }

    public function indexByCategory($id)
    {
        $warehouses = Warehouse::where('category_id', $id)->orderBy('order', 'asc')->get();
        return WarehouseResource::collection($warehouses);
    }

    public function store(Request $request)
    {
        try {
            $warehouse = new Warehouse();
            $warehouse->name = $request->get('name');
            $warehouse->unit = $request->get('unit');
            $warehouse->category_id = $request->get('category');
            $warehouse->save();
            return $warehouse;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function updateOrder(Request $request)
    {
      foreach ($request->all() as $order) {
        $warehouse = Warehouse::find($order['id']);
        $warehouse->update([
          'order' => $order['order']
        ]);
      }
      return $request;
    }

    public function reset($id)
    {
        $warehouse = Warehouse::find($id);
        if($warehouse) {
          $statuses = WarehouseStatus::where('warehouse_id', $id)->get();
          foreach ($statuses as $status) {
            $status->delete();
          }
        }
        return response()->json(['message' => 'Warehouse reset successfully']);
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::find($id);
        $warehouse->delete();
        return $warehouse;
    }
}
