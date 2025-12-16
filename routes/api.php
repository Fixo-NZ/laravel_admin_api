<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use App\Http\Controllers\Api\FirebaseController;
use App\Http\Controllers\Api\HomeownerController;
use App\Http\Controllers\Api\TradieController;
use App\Http\Controllers\Api\ChatController;
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
    });
});

// Firebase Integration Routes
Route::prefix('firebase')->group(function () {
    Route::post('sync-homeowner', [FirebaseController::class, 'syncHomeowner']);
    Route::post('sync-tradie', [FirebaseController::class, 'syncTradie']);
    Route::post('verify-token', [FirebaseController::class, 'verifyToken']);
    Route::get('user/{firebase_uid}', [FirebaseController::class, 'getUserByFirebaseUid']);
});

// Homeowner API Routes
Route::prefix('homeowners')->group(function () {
    Route::get('/', [HomeownerController::class, 'index']);
    Route::post('/', [HomeownerController::class, 'store']);
    Route::get('{id}', [HomeownerController::class, 'show']);
    Route::put('{id}', [HomeownerController::class, 'update']);
    Route::delete('{id}', [HomeownerController::class, 'destroy']);
    Route::get('firebase/{firebase_uid}', [HomeownerController::class, 'getByFirebaseUid']);
});

// Tradie API Routes
Route::prefix('tradies')->group(function () {
    Route::get('/', [TradieController::class, 'index']);
    Route::post('/', [TradieController::class, 'store']);
    Route::get('{id}', [TradieController::class, 'show']);
    Route::put('{id}', [TradieController::class, 'update']);
    Route::delete('{id}', [TradieController::class, 'destroy']);
    Route::get('firebase/{firebase_uid}', [TradieController::class, 'getByFirebaseUid']);
    Route::post('search', [TradieController::class, 'search']);
});

// Chat API Routes (Laravel controls Firestore writes)
Route::prefix('chats')->group(function () {
    Route::get('user-chats', [ChatController::class, 'getUserChats']);
    Route::get('{chat_id}/messages', [ChatController::class, 'getChatMessages']);
    Route::post('send-message', [ChatController::class, 'sendMessage']); // Laravel writes to Firestore
    Route::post('create-room', [ChatController::class, 'createRoom']); // Create chat room
    Route::post('block-user', [ChatController::class, 'blockUser']); // Block user
    Route::post('unblock-user', [ChatController::class, 'unblockUser']); // Unblock user
    Route::post('mark-as-read', [ChatController::class, 'markAsRead']);
    Route::get('stats', [ChatController::class, 'getChatStats']);
    Route::post('search-messages', [ChatController::class, 'searchMessages']);
    Route::post('sync-firebase', [ChatController::class, 'syncFirebaseMessages']);
});

// Test routes
Route::get('test', [App\Http\Controllers\Api\TestController::class, 'test']);
Route::get('test-database', [App\Http\Controllers\Api\TestController::class, 'testDatabase']);
Route::post('test-create-homeowner', [App\Http\Controllers\Api\TestController::class, 'testCreateHomeowner']);

// Direct save routes (no Firebase dependency)
Route::post('direct-save-homeowner', [App\Http\Controllers\Api\DirectSaveController::class, 'saveHomeowner']);
Route::post('direct-save-tradie', [App\Http\Controllers\Api\DirectSaveController::class, 'saveTradie']);
Route::get('get-homeowners', [App\Http\Controllers\Api\DirectSaveController::class, 'getHomeowners']);

// Simple homeowner creation without Firebase dependency
Route::post('homeowners-simple', [App\Http\Controllers\Api\TestController::class, 'testCreateHomeowner']);

// Protected routes for authenticated users
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Chat Sync API - save Firebase messages to MySQL
Route::prefix('chat-sync')->group(function () {
    Route::post('from-firebase', [ChatController::class, 'storeFirebaseMessagesToMySQL']);
    Route::post('to-firebase', [ChatController::class, 'pushMessagesToFirebase']);
});
