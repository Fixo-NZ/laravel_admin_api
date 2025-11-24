<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Homeowner;
use App\Models\HomeownerJobOffer;
use App\Notifications\ScheduleNotification;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    /**
     * Fetch all schedules (job offers with start/end time)
     */
    public function index()
    {
        // Load homeowner info
        $offers = HomeownerJobOffer::with('homeowner:id,first_name,last_name,middle_name,email,address,phone')
            ->get();

        return response()->json([
            'schedules' => $offers,
        ]);
    }

    /**
     * Create a new schedule (job offer event)
     */
    public function store(Request $request)
    {
        $request->validate([
            'homeowner_id' => 'required|exists:homeowners,id',
            'service_category_id' => 'required|exists:service_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'job_type' => 'required|in:standard,urgent,recurrent',
            'job_size' => 'required|in:small,medium,large',

            // Schedule fields
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',

            // Optional
            'preferred_date' => 'nullable|date',
            'frequency' => 'nullable|in:daily,weekly,monthly,quarterly,yearly,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $jobOffer = HomeownerJobOffer::create($request->all());

        // Notify homeowner
        $homeowner = Homeowner::find($request->homeowner_id);
        if ($homeowner) {
            $homeowner->notify(new ScheduleNotification($jobOffer));
        }

        return response()->json([
            'message' => 'Schedule created successfully and homeowner notified.',
            'schedule' => $jobOffer,
        ], Response::HTTP_CREATED);
    }

    /**
     * Reschedule an existing job offer
     */
    public function reschedule(Request $request, HomeownerJobOffer $schedule)
    {
        try {
            $messages = [
                'start_time.required' => 'Start time is required.',
                'start_time.date' => 'Start time must be a valid date.',
                'start_time.after_or_equal' => 'Start time must be in the future.',
                'end_time.required' => 'End time is required.',
                'end_time.date' => 'End time must be a valid date.',
                'end_time.after' => 'End time must be after start time.',
            ];

            $validated = $request->validate([
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
            ], $messages);

            $schedule->update([
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'status' => 'pending',           // or 'rescheduled' if you prefer
                'rescheduled_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Schedule successfully rescheduled',
                'schedule' => $schedule
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rescheduling the schedule.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel a job offer schedule
     */
    public function cancel(HomeownerJobOffer $schedule)
    {
        try {
            $schedule->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Schedule successfully cancelled',
                'schedule' => $schedule
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the schedule.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
