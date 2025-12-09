<?php

use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Http\Controllers\Api\Auth\TradieAuthController;
use App\Http\Controllers\Api\Auth\UserAuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\JobOfferController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\BroadcastTestController; 
use App\Services\FCMService;
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
    Route::post('reset-password-request', [HomeownerAuthController::class, 'resetPasswordRequest']);
    Route::post('request-otp', [HomeownerAuthController::class, 'requestOtp']);
    Route::post('verify-otp', [HomeownerAuthController::class, 'verifyOtp']);
    
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

// Calendar
Route::prefix('schedules')->middleware('auth:sanctum')->group(function () {
    // Fetch values for calendar
    Route::get('/', [ScheduleController::class, 'index']);

    // Reschedule for calendar
    Route::post('/{schedule}/reschedule', [ScheduleController::class, 'reschedule']);

    // Cancel for calendar
    Route::post('/{schedule}/cancel', [ScheduleController::class, 'cancel']);

    // Push notificaiton 
    Route::post('/tradie/update-token', [ScheduleController::class, 'updateFcmToken']);

    // Homeowner FCM token update
    Route::post('/homeowner/update-token', [ScheduleController::class, 'updateHomeownerFcmToken']);

    // Accept a job offer (notifies homeowner)
    Route::post('/{schedule}/accept', [ScheduleController::class, 'acceptOffer']);
});



// Public Job and Service Routes (POSTMAN)
Route::prefix('jobs')->group(function () {
    Route::get('/categories', [ServiceController::class, 'index']);
    Route::get('/categories/{id}', [ServiceController::class, 'indexSpecificCategory']);
    Route::get('/categories/{id}/services', [ServiceController::class, 'indexSpecificCategoryServices']);
    Route::get('/services', [ServiceController::class, 'indexService']);
    Route::get('/services/{id}', [ServiceController::class, 'indexSpecificService']);
});


// Homeowner 
Route::prefix('jobs')->middleware('auth:sanctum')->group(function () {
    Route::get('/job-offers', [JobOfferController::class, 'index']);
    Route::post('/job-offers', [JobOfferController::class, 'store']);
    Route::get('/job-offers/{id}', [JobOfferController::class, 'show']);
    Route::put('/job-offers/{id}', [JobOfferController::class, 'update']);
    Route::delete('/job-offers/{id}', [JobOfferController::class, 'destroy']);
});

// Firebase Test Routes (No CSRF protection needed)
Route::get('/test-firebase-connection', function () {
    try {
        $fcmService = app(FCMService::class);
        
        // Just test if we can create the service without errors
        \Log::info('Firebase connection test successful');
        
        return response()->json([
            'success' => true,
            'message' => 'Firebase connection is working!'
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type');
        
    } catch (\Exception $e) {
        \Log::error('Firebase connection test failed', [
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Firebase connection failed',
            'error' => $e->getMessage()
        ], 500)->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
});

Route::post('/test-push-custom', function (\Illuminate\Http\Request $request) {
    try {
        $fcmService = app(FCMService::class);
        
        $token = $request->input('token');
        $title = $request->input('title', 'Test Notification');
        $body = $request->input('body', 'Test message from Laravel');
        $data = $request->input('data', [
            'type' => 'test',
            'timestamp' => now()->toISOString(),
        ]);
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'FCM token is required'
            ], 400)->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type');
        }
        
        // For testing with invalid tokens, let's add some validation
        if ($token === 'test_token' || strlen($token) < 50) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid FCM token (test_token is not valid)',
                'note' => 'Get a real FCM token from your Flutter app'
            ], 400)->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type');
        }
        
        $result = $fcmService->send($token, $title, $body, $data);
        
        \Log::info('Custom push notification sent', [
            'token' => substr($token, 0, 20) . '...',
            'title' => $title,
            'result' => $result
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully!',
            'result' => $result
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type');
        
    } catch (\Exception $e) {
        \Log::error('Custom push notification failed', [
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to send notification',
            'error' => $e->getMessage()
        ], 500)->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
});
