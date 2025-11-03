<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use Illuminate\Http\Request;
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
        Route::post('upload-avatar', [TradieAuthController::class, 'uploadAvatar']);

        // Profile Setup Routes
        Route::prefix('profile-setup')->group(function () {
            Route::post('basic-info', [App\Http\Controllers\Api\Profile\TradieSetupController::class, 'updateBasicInfo']);
            Route::post('skills', [App\Http\Controllers\Api\Profile\TradieSetupController::class, 'updateSkillsAndService']);
            Route::post('availability', [App\Http\Controllers\Api\Profile\TradieSetupController::class, 'updateAvailability']);
            Route::post('portfolio', [App\Http\Controllers\Api\Profile\TradieSetupController::class, 'updatePortfolio']);
            Route::post('complete', [App\Http\Controllers\Api\Profile\TradieSetupController::class, 'completeSetup']);
            Route::get('get-profile', [App\Http\Controllers\Api\Profile\TradieSetupController::class, 'getProfile']);
            Route::get('get-skills', [App\Http\Controllers\Api\Profile\TradieSetupController::class, 'getSkills']);
        });
    });
});

// Protected routes for authenticated users
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
