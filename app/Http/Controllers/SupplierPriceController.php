<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\SupplierPriceService;

class SupplierPriceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'client_account' => 'required|uuid|exists:client_bank_accounts,id',
            'search' => 'nullable|string',
            'changed' => 'nullable|boolean',
        ]);

        return [
            'articles' => SupplierPriceService::history(
                $request->input('client_account'),
                $request->input('search'),
                $request->boolean('changed')
            ),
        ];
    }
}
