<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\NotificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [UserController::class , 'register']);
Route::post('/login', [UserController::class , 'login']);

Route::get('/AllUsers', [UserController ::class, 'index']);
Route::get('/AllUsers/{id}', [UserController::class, 'show']);

Route::get('/notification/{id}', [NotificationController::class, 'index']);

Route::get('/profile', function () {
    return response()->json([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
});
