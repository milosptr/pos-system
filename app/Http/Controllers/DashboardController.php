<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Invoice;
use Services\WorkingDay;
use Illuminate\Http\Request;
use App\Http\Resources\TableResource;
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
      $yesterday = ReportsService::getRevenueForDate(WorkingDay::getWorkingDay(Carbon::yesterday()));

      return [
        ReportsService::parseStats($today, $yesterday, 'Total', 'total'),
        ReportsService::parseStats($today, $yesterday, 'Refund', 'refund'),
        ReportsService::parseStats($today, $yesterday, 'Income', 'income'),
      ];

    }
}
