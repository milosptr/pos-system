<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Tables
Route::get('tables', [TableController::class, 'index']);
Route::get('tables/{id}', [TableController::class, 'indexForArea']);
Route::post('tables', [TableController::class, 'store']);

//Inventory
Route::get('inventory', [InventoryController::class, 'index']);
Route::post('inventory', [InventoryController::class, 'store']);

//Categories
Route::get('categories', [CategoryController::class, 'index']);
Route::post('categories', [CategoryController::class, 'store']);

// Orders
Route::get('orders', [OrderController::class, 'all']);
Route::get('orders/table/{id}', [OrderController::class, 'indexForTable']);
Route::get('orders/{id}', [OrderController::class, 'index']);
Route::post('orders', [OrderController::class, 'store']);
