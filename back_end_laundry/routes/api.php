<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function(){
    Route::post('/login', 'login_customer');
    Route::post('/login_user', 'login_user');

    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout_user', 'logout_user');
    });
    Route::middleware('auth:customers')->group(function(){
        Route::post('/logout', 'logout_customer');
    });
});

Route::prefix('role')->controller(RoleController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
});


Route::prefix('transaction')->controller(TransactionController::class)->group(function(){

    Route::middleware('auth:customers')->group(function(){
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
    });

    Route::middleware('auth:sanctum')->group(function(){
        Route::put('/{id}', 'update');
        Route::get('/queue', 'queue');
        Route::get('/processing', 'processing');
        Route::get('/completed', 'completed');
        Route::put('/take/{id}', 'take');
        Route::put('/payed/{id}', 'pay');
        // Route::delete('/{id}', 'destroy');
    });
});

Route::prefix('service')->controller(ServiceController::class)->group(function(){

    Route::middleware('auth:customers')->group(function(){
        Route::get('/', 'index');
    });

    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
});

Route::prefix('promo')->controller(PromoController::class)->group(function(){

    Route::middleware('auth:customers')->group(function(){
        Route::post('/apply_promo', 'apply_promo');
        Route::get('/{id}', 'show');
        Route::get('/', 'index');
    });

    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
});

Route::prefix('customer')->controller(CustomerController::class)->group(function(){

    Route::post('/register', 'register');

    Route::middleware('auth:customers')->group(function(){
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
    });

    Route::middleware('auth:sanctum')->group(function(){
        Route::get('/', 'index');
        Route::delete('/{id}', 'destroy');
    });
    // Route::middleware('auth:sanctum')->group(function(){
    //     Route::get('/', 'index');
    //     Route::post('/register', 'register');
    //     Route::get('/{id}', 'show');
    //     Route::put('/{id}', 'update');
    //     Route::delete('/{id}', 'destroy');
    // });
});
