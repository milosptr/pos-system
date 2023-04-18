<?php

namespace App\Http\Controllers;

use App\Imports\SalesImport;
use App\Models\Sales;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;

class SalesController extends Controller
{
    public function index()
    {
        return Sales::all();
    }

    public function import(Request $request)
    {
        try {
            $date = $request->has('date') ? $request->get('date') : date('Y-m-d');
            $file = $request->file('file');
            Excel::import(new SalesImport($date), $file);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect()->back()->with('error', 'Something went wrong!');
        }
        return response('Success', 200);
    }

    public function clear()
    {
        Artisan::call('sales:clear');
        return response('Success', 200);
    }
}
