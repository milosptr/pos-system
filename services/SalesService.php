<?php

namespace Services;

use App\Models\Invoice;
use App\Models\Sales;
use App\Models\WarehouseInventory;
use App\Models\WarehouseStatus;
use Carbon\Carbon;

class SalesService {
    /**
    * Calculates in percent, the change between 2 numbers.
    * e.g from 1000 to 500 = 50%
    *
    * @param $orders - The orders to be saved
    * @param $invoice - The invoice to be saved
    */
    public static function parseAndSaveOrder($orders, $invoice)
    {
      $data = [];
      foreach ($orders as $order) {
        if(isset($order['refund']) && $order['refund'])
          continue;
          try {
            self::populateWarehouse($order);
          } catch (\Exception $e) {
            // Log the error
          }
          array_push($data, [
            'invoice_id' => $invoice->id,
            'inventory_id' => $order['id'],
            'category_id' => $order['category_id'],
            'table_id' => $order['table_id'],
            'name' => $order['name'],
            'category_name' => $order['category_name'],
            'price' => $order['price'],
            'total' => $order['price'] * $order['qty'],
            'sku' => $order['sku'],
            'qty' => $order['qty'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);
      }
      return Sales::insert($data);
    }

    public static function populateWarehouse($order)
    {
      $warehouse = WarehouseInventory::where('inventory_id', $order['id']);
      if($warehouse && $warehouse->first()) {
        $warehouse = $warehouse->first();
        WarehouseStatus::create([
          'warehouse_id' => $warehouse['warehouse_id'],
          'inventory_id' => $order['id'],
          'date' => Carbon::now()->format('Y-m-d'),
          'quantity' => (float) $order['qty'] * (float) $warehouse['norm'],
          'type' => WarehouseStatus::TYPE_OUT,
          'comment' => 'Sale from ePOS',
        ]);
      }
    }

    public static function populateWarehouseFromSaleImport(Sales $sale, $date)
    {
      $warehouse = WarehouseInventory::where('inventory_id', $sale->inventory_id);
      if($warehouse && $warehouse->first()) {
        $warehouse = $warehouse->first();
        WarehouseStatus::create([
          'warehouse_id' => $warehouse['warehouse_id'],
          'inventory_id' => $sale->inventory_id,
          'quantity' => (float) $sale->qty * (float) $warehouse['norm'],
          'type' => WarehouseStatus::TYPE_OUT,
          'date' => Carbon::now($date)->format('Y-m-d'),
          'batch_id' => $sale->batch_id,
          'comment' => 'Sale from other system',
          'created_at' => $date,
        ]);
      }
    }
}
