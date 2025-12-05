<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Homeowner;
use App\Models\HomeownerJobOffer;
use App\Notifications\ScheduleNotification;
use Symfony\Component\HttpFoundation\Response;
use App\Services\FCMService;

class ScheduleController extends Controller
{
    protected $fcm;

    public function __construct(FCMService $fcm)
    {
        $this->fcm = $fcm;
    }

    /**
     * Fetch all schedules (job offers with start/end time)
     */
    public function index(Request $request)
{
    // Get the currently authenticated tradie
    $tradie = $request->user(); 
    $tradieId = $tradie->id;

    // Load schedules + homeowner + service category
    $offers = HomeownerJobOffer::with([
        'homeowner:id,first_name,last_name,middle_name,email,address,phone',
        'category:id,name,description,icon,status'
    ])
    ->where('tradie_id', $tradieId)
    ->get();

    return response()->json([
        'schedules' => $offers,
    ]);
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
                'status' => 'in_progress',           // or 'rescheduled' if you prefer
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

    // Update FCM token for tradie
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        // Authenticated tradie
        $tradie = $request->user();

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
    }

        $tradie->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM Token updated successfully',
            'token' => $tradie->fcm_token
        ]);
    }

    // Update FCM token for homeowner
    public function updateHomeownerFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        // Authenticated homeowner
        $homeowner = $request->user();

        if (!$homeowner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $homeowner->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM Token updated successfully',
            'token' => $homeowner->fcm_token
        ]);
    }

    /**
     * Accept a job offer and notify the homeowner
     */
    public function acceptOffer(Request $request, HomeownerJobOffer $schedule)
    {
        $tradie = $request->user();

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Update the job offer
        $schedule->update([
            'tradie_id' => $tradie->id,
            'status' => 'in_progress',
        ]);

        // Send FCM notification to the homeowner
        if ($schedule->homeowner && $schedule->homeowner->fcm_token) {
            $title = "Job Offer Accepted";
            $body = "{$tradie->first_name} {$tradie->last_name} accepted your job offer: {$schedule->title}";

            $data = [
                'job_id' => (string) $schedule->id,
                'type' => 'job_accepted',
            ];

            try {
                $this->fcm->send($schedule->homeowner->fcm_token, $title, $body, $data);
            } catch (\Exception $e) {
                \Log::error("Failed to send FCM to homeowner ID {$schedule->homeowner->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Job offer accepted successfully',
            'schedule' => $schedule
        ], Response::HTTP_OK);
    }

    /**
    * Send a reminder to a tradie about an upcoming job
    */
    public function sendJobReminderToTradie(HomeownerJobOffer $schedule)
    {
        $tradie = $schedule->tradie;

        if (!$tradie || !$tradie->fcm_token) {
            \Log::warning("No FCM token found for tradie ID {$schedule->tradie_id}");
            return false;
        }

        $title = "Upcoming Job Reminder";
        $body = "You have a job scheduled in 1 hour: {$schedule->title} with {$schedule->homeowner->first_name} {$schedule->homeowner->last_name}";

        $data = [
            'job_id' => (string) $schedule->id,
            'type' => 'job_reminder',
            'start_time' => $schedule->start_time->toDateTimeString(),
        ];

        try {
            $this->fcm->send($tradie->fcm_token, $title, $body, $data);
            \Log::info("Job reminder sent to tradie ID {$tradie->id}");
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to send job reminder to tradie ID {$tradie->id}: " . $e->getMessage());
            return false;
        }
    }
}


