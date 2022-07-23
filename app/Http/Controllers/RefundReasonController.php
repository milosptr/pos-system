<?php

namespace App\Http\Controllers;

use App\Models\RefundReason;
use Illuminate\Http\Request;

class RefundReasonController extends Controller
{
    public function index()
    {
      return RefundReason::all();
    }
}
