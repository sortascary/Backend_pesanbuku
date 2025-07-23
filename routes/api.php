<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\BookController;
use App\Http\Controllers\V1\OrderController;
use App\Http\Controllers\V1\NotificationController;
use Illuminate\Support\Facades\URL;

//for the admin dashboard
Route::get('/init', [OrderController::class , 'init'])->middleware(['auth:sanctum']);

Route::prefix('user')->group(function (){
    Route::post('/register', [UserController::class , 'register']);
    Route::post('/login', [UserController::class , 'login']);
    Route::get('/AllUsers', [UserController::class, 'index']);
    Route::get('/verify/{id}/{hash}', [UserController::class, 'verify'])->name('verification.verify');
    Route::post('/forgot-password', [UserController::class, 'sendResetToken'])->middleware(['throttle:6,1']);
    Route::post('/reset-password', [UserController::class, 'reset']);
    Route::get('/reset-verify/{email}/{token}', [UserController::class, 'resetRedirect']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/get',[UserController::class, 'getuserdata']);
        Route::get('/verification-notification', [UserController::class, 'sendVerification'])->middleware(['throttle:6,1'])->name('verification.send');
        Route::put('/update', [UserController::class , 'update']);
        Route::post('/logout', [UserController::class , 'logout']);
    });
});

Route::prefix('book')->middleware('auth:sanctum')->group(function (){
    Route::get('/', [BookController::class , 'index']);//for the books page
    Route::prefix('order')->group(function () {
        Route::get('/', [BookController::class, 'order']); //the items in the order page (sekolah)
        Route::get('/{daerah}', [BookController::class, 'orderSearch']); //the items in the order page (distributor)
    });

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
        Route::prefix('delete')->group(function (){
            Route::delete('/stock/{id}', [BookController::class , 'deleteClass']); 
            Route::delete('/book/{id}', [BookController::class , 'deleteBook']); 
        });
        Route::get('/daerah/{place}', [BookController::class , 'daerahsearch']);
    });
});

Route::prefix('order')->group(function (){
    
    Route::get('/createpdf', [OrderController::class , 'generatePDF']);
    Route::get('/test', [OrderController::class , 'test']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [OrderController::class , 'index']);
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

Route::get('/laporan/{startDate}/{endDate}', [OrderController::class , 'laporan']);

Route::prefix('notification')->group(function (){
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [NotificationController::class, 'index']);   
        Route::put('/read/{id}', [NotificationController::class, 'Read']);  
        Route::put('/readAll', [NotificationController::class, 'ReadAll']); 
    });
});

