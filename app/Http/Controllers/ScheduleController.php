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
        try {
            // Custom validation messages
            $messages = [
                'start_time.required'        => 'Start time is required.',
                'start_time.date'            => 'Start time must be a valid date.',
                'start_time.after_or_equal'  => 'Start time must be in the future.',
                'end_time.required'          => 'End time is required.',
                'end_time.date'              => 'End time must be a valid date.',
                'end_time.after'             => 'End time must be after start time.',
            ];

        // Validate request
        $validated = $request->validate([
            'start_time' => 'required|date|after_or_equal:now',
            'end_time'   => 'required|date|after:start_time',
        ], $messages);

        // Update schedule
        $schedule->update([
            'start_time'     => $validated['start_time'],
            'end_time'       => $validated['end_time'],
            'status'         => 'rescheduled',
            'rescheduled_at' => now(),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Schedule successfully rescheduled',
            'schedule' => $schedule
        ], \Symfony\Component\HttpFoundation\Response::HTTP_OK);

    } catch (\Illuminate\Validation\ValidationException $e) {

        // Validation errors (422)
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY);

    } catch (\Exception $e) {

        // Unexpected errors (500)
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while rescheduling the schedule.',
            'error'   => $e->getMessage(), // remove in production for security
        ], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}




public function cancel(Schedule $schedule)
{
    try {
        // Use the model's cancel() method
        $schedule->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Schedule successfully cancelled',
            'schedule' => $schedule
        ], Response::HTTP_OK);

    } catch (\Exception $e) {
        // Handle unexpected errors
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while cancelling the schedule.',
            'error'   => $e->getMessage(), // remove in production
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
}