<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ServiceAccountController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\PricingController;
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

// ============================================
// AUTHENTICATION ROUTES
// ============================================
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

// ============================================
// PUBLIC ROUTES
// ============================================
// Pricing (public - no auth required)
Route::get('/pricing', [PricingController::class, 'index']);
Route::get('/pricing/{serviceType}', [PricingController::class, 'show']);

// Payment webhooks (public route for QiCard callbacks)
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// ============================================
// PROTECTED ROUTES (JWT Authentication)
// ============================================
Route::middleware('auth:api')->group(function () {
    
    // Service Account
    Route::get('/account', [ServiceAccountController::class, 'show']);
    Route::post('/account', [ServiceAccountController::class, 'store']);
    
    // Balance & Top-up
    Route::get('/balance', [BalanceController::class, 'show']);
    Route::post('/balance/topup', [BalanceController::class, 'topUp']);
    Route::get('/balance/transactions', [BalanceController::class, 'transactions']);
    
    // Pricing calculator (authenticated for volume discount calculation)
    Route::post('/pricing/calculate', [PricingController::class, 'calculate']);
    
    // Usage tracking
    Route::get('/usage/summary', [UsageController::class, 'summary']);
    Route::get('/usage/history', [UsageController::class, 'history']);
    
    // Payments
    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
    Route::get('/payments/{id}', [PaymentController::class, 'status']);
    Route::get('/payments', [PaymentController::class, 'history']);
    
    // API Key management
    Route::apiResource('api-keys', ApiKeyController::class);
    Route::get('/api-keys/{id}/embed', [ApiKeyController::class, 'embedCode'])->name('api-keys.embed');
});

// ============================================
// CUSTOMER SERVICE ROUTES (API Key Only)
// ============================================
// These routes are for customers using API keys (no login required)
// Usage is tracked per-request and deducted from service account balance
Route::group(['prefix' => 'chat'], function () {
    Route::middleware([\App\Http\Middleware\AuthenticateApiKey::class])->group(function () {
        Route::post('/message', [\App\Http\Controllers\ChatController::class, 'chat']);
    });
});

// ============================================
// ADMIN ROUTES (Admin only)
// ============================================
Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
    
    // User management
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::get('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show']);
    Route::put('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update']);
    Route::delete('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy']);
    
    // Service Account management
    Route::get('/service-accounts', [\App\Http\Controllers\Admin\ServiceAccountController::class, 'index']);
    Route::get('/service-accounts/{id}', [\App\Http\Controllers\Admin\ServiceAccountController::class, 'show']);
    Route::post('/service-accounts/{id}/adjust-balance', [\App\Http\Controllers\Admin\ServiceAccountController::class, 'adjustBalance']);
    Route::post('/service-accounts/{id}/change-status', [\App\Http\Controllers\Admin\ServiceAccountController::class, 'changeStatus']);
    
    // Pricing management
    Route::post('/pricing', [\App\Http\Controllers\Admin\PricingController::class, 'store']);
    Route::put('/pricing/{id}', [\App\Http\Controllers\Admin\PricingController::class, 'update']);
    Route::delete('/pricing/{id}', [\App\Http\Controllers\Admin\PricingController::class, 'destroy']);
    Route::post('/pricing/tiers', [\App\Http\Controllers\Admin\PricingController::class, 'storeTier']);
    Route::delete('/pricing/tiers/{id}', [\App\Http\Controllers\Admin\PricingController::class, 'destroyTier']);
    
    // Analytics & Reports
    Route::get('/analytics/usage', [\App\Http\Controllers\Admin\AnalyticsController::class, 'usage']);
    Route::get('/analytics/revenue', [\App\Http\Controllers\Admin\AnalyticsController::class, 'revenue']);
    Route::get('/analytics/users', [\App\Http\Controllers\Admin\AnalyticsController::class, 'users']);
});
