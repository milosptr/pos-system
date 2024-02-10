<?php

namespace App\Http\Controllers;

use App\Models\WarehouseCategory;
use Illuminate\Http\Request;

class WarehouseCategoryController extends Controller
{
    public function index()
    {
      return WarehouseCategory::where('is_deleted', 0)->get();
    }

    public function store(Request $request)
    {
      $category = new WarehouseCategory();
      $category->group_id = $request->get('parent_id');
      $category->name = $request->get('name');
      $category->order = $request->get('order');
      $category->save();
      return $category;
    }

    public function destroy($id)
    {
      $category = WarehouseCategory::findOrFail($id);
      $category->is_deleted = 1;
      $category->save();
      return $category;
    }
}
