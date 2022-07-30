<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Invoice;
use Services\WorkingDay;
use Illuminate\Http\Request;
use App\Http\Resources\TableResource;
use App\Models\Order;
use Carbon\Carbon;
use Services\ReportsService;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
    }

    public function revenue(Request $request)
    {
        return Invoice::filter($request)->first();
    }

    public function activeOrders(Request $request)
    {
        $tables = Table::whereHas('orders');
        $tables->with('orders');
        return TableResource::collection($tables->get());
    }

    public function stats()
    {
      $today = ReportsService::getRevenueForDate(WorkingDay::getWorkingDay());
      $yesterday = ReportsService::getRevenueForDate(WorkingDay::getWorkingDay(Carbon::now(), 'yesterday'));
      $activeTablesTotal = Order::selectRaw('sum(total) as total')->groupBy('table_id')->get()->sum('total');

      return [
        ReportsService::parseStats($today, $yesterday, 'Total', 'total'),
        [
          "name" => 'Active Orders',
          "stat" => (int) $activeTablesTotal,
          "previousStat" => 0,
          "change" => null,
          "changeType" => null,
          "primary" => false,
        ],
        ReportsService::parseStats($today, $yesterday, 'Refund', 'refund'),
        [
          "name" => 'Total + Active Orders',
          "stat" => (int) $activeTablesTotal + (int) $today['total'],
          "previousStat" => 0,
          "change" => null,
          "changeType" => null,
          "primary" => true,
        ],
      ];

    }
}
