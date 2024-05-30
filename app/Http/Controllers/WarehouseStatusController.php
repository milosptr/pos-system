<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseStatusResource;
use App\Models\WarehouseStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Services\WorkingDay;

class WarehouseStatusController extends Controller
{
    public function index(Request $request)
    {
      $warehouse = WarehouseStatus::query();

      // If the warehouse has been deleted, remove the warehouse status from query
      $warehouse = $warehouse->whereHas('warehouse');

      $warehouse = $warehouse->leftJoin('warehouses', 'warehouses.id', '=', 'warehouse_status.warehouse_id');
      $date = $request->get('date') ?? date('Y-m-d');
      $category_id = $request->get('category_id');
      $group_id = $request->get('group_id');
      if ($category_id) {
        $warehouse = $warehouse->whereHas('warehouse', function ($query) use ($category_id) {
          $query->where('category_id', $category_id);
        });
      }
      if ($request->has('group_id')) {
        $warehouse = $warehouse->leftJoin('warehouse_categories', 'warehouse_categories.id', '=', 'warehouses.category_id')
          ->where('warehouse_categories.group_id', $group_id);
      }

      $warehouse = $warehouse->whereDate('date', '<=', $date)
        ->selectRaw(
          'warehouse_id, ' .
          'SUM(case when warehouse_status.date = ? then(case when warehouse_status.type = 0 then quantity else 0 end) else 0 end) as import_quantity, ' .
          'SUM(case when warehouse_status.date = ? then(case when warehouse_status.type = 1 then quantity else 0 end) else 0 end) as sale_quantity, ' .
          'SUM(case when warehouse_status.date <= ? then (case when warehouse_status.type = 1 then -quantity else quantity end) else 0 end) as quantity,' .
          'SUM(case when warehouse_status.date < ? then (case when warehouse_status.type = 1 then -quantity else quantity end) else 0 end) as previous_quantity,' .
          'SUM(case when warehouse_status.date = ? then(case when warehouse_status.type = 2 then quantity else 0 end) else 0 end) as recalculated_quantity',
          [$date, $date, $date, $date, $date]
        )
        ->groupBy('warehouse_id')
        ->orderBy('warehouses.order');

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

    public function recalculate($id, Request $request)
    {
      $quantity = (float) $request->get('quantity');
      $today = date('Y-m-d');
      $date = $request->get('date') ?? $today;
      if($date === $today) {
        $date = WorkingDay::setCorrectDateForWorkingDay();
      }

      $existingRecalculation = WarehouseStatus::where('type', WarehouseStatus::TYPE_RESET)->where('warehouse_id', $id)->whereDate('date', $date)->first();
      if ($existingRecalculation) {
        $existingRecalculation->update([
          'quantity' => round($quantity, 2),
          'comment' => 'Recalculated from ' . $request->get('previous_quantity') . ' to ' . $quantity
        ]);
        return $existingRecalculation;
      }

      return WarehouseStatus::create([
        'warehouse_id' => $id,
        'type' => WarehouseStatus::TYPE_RESET,
        'quantity' => round($quantity, 2),
        'date' => $date,
        'comment' => 'Recalculated from ' . $request->get('previous_quantity') . ' to ' . $quantity
      ]);
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
