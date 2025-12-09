<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\HomeownerAuthController;
use App\Events\ScheduleDisplayed;
use App\Services\FCMService;

// Web route for viewing a homeowner profile
Route::get('/homeowners/{homeowner}', [HomeownerAuthController::class, 'show'])
    ->name('homeowners.show');

// Test broadcast route
Route::get('/test-broadcast', function () {
    \Log::info('Test broadcast route called');
    \Log::info('Broadcast driver: ' . config('broadcasting.default'));
    \Log::info('Queue connection: ' . config('queue.default'));
    
    try {
        // Create and broadcast a test event
        $event = new ScheduleDisplayed([
            'schedules' => [
                [
                    'id' => 999,
                    'title' => 'Test Broadcast Message',
                    'description' => 'This is a test broadcast from Laravel Reverb',
                    'status' => 'test'
                ]
            ],
            'tradie_id' => 12,
            'total_count' => 1,
            'action' => 'test_broadcast'
        ]);
        
        \Log::info('Broadcasting event now...');
        
        // Broadcast the event
        broadcast($event)->toOthers();
        
        \Log::info('Test broadcast sent successfully');
        
        return response()->json([
            'success' => true,
            'message' => 'Test broadcast sent successfully!',
            'config' => [
                'broadcast_driver' => config('broadcasting.default'),
                'queue_connection' => config('queue.default'),
            ]
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type');
          
    } catch (\Exception $e) {
        \Log::error('Test broadcast failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        
        return response()->json([
            'success' => false,
            'message' => 'Broadcast failed: ' . $e->getMessage()
        ], 500)->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
});

// Serve the test HTML page from Laravel
Route::get('/test-reverb-page', function () {
    return file_get_contents(base_path('test_reverb.html'));
});

// Simple broadcast trigger page
Route::get('/broadcast-tester', function () {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Broadcast Tester</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 50px; }
            button { padding: 15px 30px; font-size: 16px; margin: 10px; cursor: pointer; }
            .success { background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; }
            .error { background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <h1>🚀 Broadcast Tester</h1>
        <p>Click the button below to send a test broadcast to your Flutter app:</p>
        
        <button onclick="sendBroadcast()">Send Test Broadcast</button>
        <button onclick="sendScheduleUpdate()">Send Schedule Update</button>
        <button onclick="sendScheduleCancel()">Send Schedule Cancel</button>
        
        <div id="result"></div>
        
        <script>
            function sendBroadcast() {
                fetch("/test-broadcast")
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById("result").innerHTML = 
                            `<div class="success">✅ ${data.message}</div>`;
                    })
                    .catch(error => {
                        document.getElementById("result").innerHTML = 
                            `<div class="error">❌ Error: ${error.message}</div>`;
                    });
            }
            
            function sendScheduleUpdate() {
                fetch("/api/schedules/999/reschedule", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({
                        start_time: "2025-12-08 10:00:00",
                        end_time: "2025-12-08 14:00:00"
                    })
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("result").innerHTML = 
                        `<div class="success">✅ Schedule update broadcast sent!</div>`;
                })
                .catch(error => {
                    document.getElementById("result").innerHTML = 
                        `<div class="error">❌ Error: ${error.message}</div>`;
                });
            }
            
            function sendScheduleCancel() {
                fetch("/api/schedules/999/cancel", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"}
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("result").innerHTML = 
                        `<div class="success">✅ Schedule cancel broadcast sent!</div>`;
                })
                .catch(error => {
                    document.getElementById("result").innerHTML = 
                        `<div class="error">❌ Error: ${error.message}</div>`;
                });
            }
        </script>
    </body>
    </html>';
});


