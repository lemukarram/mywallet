<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // User routes
        Route::get('/user', [UserController::class, 'showCurrentUser']);
        Route::get('/users/{user}', [UserController::class, 'show'])->middleware('can:view,user');
        
        // Wallet routes
        Route::prefix('wallet')->group(function () {
            Route::post('/deposit', [WalletController::class, 'deposit']);
            Route::post('/withdraw', [WalletController::class, 'withdraw']);
        });
        
        // Transaction routes
        Route::prefix('transactions')->group(function () {
            Route::post('/transfer', [TransactionController::class, 'transfer']);
            Route::get('/', [TransactionController::class, 'index']);
        });
    });
