<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Homeowner;
use App\Models\Schedule;
use App\Notifications\ScheduleNotification;

class ScheduleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'color' => 'nullable|string',
        ]);

        $schedule = Schedule::create($request->all());

        // Send notification to a specific homeowner (id = 1 as example)
        $homeowner = Homeowner::find(1);
        if ($homeowner) {
            $homeowner->notify(new ScheduleNotification($schedule));
        }

        return response()->json([
            'message' => 'Schedule created and email sent to homeowner!',
            'schedule' => $schedule,
        ]);
    }
}
