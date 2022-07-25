<?php

use Illuminate\Support\Facades\Route;

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


require __DIR__.'/auth.php';
