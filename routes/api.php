<?php

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ValidatePinController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

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
Route::get('tables', [TableController::class, 'all']);
Route::get('table/{id}', [TableController::class, 'index']);
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

// Invoices
Route::get('invoices', [InvoiceController::class, 'all']);
Route::get('invoices/today', [InvoiceController::class, 'allForToday']);
Route::get('invoices/today-transactions', [InvoiceController::class, 'todayTransactions']);
Route::post('invoices', [InvoiceController::class, 'store']);
Route::post('invoices/{id}/refund', [InvoiceController::class, 'refund']);

// Waiters
Route::get('waiters', [UsersController::class, 'waiters']);

// Pin
Route::post('validate-pin', [ValidatePinController::class, 'validatePin']);

//Dashboard
Route::get('revenue', [DashboardController::class, 'revenue']);
Route::get('active-orders', [DashboardController::class, 'activeOrders']);
