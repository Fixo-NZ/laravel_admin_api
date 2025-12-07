<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use App\Http\Controllers\PaymentController; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TradieRecommendationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\JobController;

// Booking Routes
Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
    // Booking History 
    Route::get('/bookings/history', [BookingController::class, 'history']); // Grouped booking history (past + upcoming)
    Route::get('/bookings/{id}', [BookingController::class, 'show']); // Booking details
    Route::get('/bookings', [BookingController::class, 'index']); // View bookings
    Route::post('/bookings', [BookingController::class, 'store']); // Create booking
    Route::put('/bookings/{id}', [BookingController::class, 'update']); // Update booking
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']); // Cancel booking
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

// Public payment route (protected + rate limited)
Route::middleware(['auth:sanctum','throttle:api'])->group(function() {
    Route::post('/payment/process', [PaymentController::class, 'processPayment']);
    Route::get('/payments/{id}/decrypt', [PaymentController::class, 'viewDecryptedPayment']);
    Route::delete('/payments/{id}/delete', [PaymentController::class, 'deletePayment']);
    Route::put('/payments/{id}/update', [PaymentController::class, 'updatePayment']);
});

// Protected routes (for authenticated users)
Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Create backend API to fetch recommended tradies for a given job request. (G4 - #52)
Route::get('/jobs/{jobId}/recommend-tradies', [TradieRecommendationController::class, 'recommend']);


// Service API Resource Routes
Route::apiResource('services', ServiceController::class);

// Job API Resource Routes
Route::apiResource('jobs', JobController::class);