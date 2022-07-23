<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Resources\TableResource;

class DashboardController extends Controller
{
    public function revenue(Request $request)
    {
        return Invoice::filter($request)->get();
    }

    public function activeOrders(Request $request)
    {
        $tables = Table::whereHas('orders');
        $tables->with('orders');
        return TableResource::collection($tables->get());
    }
}
