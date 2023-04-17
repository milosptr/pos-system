<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\InventoryImport;
use Maatwebsite\Excel\Facades\Excel;

class StockroomController extends Controller
{
    public function import(Request $request)
    {
        try {
            $date = $request->has('date') ? $request->get('date') : date('Y-m-d');
            $file = $request->file('file');
            Excel::import(new InventoryImport($date), $file);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect()->back()->with('error', 'Something went wrong!');
        }
        return response('Success', 200);
    }
}
