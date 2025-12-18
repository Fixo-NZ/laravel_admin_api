<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use App\Http\Controllers\Api\Auth\UserAuthController;
use App\Http\Controllers\Api\Homeowner\HomeownerJobOfferController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\JobOfferController;
use App\Http\Controllers\PaymentController; 
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

// Booking Routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
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

    Route::prefix('auth')->group(function () {
        Route::get('verify-email/{id}/{hash}', [HomeownerAuthController::class, 'verifyEmail'])
            ->middleware('signed')
            ->name('homeowner.verification.verify');

        Route::post('resend-email-verification', [HomeownerAuthController::class, 'resendEmailVerification'])
            ->name('homeowner.verification.resend');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/reset-password', [HomeownerAuthController::class, 'resetPassword']);
        Route::post('logout', [HomeownerAuthController::class, 'logout']);
        Route::get('me', [HomeownerAuthController::class, 'me']);
    });
});

Route::prefix('user')->group(function () {
    Route::post('login', [UserAuthController::class, 'login']);
    
});

// Tradie Authentication Routes
Route::prefix('tradie')->group(function () {
    Route::post('register', [TradieAuthController::class, 'register']);
    Route::post('login', [TradieAuthController::class, 'login']);
    Route::post('reset-password-request', [TradieAuthController::class, 'resetPasswordRequest']);
    Route::post('request-otp', [TradieAuthController::class, 'requestOtp']);
    Route::post('verify-otp', [TradieAuthController::class, 'verifyOtp']);

    Route::prefix('auth')->group(function () {
        Route::get('verify-email/{id}/{hash}', [TradieAuthController::class, 'verifyEmail'])
            ->middleware('signed')
            ->name('tradie.verification.verify');

        Route::post('resend-email-verification', [TradieAuthController::class, 'resendEmailVerification'])
            ->name('tradie.verification.resend');
    });

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::put('/reset-password', [TradieAuthController::class, 'resetPassword']);
        Route::post('logout', [TradieAuthController::class, 'logout']);
        Route::get('me', [TradieAuthController::class, 'me']);
    });
});

// Public payment route (protected + rate limited)
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/payment/process', [PaymentController::class, 'processPayment']);
    Route::get('/payments/{id}/decrypt', [PaymentController::class, 'viewDecryptedPayment']);
    Route::delete('/payments/{id}/delete', [PaymentController::class, 'deletePayment']);
    Route::put('/payments/{id}/update', [PaymentController::class, 'updatePayment']);
});

// Protected routes (for authenticated users)
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Public Job and Service Routes (POSTMAN)
Route::prefix('jobs')->group(function () {

    // Categories & Services
    Route::get('/categories', [ServiceController::class, 'index']);
    Route::get('/categories/{id}', [ServiceController::class, 'indexSpecificCategory'])->whereNumber('id');
    Route::get('/categories/{id}/services', [ServiceController::class, 'indexSpecificCategoryServices'])->whereNumber('id');
    Route::get('/services', [ServiceController::class, 'indexService']);
    Route::get('/services/{id}', [ServiceController::class, 'indexSpecificService'])->whereNumber('id');

    // Job Offers
    Route::get('/job-offers/browse', [JobOfferController::class, 'browse']);
    Route::get('/job-offers', [JobOfferController::class, 'index']);
    Route::get('/job-offers/{id}', [JobOfferController::class, 'show'])->whereNumber('id');
});


// Homeowner 
// Route::prefix('jobs')->middleware('auth:sanctum')->group(function () {
//     Route::get('/job-offers', [JobOfferController::class, 'index']);
    
//     Route::get('/job-offers/{id}', [JobOfferController::class, 'show']);
//     Route::put('/job-offers/{id}', [JobOfferController::class, 'update']);
//     Route::delete('/job-offers/{id}', [JobOfferController::class, 'destroy']);
// });

Route::prefix('homeowner')->middleware('auth:sanctum')->group(function () {
    Route::get('/job-offers', [HomeownerJobOfferController::class, 'myJobOffers']);
    Route::post('/job-offers', [HomeownerJobOfferController::class, 'store']);
    Route::post('/job-offers', [HomeownerJobOfferController::class, 'store']);
    Route::get('/job-offers/{id}', [HomeownerJobOfferController::class, 'show']);
    Route::put('/job-offers/{id}', [HomeownerJobOfferController::class, 'update']);
    Route::delete('/job-offers/{id}', [HomeownerJobOfferController::class, 'destroy']);
});
