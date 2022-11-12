<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
  public function show()
  {
    return Client::all();
  }

  public function create(Request $request)
  {
    return Client::create($request->all());
  }

  public function update(Request $request, $id)
  {
    $client = Client::find($id);
    $client->update($request->all());
    return $client;
  }

  public function destroy($id)
  {
    $client = Client::find($id);
    return $client->delete();
  }
}
