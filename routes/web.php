<?php

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use Laravie\SerializesQuery\Eloquent;
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


Route::get('/', [ChatController::class, 'index']);
Route::get('/api/v1/messages/{user}', [ChatController::class, 'fetchAllMessages']);
Route::post('/api/v1/messages/{user}', [ChatController::class, 'sendMessage']);
Route::get('/users', [ChatController::class, 'users']);

//Route::get('/', function () {
//    return view('welcome');
//});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
