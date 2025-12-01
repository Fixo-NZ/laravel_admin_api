<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TradieRecommendationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\BookingController;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UrgentBookingController;
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

// Create backend API to fetch recommended tradies for a given job request. (G4 - #52)
Route::get('/jobs/{jobId}/recommend-tradies', [TradieRecommendationController::class, 'recommend']);

// Service tradie recommendations (bridge to existing Job-based recommender)
Route::middleware('auth:sanctum')->get('/services/{serviceId}/recommend-tradies', function ($serviceId) {
    $service = Service::findOrFail($serviceId);
    
    // Find JobRequest that matches this service (by homeowner and category)
    // Get the category ID directly from the database column
    $categoryId = DB::table('services')
        ->where('id', $service->id)
        ->value('job_category_id');
    
    $jobRequest = \App\Models\JobRequest::where('homeowner_id', $service->homeowner_id)
        ->where('job_category_id', $categoryId)
        ->where('status', '!=', 'cancelled')
        ->first();
    
    if (!$jobRequest) {
        return response()->json([
            'success' => true,
            'message' => 'No job request found for this service. Please create a job request first.',
            'data' => [],
        ], 200);
    }
    
    return app(TradieRecommendationController::class)
        ->recommend($jobRequest->id);
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

// Service API Resource Routes
Route::apiResource('services', ServiceController::class);

// Job API Resource Routes
Route::apiResource('jobs', JobController::class);


// Booking Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']); // View bookings
    Route::post('/bookings', [BookingController::class, 'store']); // Create booking
    Route::put('/bookings/{id}', [BookingController::class, 'update']); // Update booking
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']); // Cancel booking

        // Urgent Booking Routes
    Route::get('/urgent-bookings', [UrgentBookingController::class, 'index']);
    Route::post('/urgent-bookings', [UrgentBookingController::class, 'store']);
    Route::get('/urgent-bookings/{id}', [UrgentBookingController::class, 'show']);
    Route::put('/urgent-bookings/{id}', [UrgentBookingController::class, 'update']);
    Route::delete('/urgent-bookings/{id}', [UrgentBookingController::class, 'destroy']);
    Route::get('/urgent-bookings/{id}/recommendations', [UrgentBookingController::class, 'recommendations']);
});



