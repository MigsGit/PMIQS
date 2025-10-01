<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommonController;

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


Route::middleware('auth')->group(function(){
    //NOTE This Query should place under the Route Get to avoid the Route Not Found !
    Route::get('/{any}', function(){
        return view('welcome');
    })->where('any','.*')->name('login');
});



