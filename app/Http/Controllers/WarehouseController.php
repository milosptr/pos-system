<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseResource;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
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

    public function destroy($id)
    {
        $warehouse = Warehouse::find($id);
        $warehouse->delete();
        return $warehouse;
    }
}
