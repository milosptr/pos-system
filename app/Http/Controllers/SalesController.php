<?php

namespace App\Http\Controllers;

use App\Imports\SalesImport;
use App\Models\Sales;
use App\Models\SalesImportDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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
            $fileName = $file->getClientOriginalName();
            $saleDetails = SalesImportDetail::create([
                'filename' => $fileName,
            ]);
            Excel::import(new SalesImport($date, $saleDetails), $file);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response($th->getMessage(), 422);
        }
        return response('Success', 200);
    }

    public function clear()
    {
        Artisan::call('sales:clear');
        return response('Success', 200);
    }
}
