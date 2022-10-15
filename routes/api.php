<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\UsersController;
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

Route::post('/login', [UsersController::class, 'login']);
Route::post('/register', [UsersController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('threads', ThreadController::class);
    Route::get('/threads/from/{user}', [ThreadController::class, 'index']);

    Route::apiResource('threads.messages', MessageController::class)->except('show');
    Route::get('/user/{user}/messages', [MessageController::class, 'search']);
});
