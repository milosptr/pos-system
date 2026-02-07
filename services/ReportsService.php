<?php

namespace Services;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\ThirdPartyInvoice;

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
      return Invoice::selectRaw('sum(total) AS total')
        ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
        ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
        ->selectRaw('(sum(total) - sum(case when status = 0 then total else 0 end) - sum(case when status = 2 then total else 0 end)) as income')
        ->whereBetween('created_at', $date)
        ->get()
        ->first();
    }

    public static function parseStats($today, $name, $field, $primary = false, $addition = 0)
    {
      return [
        "name" => $name,
        "stat" => (int) $today[$field] + (int) $addition,
        "primary" => $primary,
      ];
    }

    public static function getCombinedRevenueForDate($date)
    {
        $tp = ThirdPartyInvoice::selectRaw('
                sum(case when payment_type in (1,2,3) then total else 0 end)
                - sum(case when payment_type in (1,2,3) and status = 0 then total else 0 end)
                - sum(case when payment_type in (1,2,3) and status = 2 then total else 0 end) as komp')
            ->selectRaw('
                sum(case when payment_type = 4 then total else 0 end)
                - sum(case when payment_type = 4 and status = 0 then total else 0 end)
                - sum(case when payment_type = 4 and status = 2 then total else 0 end) as kasa_i')
            ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
            ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
            ->whereRaw('COALESCE(invoiced_at, created_at) BETWEEN ? AND ?', $date)
            ->first();

        $inv = Invoice::selectRaw('
                sum(total) - sum(case when status = 0 then total else 0 end) - sum(case when status = 2 then total else 0 end) as kasa_i')
            ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
            ->selectRaw('sum(case when status = 2 then total else 0 end) as onthehouse')
            ->whereBetween('created_at', $date)
            ->first();

        return (object) [
            'komp' => (int) ($tp->komp ?? 0),
            'kasa_i' => (int) ($tp->kasa_i ?? 0) + (int) ($inv->kasa_i ?? 0),
            'refund' => (int) ($tp->refund ?? 0) + (int) ($inv->refund ?? 0),
            'onthehouse' => (int) ($tp->onthehouse ?? 0) + (int) ($inv->onthehouse ?? 0),
        ];
    }
}
