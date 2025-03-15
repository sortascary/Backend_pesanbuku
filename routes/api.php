<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\BookController;
use App\Http\Controllers\V1\OrderController;
use App\Http\Controllers\V1\NotificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('user')->group(function (){
    Route::post('/register', [UserController::class , 'register']);
    Route::post('/login', [UserController::class , 'login']);

    Route::get('/AllUsers', [UserController ::class, 'index']);
    Route::get('/AllUsers/{id}', [UserController::class, 'show']);
});

Route::prefix('book')->group(function (){
    Route::get('/', [BookController::class , 'index']);
    Route::get('/class', [BookController::class , 'class']);
    Route::get('/daerah', [BookController::class , 'daerah']);
    Route::get('/daerah/{id}', [BookController::class , 'daerahsearch']);
});

Route::prefix('order')->group(function (){
    Route::get('/', [OrderController::class , 'index']);
    Route::get('/all', [OrderController::class , 'index']);
});

Route::get('/notification', [NotificationController::class, 'index']);
Route::get('/notification/{id}', [NotificationController::class, 'show']);

Route::get('/profile', function () {
    return response()->json([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
});
