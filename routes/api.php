<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\BookController;
use App\Http\Controllers\V1\OrderController;
use App\Http\Controllers\V1\NotificationController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('user')->group(function (){
    Route::post('/register', [UserController::class , 'register']);
    Route::post('/login', [UserController::class , 'login']);
    Route::get('/AllUsers', [UserController ::class, 'index']);
    Route::get('/AllUsers/{id}', [UserController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/update', [UserController::class , 'update']);
        Route::post('/logout', [UserController::class , 'logout']);
    });
});

Route::prefix('book')->middleware('auth:sanctum')->group(function (){
    Route::get('/', [BookController::class , 'index']);//for the books page
    Route::get('/order', [BookController::class , 'order']);//the items in the order page

    Route::middleware('admin')->group(function () {
        Route::get('/class', [BookController::class , 'class']);
        Route::get('/stock', [BookController::class , 'stock']);
        Route::get('/stock/{id}', [BookController::class , 'stocksearch']);
        Route::put('/update/{id}', [BookController::class , 'update']); 
        Route::get('/daerah/{place}', [BookController::class , 'daerahsearch']);
    });
});

Route::prefix('order')->group(function (){
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/{status}', [OrderController::class , 'search']);
        Route::post('/add', [OrderController::class , 'store']);
    
        Route::middleware('admin')->group(function () {
            Route::put('/update/{id}', [OrderController::class , 'updateorder']);
            Route::put('/update/book/{id}', [OrderController::class , 'updatebook']);
            Route::delete('/delete/{id}', [OrderController::class , 'destroy']);
        });
    });
    Route::get('/all', [OrderController::class , 'index']);
});

Route::get('/notification', [NotificationController::class, 'index']);
Route::get('/notification/{id}', [NotificationController::class, 'show']);  
