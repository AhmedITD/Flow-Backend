<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Payment routes
Route::group(['prefix' => 'payments'], function () {
    // Public routes (webhooks and callbacks)
    Route::post('/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
    Route::get('/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/return', [PaymentController::class, 'return'])->name('payment.return');
    
    // Protected routes
    Route::middleware('auth:api')->group(function () {
        Route::post('/initiate', [PaymentController::class, 'initiate']);
        Route::get('/status/{transactionId}', [PaymentController::class, 'status']);
        Route::get('/history', [PaymentController::class, 'history']);
    });
});

