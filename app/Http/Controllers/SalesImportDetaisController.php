<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesImportDetail;

class SalesImportDetaisController extends Controller
{
    public function index()
    {
        return SalesImportDetail::orderBy('created_at', 'desc')->paginate(50);
    }

    public function destroy($id)
    {
        $salesImportDetail = SalesImportDetail::find($id);
        $salesImportDetail->sales()->delete();
        $salesImportDetail->delete();
        return response('Success', 200);
    }
}
