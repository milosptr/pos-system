<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Invoice;
use Services\WorkingDay;
use Illuminate\Http\Request;
use App\Http\Resources\TableResource;
use App\Models\Order;
use App\Models\ThirdPartyOrderItem;
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
        $today = ReportsService::getCombinedRevenueForDate(WorkingDay::getWorkingDay());
        $activeTablesTotal = Order::selectRaw('sum(total) as total')->groupBy('table_id')->get()->sum('total');
        $thirdPartyActiveTotal = (int) ThirdPartyOrderItem::where('active', 1)
            ->selectRaw('sum(qty * price) as total')
            ->value('total');

        return [
          ['name' => 'Komp', 'stat' => $today->komp, 'primary' => false],
          ['name' => 'Kasa I', 'stat' => $today->kasa_i, 'primary' => false],
          ['name' => 'Aktivni stolovi', 'stat' => (int) $activeTablesTotal + $thirdPartyActiveTotal, 'primary' => false],
          ['name' => 'Stornirano', 'stat' => $today->refund, 'primary' => false],
          ['name' => 'Na račun kuće', 'stat' => $today->onthehouse, 'primary' => false],
          ['name' => 'Ukupno', 'stat' => $today->komp + $today->kasa_i + (int) $activeTablesTotal + $thirdPartyActiveTotal, 'primary' => true],
        ];
    }
}
