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
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['prefix' => 'auth/facebook'], function () {
    Route::get('/', [\App\Http\Controllers\FacebookController::class, 'facebook_provider']);
    Route::get('/callback', [\App\Http\Controllers\FacebookController::class, 'handle_callback']);
});

#Post Related
Route::get('/get_post/{id}-{token}', [\App\Http\Controllers\FacebookController::class, 'get_post']);
Route::post('/post', [\App\Http\Controllers\FacebookController::class, 'create_post']);
Route::post('/delete_post', [\App\Http\Controllers\FacebookController::class, 'delete_post']);
Route::post('/update_post', [\App\Http\Controllers\FacebookController::class, 'update_post']);

#Mail Related
Route::get('/mail/{id}-{token}', [\App\Http\Controllers\MailController::class, 'send']);
