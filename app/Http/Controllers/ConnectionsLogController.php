<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ConnectionLog;

class ConnectionsLogController extends Controller
{
    public function index()
    {
      return ConnectionLog::whereNotNull('end_at')->orderBy('id', 'DESC')->get();
    }

    public function check(Request $request)
    {
      $type = $request->get('error');

      try {
        $connection = ConnectionLog::whereNull('end_at')->first();
        if(!isset($connection)) {
          ConnectionLog::create(['start_at' => Carbon::now()]);
        } else if($type === 'false'){
          $connection->update(['start_at' => Carbon::now()]);
        }
        if($type === 'true') {
          $connection->update(['end_at' => Carbon::now()]);
        }
      } catch (Exception $e) {
        return $e->getMessage();
      }

      return true;
    }
}
