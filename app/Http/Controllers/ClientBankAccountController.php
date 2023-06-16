<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientBankAccount;

class ClientBankAccountController extends Controller
{
    public function index()
    {
        return ClientBankAccount::all();
    }

    public function store(Request $request)
    {
        $clientBankAccount = ClientBankAccount::create($request->all());
        return $clientBankAccount;
    }
}
