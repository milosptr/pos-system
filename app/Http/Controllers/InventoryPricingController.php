<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventoryBackofficeResource;
use App\Models\InventoryPricing;
use Illuminate\Http\Request;

class InventoryPricingController extends Controller
{
   public function store(Request $request)
    {
      $pricing = InventoryPricing::create($request->all());
      return new InventoryBackofficeResource($pricing->inventory);
    }

    public function destroy($id)
    {
      $inventoryPricing = InventoryPricing::find($id);
      return $inventoryPricing->delete();
    }
}
