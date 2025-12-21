<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ReviewResponseController;
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
    
    Route::middleware('auth:homeowner')->group(function () {
        Route::post('logout', [HomeownerAuthController::class, 'logout']);
        Route::get('me', [HomeownerAuthController::class, 'me']);
    });
});

// Tradie Authentication Routes
Route::prefix('tradie')->group(function () {
    Route::post('register', [TradieAuthController::class, 'register']);
    Route::post('login', [TradieAuthController::class, 'login']);
    
    Route::middleware('auth:tradie')->group(function () {
        Route::post('logout', [TradieAuthController::class, 'logout']);
        Route::get('me', [TradieAuthController::class, 'me']);
    });
});

// Protected routes for authenticated users
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Public routes - anyone can view reviews
Route::prefix('reviews')->group(function () {
    Route::get('tradie/{tradieId}', [ReviewController::class, 'getTradieReviews']);
    Route::get('tradie/{tradieId}/stats', [ReviewController::class, 'getTradieStats']);
    Route::get('job/{jobId}', [ReviewController::class, 'getJobReview']);
});

// Homeowner-protected routes
Route::middleware('auth:homeowner')->prefix('reviews')->group(function () {
    Route::get('can-review/{jobId}', [ReviewController::class, 'canReview']);
    Route::post('/', [ReviewController::class, 'store']);
    Route::get('my-reviews', [ReviewController::class, 'myReviews']);
    Route::post('{reviewId}/helpful', [ReviewController::class, 'markHelpful']);
    Route::post('{reviewId}/report', [ReviewController::class, 'reportReview']);
});

// Tradie-protected routes for responses
Route::middleware('auth:tradie')->prefix('reviews')->group(function () {
    Route::get('{review}/response', [ReviewResponseController::class, 'show']);
    Route::post('{review}/response', [ReviewResponseController::class, 'store']);
    Route::patch('{review}/response', [ReviewResponseController::class, 'update']);
});

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now(),
    ]);
});
