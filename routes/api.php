<?php

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RefundReasonController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ValidatePinController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use Services\WorkingDay;

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
Route::get('connection', function() {
  return true;
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Tables
Route::get('tables', [TableController::class, 'all']);
Route::get('tables/available', [TableController::class, 'available']);
Route::get('table/{id}', [TableController::class, 'index']);
// Route::get('tables/all', [TableController::class, 'indexForArea']);
Route::post('tables', [TableController::class, 'store']);

//Inventory
Route::get('inventory', [InventoryController::class, 'index']);
Route::get('inventory/all', [InventoryController::class, 'all']);
Route::post('inventory', [InventoryController::class, 'store']);

//Categories
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/printing', [CategoryController::class, 'indexPrinting']);
Route::post('categories', [CategoryController::class, 'store']);

// Orders
Route::get('orders', [OrderController::class, 'index']);
Route::get('orders/table/{id}', [OrderController::class, 'indexForTable']);
Route::get('orders/{id}', [OrderController::class, 'index']);
Route::post('orders', [OrderController::class, 'store']);
Route::post('orders/{id}/refund-item', [OrderController::class, 'update']);
Route::post('orders/move/{fromId}/{toId}', [OrderController::class, 'move']);

// Invoices
Route::get('invoices', [InvoiceController::class, 'all']);
Route::get('invoices/today', [InvoiceController::class, 'allForToday']);
Route::get('invoices/today-transactions', [InvoiceController::class, 'todayTransactions']);
Route::post('invoices', [InvoiceController::class, 'store']);
Route::post('invoices/one/{orderId}', [InvoiceController::class, 'store']);
Route::post('invoices/{id}/refund', [InvoiceController::class, 'refund']);

// Waiters
Route::get('waiters', [UsersController::class, 'waiters']);

// Tasks
Route::get('tasks', [TaskController::class, 'index']);
Route::get('tasks/today', [TaskController::class, 'indexForToday']);
Route::post('tasks', [TaskController::class, 'store']);
Route::post('tasks/finish/{id}', [TaskController::class, 'finish']);
Route::delete('tasks/{id}', [TaskController::class, 'destroy']);

// Refund Reasons
Route::get('refund-reasons', [RefundReasonController::class, 'index']);

// Pin
Route::post('validate-pin', [ValidatePinController::class, 'validatePin']);

//Dashboard
Route::get('stats', [DashboardController::class, 'stats']);
Route::get('active-orders', [DashboardController::class, 'activeOrders']);
Route::get('users', [UsersController::class, 'index']);

// Working day
Route::get('working-day', function() {
  return WorkingDay::getWorkingDay();
});

//Backoffice
Route::prefix('/backoffice')->group(function() {
  Route::post('/inventory', [InventoryController::class, 'store']);
  Route::put('/inventory/{id}', [InventoryController::class, 'update']);
  Route::delete('/inventory/{id}', [InventoryController::class, 'destroy']);

  Route::post('/categories', [CategoryController::class, 'store']);
  Route::put('/categories/{id}', [CategoryController::class, 'update']);
  Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);


  Route::put('/tables/{id}', [TableController::class, 'update']);

  Route::get('users/{id}', [UsersController::class, 'show']);
  Route::post('users/{id}', [UsersController::class, 'update']);
  Route::delete('users/{id}', [UsersController::class, 'destroy']);

  Route::get('reports/{type}', [ReportsController::class, 'index']);
});
