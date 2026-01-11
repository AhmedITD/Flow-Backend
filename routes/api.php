<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\UsageController;

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
    Route::post('/send-verification', [AuthController::class, 'sendPhoneVerification']);
    Route::post('/verify-code', [AuthController::class, 'verifyPhoneCode']);
    // Password reset endpoints
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Payment webhooks and callbacks (public routes)
// Route::group(['prefix' => 'payments'], function () {
//     Route::post('/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
//     Route::get('/callback', [PaymentController::class, 'callback'])->name('payment.callback');
//     Route::get('/return', [PaymentController::class, 'return'])->name('payment.return');
// });

// ============================================
// ADMIN ROUTES (JWT Authentication Only)
// ============================================
// These routes are for admin dashboard access
Route::middleware('auth:api')->group(function () {
    // Plans (public for authenticated users)
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{id}', [PlanController::class, 'show']);
    
    // Subscriptions
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);
    
    // Payment management
    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
    Route::get('/payments/{id}', [PaymentController::class, 'status']);
    Route::get('/payments/history', [PaymentController::class, 'history']);
    
    // Billing cycles
    Route::get('/subscriptions/{subscriptionId}/billing-cycles', [BillingController::class, 'cycles']);
    
    // Usage-based pricing (Pay-as-you-go)
    Route::get('/subscriptions/{subscriptionId}/usage-cost', [UsageController::class, 'calculateCost']);
    Route::post('/subscriptions/{subscriptionId}/usage-payment', [UsageController::class, 'createPayment']);
    
    // API Key management (admin generates keys for customers)
    Route::apiResource('api-keys', ApiKeyController::class);
    Route::get('/api-keys/{id}/embed', [ApiKeyController::class, 'embedCode'])->name('api-keys.embed');
    
});

// ============================================
// CUSTOMER SERVICE ROUTES (API Key Only)
// ============================================
// These routes are for customers using API keys (no login required)
// No conversation or message history is saved
Route::group(['prefix' => 'chat'], function () {
    Route::middleware([\App\Http\Middleware\AuthenticateApiKey::class])->group(function () {
        Route::post('/message', [\App\Http\Controllers\ChatController::class, 'chat']);
    });
});
