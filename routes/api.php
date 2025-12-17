<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use App\Http\Controllers\Api\Auth\UserAuthController;
use App\Http\Controllers\Api\Homeowner\HomeownerJobOfferController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\JobOfferController;
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

Route::prefix('user')->group(function () {
    Route::post('login', [UserAuthController::class, 'login']);
    
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
Route::prefix('homeowner')->middleware('auth:sanctum')->group(function () {
    Route::get('/job-offers', [HomeownerJobOfferController::class, 'myJobOffers']);
    Route::post('/job-offers', [HomeownerJobOfferController::class, 'store']);
    Route::get('/job-offers/{id}', [HomeownerJobOfferController::class, 'show']);
    Route::put('/job-offers/{id}', [HomeownerJobOfferController::class, 'update']);
    Route::delete('/job-offers/{id}', [HomeownerJobOfferController::class, 'destroy']);
});