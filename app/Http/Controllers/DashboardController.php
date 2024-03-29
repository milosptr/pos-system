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
        $activeTablesTotal = Order::selectRaw('sum(total) as total')->groupBy('table_id')->get()->sum('total');

        return [
          ReportsService::parseStats($today, 'Ukupno', 'total'),
          [
            "name" => 'Aktivni stolovi',
            "stat" => (int) $activeTablesTotal,
            "primary" => false,
          ],
          ReportsService::parseStats($today, 'Stornirano', 'refund'),
          ReportsService::parseStats($today, 'Na račun kuće', 'onthehouse'),
          ReportsService::parseStats($today, 'Prihod', 'income', true, $activeTablesTotal),

        ];

    }
}
