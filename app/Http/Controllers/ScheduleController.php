<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Homeowner;
use App\Models\Schedule;
use App\Notifications\ScheduleNotification;
use Symfony\Component\HttpFoundation\Response;
class ScheduleController extends Controller
{
    public function index()
    {
        // Get all schedules with homeowner data
        $schedules = Schedule::with('homeowner:id,first_name,last_name,middle_name,email,address,phone')->get();

        return response()->json([
            'schedules' => $schedules,
        ]);
    }

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
     public function reschedule(Request $request, Schedule $schedule)
{
    $validated = $request->validate([
        'start_time' => 'required|date',
        'end_time'   => 'required|date|after:start_time',
    ]);

    $schedule->update([
        'start_time' => $validated['start_time'],
        'end_time'   => $validated['end_time'],
        'rescheduled_at' => now(),
    ]);

    return response()->json([
        'message' => 'Schedule successfully rescheduled',
        'schedule' => $schedule
    ], \Symfony\Component\HttpFoundation\Response::HTTP_OK);
}


    public function cancel(Schedule $schedule)
    {
         $schedule->update([
        'status' => 'cancelled'
    ]);

    return response()->json([
        'message' => 'Schedule successfully cancelled',
        'schedule' => $schedule
    ], Response::HTTP_OK);
}
}