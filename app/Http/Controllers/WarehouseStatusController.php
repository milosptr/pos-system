<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseStatusResource;
use App\Models\WarehouseStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseStatusController extends Controller
{
    public function index(Request $request)
    {
      $warehouse = WarehouseStatus::query();
      $date = $request->get('date') ?? date('Y-m-d');

      $warehouse = $warehouse->whereDate('date', '<=', $date)
        ->selectRaw(
          'warehouse_id, ' .
          'SUM(case when warehouse_status.date = ? then(case when warehouse_status.type = 0 then quantity else 0 end) else 0 end) as import_quantity, ' .
          'SUM(case when warehouse_status.date = ? then(case when warehouse_status.type = 1 then quantity else 0 end) else 0 end) as sale_quantity, ' .
          'SUM(case when warehouse_status.date <= ? then (case when warehouse_status.type = 0 then quantity else -quantity end) else 0 end) as quantity,' .
//          'SUM(case when warehouse_status.type = 0 and warehouse_status.date = ? then quantity else 0 end) as date_import_quantity, ' .
//          'SUM(case when warehouse_status.type = 1 and warehouse_status.date = ? then quantity else 0 end) as date_sale_quantity, ' .
//          'SUM(case when warehouse_status.date = ? then (case when warehouse_status.type = 0 then quantity else -quantity end) else 0 end) as date_quantity, ' .
          'SUM(case when warehouse_status.date < ? then (case when warehouse_status.type = 0 then quantity else -quantity end) else 0 end) as previous_quantity',
          [$date, $date, $date, $date]
        )
        ->groupBy('warehouse_id');

      return WarehouseStatusResource::collection($warehouse->get());
    }

    public function imports(Request $request)
    {
      $warehouse = WarehouseStatus::where('type', WarehouseStatus::TYPE_IN)->orderBy('created_at', 'desc');
      if ($request->has('date')) {
        $warehouse = $warehouse->whereDate('created_at', $request->get('date'));
      }
      $warehouse = $warehouse->get();
      return WarehouseStatusResource::collection($warehouse);
    }


    public function indexSummarized(Request $request)
    {
        $warehouse = WarehouseStatus::select('warehouse_id', DB::raw('SUM(case when warehouse_status.type = 0 then quantity * 1 else quantity * -1 end) as quantity'))
          ->groupBy('warehouse_id');
        if ($request->has('date')) {
          $warehouse = $warehouse->whereBetween('created_at', ['2000-01-01 00:00:00', $request->get('date') . ' 23:59:59']);
        }
        return WarehouseStatusResource::collection($warehouse->get());
    }

    public function show($id)
    {
        return WarehouseStatus::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'warehouse_id',
            'inventory_id',
            'quantity',
            'type',
            'comment',
            'created_at',
        ]);

        try {
            $ws = new WarehouseStatus();
            $ws->warehouse_id = $data['warehouse_id'];
            $ws->inventory_id = $data['inventory_id'] ?? null;
            $ws->quantity = $data['quantity'] ?? 0;
            $ws->type = $data['type'] ?? 0;
            $ws->comment = $data['comment'] ?? null;
            $ws->date = $data['created_at'] ?? now();
            $ws->created_at = $data['created_at'] ?? now();
            $ws->save();
            return response()->json([
                'status' => 'success',
                'message' => 'WarehouseStatus created successfully',
                'data' => $ws
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'WarehouseStatus creation failed',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    public function importsUpdate($id, Request $request)
    {
      $import = WarehouseStatus::where('type', WarehouseStatus::TYPE_IN)->where('id', $id)->first();
      if (!$import) {
        return response()->json([
          'status' => 'error',
          'message' => 'WarehouseStatus imports not found',
          'data' => null
        ], 404);
      }
      $import->update($request->only('quantity'));
      return response()->json([
        'status' => 'success',
        'message' => 'WarehouseStatus imports updated successfully',
        'data' => $import
      ], 200);
    }

    public function importsDestroy($id)
    {
        $warehouse = WarehouseStatus::where('type', WarehouseStatus::TYPE_IN)->where('id', $id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'WarehouseStatus imports deleted successfully',
            'data' => $warehouse
        ], 200);
    }
}
