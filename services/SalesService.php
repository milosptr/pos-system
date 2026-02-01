<?php

namespace Services;

use App\Models\ExceptionLog;
use App\Models\Inventory;
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
            self::populateWarehouse($order, $invoice->id);
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

    public static function populateWarehouse($order, $invoiceId)
    {
      $warehouses = WarehouseInventory::where('inventory_id', $order['id'])->get();
      foreach($warehouses as $warehouse) {
        try {
          WarehouseStatus::create([
            'warehouse_id' => $warehouse['warehouse_id'],
            'inventory_id' => $order['id'],
            'batch_id' => $invoiceId,
            'date' => WorkingDay::setCorrectDateForWorkingDay(),
            'quantity' => (float)$order['qty'] * (float)$warehouse['norm'],
            'type' => WarehouseStatus::TYPE_OUT,
            'comment' => 'Sale from ePOS',
          ]);
        } catch (\Exception $e) {
          ExceptionLog::create([
            'type' => 'WarehouseStatus::populateWarehouse',
            'message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'method' => $e->getLine(),
            'payload' => json_encode($order),
            'status_code' => 500
          ]);
        }
      }
    }

    public static function populateWarehouseFromSaleImport(Sales $sale, $date)
    {
      $warehouses = WarehouseInventory::where('inventory_id', $sale->inventory_id)->get();
      try {
        $dateValue = $date ? $date : $sale->created_at;
        $dateInstance = Carbon::parse($dateValue)->timezone('Europe/Belgrade');
        $dateInstance = WorkingDay::setCorrectDateForWorkingDay($dateInstance);
      } catch (\Exception $e) {
        $dateInstance = WorkingDay::setCorrectDateForWorkingDay();
      }
      foreach($warehouses as $warehouse) {
        try {
          WarehouseStatus::create([
            'warehouse_id' => $warehouse['warehouse_id'],
            'inventory_id' => $sale->inventory_id,
            'quantity' => (float)$sale->qty * (float)$warehouse['norm'],
            'type' => WarehouseStatus::TYPE_OUT,
            'date' => $dateInstance,
            'batch_id' => $sale->batch_id,
            'comment' => 'Sale from other system',
          ]);
        } catch (\Exception $e) {
          $combinedPayload = array_merge($sale->toArray(), ['date' => $date]);
          ExceptionLog::create([
            'type' => 'WarehouseStatus::populateWarehouseFromSaleImport',
            'message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'method' => $e->getLine(),
            'payload' => json_encode($combinedPayload),
            'status_code' => 500
          ]);
        }
      }
    }

    public static function populateWarehouseForThirdParty($inventoryId, $qty, $invoiceId, $date = null)
    {
        $warehouses = WarehouseInventory::where('inventory_id', $inventoryId)->get();

        foreach ($warehouses as $warehouse) {
            try {
                WarehouseStatus::create([
                    'warehouse_id' => $warehouse->warehouse_id,
                    'inventory_id' => $inventoryId,
                    'batch_id' => $invoiceId,
                    'date' => $date ?? WorkingDay::setCorrectDateForWorkingDay(),
                    'quantity' => (float)$qty * (float)$warehouse->norm,
                    'type' => WarehouseStatus::TYPE_OUT,
                    'comment' => 'Sale from Third Party System',
                ]);
            } catch (\Exception $e) {
                ExceptionLog::create([
                    'type' => 'WarehouseStatus::populateWarehouseForThirdParty',
                    'message' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'method' => $e->getLine(),
                    'payload' => json_encode([
                        'inventory_id' => $inventoryId,
                        'qty' => $qty,
                        'invoice_id' => $invoiceId,
                    ]),
                    'status_code' => 500,
                ]);
            }
        }
    }

    public static function createSalesForThirdParty($matchedItems, $invoiceId, $date = null)
    {
        $date = $date ?? WorkingDay::setCorrectDateForWorkingDay();

        foreach ($matchedItems as $item) {
            $inventory = Inventory::with('category')->find($item['inventory_id']);
            if (!$inventory || !$inventory->category) {
                continue;
            }

            Sales::create([
                'inventory_id' => $inventory->id,
                'category_id' => $inventory->category_id,
                'category_name' => $inventory->category->name,
                'name' => $item['name'],
                'sku' => $inventory->sku,
                'qty' => $item['qty'],
                'price' => $item['price'],
                'total' => (int) round($item['qty'] * $item['price']),
                'type' => Sales::TYPE_EBAR,
                'status' => Sales::STATUS_ACTIVE,
                'batch_id' => $invoiceId,
                'created_at' => $date . ' 12:00:00',
            ]);
        }
    }
}
