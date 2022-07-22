<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidatePinRequest;

class ValidatePinController extends Controller
{
    public function validatePin(ValidatePinRequest $request)
    {
      $APP_PIN = (int) env('APP_PIN');
      return response()->json(['status' => $request->get('pin') === $APP_PIN]);
    }
}
