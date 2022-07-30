<?php

namespace Services;

use Carbon\Carbon;
use App\Models\Invoice;

class ReportsService {
    /**
    * Calculates in percent, the change between 2 numbers.
    * e.g from 1000 to 500 = 50%
    *
    * @param oldNumber The initial value
    * @param newNumber The value that changed
    */
    public static function getPercentageChange($oldNumber, $newNumber)
    {
      if(!$oldNumber) $oldNumber = 1;
      $decreaseValue = $oldNumber - $newNumber;
      $result = ($decreaseValue / $oldNumber) * 100;
      return number_format((float) $result, 1, '.', '');
    }

    public static function getRevenueForDate($date)
    {
      return Invoice::selectRaw('sum(total) AS total_with_refund')
        ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
        ->selectRaw('(sum(total) - sum(case when status = 0 then total else 0 end)) as total')
        ->whereBetween('created_at', $date)
        ->get()
        ->first();
    }

    public static function parseStats($today, $yesterday, $name, $field)
    {
      return [
        "name" => $name,
        "stat" => $today[$field],
        "primary" => false,
      ];
    }
}
