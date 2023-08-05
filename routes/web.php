<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;

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

Route::group(['prefix' => 'public', 'middleware' => ['cors']], function () {
    Route::post('sales/import', [SalesController::class, 'import']);
});


require __DIR__.'/auth.php';
