<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

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

// Homeowner Authentication Routes
Route::prefix('homeowner')->group(function () {
    Route::post('register', [HomeownerAuthController::class, 'register']);
    Route::post('login', [HomeownerAuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [HomeownerAuthController::class, 'logout']);
        Route::get('me', [HomeownerAuthController::class, 'me']);
    });
});

// Tradie Authentication Routes
Route::prefix('tradie')->group(function () {
    Route::post('register', [TradieAuthController::class, 'register']);
    Route::post('login', [TradieAuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [TradieAuthController::class, 'logout']);
        Route::get('me', [TradieAuthController::class, 'me']);
    });
});

// Protected routes for authenticated users
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

// Public routes - anyone can view reviews
Route::prefix('reviews')->group(function () {
    // Get provider reviews and stats
    // Change from provider to tradie
    Route::get('tradie/{tradieId}', [ReviewController::class, 'getTradieReviews']);
    Route::get('tradie/{tradieId}/stats', [ReviewController::class, 'getTradieStats']);
    
    // Get job review
    Route::get('job/{jobId}', [ReviewController::class, 'getJobReview']);
});

// Protected routes - require authentication
Route::middleware('auth:sanctum')->prefix('reviews')->group(function () {
    // Check if job can be reviewed
    Route::get('can-review/{jobId}', [ReviewController::class, 'canReview']);
    
    // Submit review
    Route::post('/', [ReviewController::class, 'store']);
    
    // My reviews
    Route::get('my-reviews', [ReviewController::class, 'myReviews']);
    
    // Mark as helpful
    Route::post('{reviewId}/helpful', [ReviewController::class, 'markHelpful']);
    
    // Report review
    Route::post('{reviewId}/report', [ReviewController::class, 'reportReview']);
    });
});

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now(),
    ]);
});