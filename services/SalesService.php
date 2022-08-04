<?php

namespace Services;

use App\Models\Invoice;
use App\Models\Sales;
use Carbon\Carbon;

class SalesService {
    /**
    * Calculates in percent, the change between 2 numbers.
    * e.g from 1000 to 500 = 50%
    *
    * @param oldNumber The initial value
    * @param newNumber The value that changed
    */
    public static function parseAndSaveOrder($orders, $invoice)
    {
      $data = [];
      foreach ($orders as $order) {
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
          'status' => isset($order['refund']) && $order['refund'] ? 0 : 1,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
      }
      return Sales::insert($data);
    }

}
