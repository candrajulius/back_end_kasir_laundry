<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function(){
    Route::post('/login', 'login_customer');
    Route::post('/login_user', 'login_user');
    Route::post('/logout', 'logout_customer');
    Route::post('/logout_user', 'logout_user');
});

Route::prefix('customer')->controller(CustomerController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::get('/', 'index');
        Route::post('/register', 'register');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
});