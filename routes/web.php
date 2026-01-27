<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ThirdPartyInvoiceController;
use App\Http\Controllers\ThirdPartyOrderController;
use App\Http\Controllers\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('app');
});


Route::group(['middleware' => ['administrator', 'auth']], function () {
    Route::get('/backoffice/', function () {
        return view('backoffice');
    })->name('backoffice');
});

// Log viewer - protected by auth only (no admin required for debugging)
Route::group(['middleware' => ['auth']], function () {
    Route::get('/logs', [LogViewerController::class, 'index']);
    Route::get('/logs/clear', [LogViewerController::class, 'clear']);
});

Route::group(['prefix' => 'public', 'middleware' => ['cors']], function () {
    Route::post('sales/import', [SalesController::class, 'import']);
});

Route::group(['prefix' => 'public', 'middleware' => ['cors', 'external.api']], function () {
    Route::post('third-party-invoice', [ThirdPartyInvoiceController::class, 'store']);
    Route::post('third-party-order', [ThirdPartyOrderController::class, 'store']);
});

// Debug route - no middleware - remove after testing
Route::any('public/test-ping', function () {
    return response()->json(['pong' => true, 'ip' => request()->ip()]);
});


require __DIR__.'/auth.php';
