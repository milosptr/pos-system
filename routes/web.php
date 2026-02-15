<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
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


Route::get('/kitchen', function () {
    return view('kitchen');
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


require __DIR__.'/auth.php';
