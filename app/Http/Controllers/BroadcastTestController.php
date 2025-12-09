<?php

namespace App\Http\Controllers;

use App\Events\ScheduleUpdated;
use Illuminate\Http\Request;

class BroadcastTestController extends Controller
{
    public function testBroadcast(Request $request)
    {
        $scheduleData = [
            'id' => rand(1, 100),
            'title' => 'Test Schedule Update',
            'description' => 'This is a test broadcast from Laravel Reverb',
            'status' => 'updated',
            'updated_at' => now()->toISOString(),
        ];

        // Broadcast the event
        broadcast(new ScheduleUpdated($scheduleData));

        return response()->json([
            'success' => true,
            'message' => 'Schedule update broadcasted successfully',
            'data' => $scheduleData
        ]);
    }
}
