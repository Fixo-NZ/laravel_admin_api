<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use App\Http\Controllers\PaymentController; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupportTicketController;

// Support Ticket Routes
Route::prefix('support')->group(function () {
    Route::post('/tickets', [SupportTicketController::class, 'store']);    // Create
    Route::get('/tickets', [SupportTicketController::class, 'index']);    // Read all
    Route::get('/tickets/{id}', [SupportTicketController::class, 'show']); // Read one
    Route::put('/tickets/{id}', [SupportTicketController::class, 'update']); // Update
    Route::delete('/tickets/{id}', [SupportTicketController::class, 'destroy']); // Delete
});

// Homeowner Authentication Routes
Route::prefix('homeowner')->group(function () {
    Route::post('register', [HomeownerAuthController::class, 'register']);
    Route::post('login', [HomeownerAuthController::class, 'login']);
    Route::post('reset-password-request', [HomeownerAuthController::class, 'resetPasswordRequest']);
    Route::post('request-otp', [HomeownerAuthController::class, 'requestOtp']);
    Route::post('verify-otp', [HomeownerAuthController::class, 'verifyOtp']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/reset-password', [HomeownerAuthController::class, 'resetPassword']);
        Route::post('logout', [HomeownerAuthController::class, 'logout']);
        Route::get('me', [HomeownerAuthController::class, 'me']);
    });
});

// Tradie Authentication Routes
Route::prefix('tradie')->group(function () {
    Route::post('register', [TradieAuthController::class, 'register']);
    Route::post('login', [TradieAuthController::class, 'login']);
    Route::post('reset-password-request', [TradieAuthController::class, 'resetPasswordRequest']);
    Route::post('request-otp', [TradieAuthController::class, 'requestOtp']);
    Route::post('verify-otp', [TradieAuthController::class, 'verifyOtp']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/reset-password', [TradieAuthController::class, 'resetPassword']);
        Route::post('logout', [TradieAuthController::class, 'logout']);
        Route::get('me', [TradieAuthController::class, 'me']);
    });
});

// Public payment route
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/payment/process', [PaymentController::class, 'processPayment']);
    Route::get('/payments/{id}/decrypt', [PaymentController::class, 'viewDecryptedPayment']);
    Route::delete('/payments/{id}/delete', [PaymentController::class, 'deletePayment']);
    Route::put('/payments/{id}/update', [PaymentController::class, 'updatePayment']);
});

// Protected routes (for authenticated users)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
