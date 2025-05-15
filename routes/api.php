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

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/get',[UserController::class, 'getuserdata']);
        Route::put('/update', [UserController::class , 'update']);
        Route::post('/logout', [UserController::class , 'logout']);
    });
});

Route::prefix('book')->middleware('auth:sanctum')->group(function (){
    Route::get('/', [BookController::class , 'index']);//for the books page
    Route::get('/order', [BookController::class , 'order']);//the items in the order page

    Route::middleware('admin')->group(function () {
        Route::get('/class', [BookController::class , 'class']);
        Route::get('/stock', [BookController::class , 'stock']); //specifications of the book for stock page
        Route::get('/stock/{id}', [BookController::class , 'stocksearch']);
        Route::prefix('create')->group(function (){
            Route::post('/', [BookController::class , 'createBook']); 
            Route::post('/stock', [BookController::class , 'createClass']); 
        });
        Route::prefix('update')->group(function (){
            Route::put('/stock/{id}', [BookController::class , 'updateStock']); 
            Route::put('/price/{id}', [BookController::class , 'updatePrice']); 
        });
        Route::get('/daerah/{place}', [BookController::class , 'daerahsearch']);
    });
});

Route::prefix('order')->group(function (){

    Route::get('/all', [OrderController::class , 'index']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/add', [OrderController::class , 'store']);
        Route::get('/tagihan/{isPayed}', [OrderController::class , 'tagihan']);
        
        Route::middleware('admin')->group(function () {
            Route::put('/update/{id}', [OrderController::class , 'updateorder']);
            Route::put('/update/book/{id}', [OrderController::class , 'updatebook']);
            Route::delete('/delete/{id}', [OrderController::class , 'destroy']);
        });
        
        Route::get('/{status}', [OrderController::class , 'search']);
    });
});

Route::get('/laporan', [OrderController::class , 'laporan']);

Route::get('/notification', [NotificationController::class, 'index']);
Route::get('/notification/{id}', [NotificationController::class, 'show']);  
