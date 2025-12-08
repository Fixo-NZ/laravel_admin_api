<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    // Check availability
    private function isAvailable($tradie_id, $start, $end, $excludeBookingId = null) {
        $query = Booking::where('tradie_id', $tradie_id)
                        ->where('status', '!=', 'canceled')
                        ->where(function($q) use ($start, $end) {
                            $q->whereBetween('booking_start', [$start, $end])
                              ->orWhereBetween('booking_end', [$start, $end]);
                        });
        if ($excludeBookingId) $query->where('id', '!=', $excludeBookingId);
        return $query->count() == 0;
    }

    // Create booking
    public function store(Request $request)
    {
        $request->validate([
            'tradie_id' => 'required|exists:tradies,id',
            'service_id' => 'required|exists:services,id',
            'booking_start' => 'required|date|after:now',
            'booking_end' => 'required|date|after:booking_start'
        ]);

        if (!$this->isAvailable($request->tradie_id, $request->booking_start, $request->booking_end)) {
            return response()->json([
                'success' => false,
                'message' => 'Tradie not available in the selected time slot.'
            ], 400);
        }

        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'homeowner_id' => $homeowner->id,
                'tradie_id' => $request->tradie_id,
                'service_id' => $request->service_id,
                'booking_start' => $request->booking_start,
                'booking_end' => $request->booking_end,
                'status' => 'pending'
            ]);

            BookingLog::create([
                'booking_id' => $booking->id,
                'user_id' => $homeowner->id,
                'action' => 'created',
                'notes' => 'Booking created.'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully.',
                'booking' => $booking
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking.'
            ], 500);
        }
    }

    // View all bookings for homeowner
    public function index(Request $request) {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        // Log for debugging (can be removed in production)
        //\Log::info("Booking index called by homeowner: {$homeowner->id} ({$homeowner->email})");
        
        $bookings = Booking::where('homeowner_id', $homeowner->id)
                    ->with(['tradie', 'service', 'logs' => function($q) { $q->orderBy('created_at', 'desc'); }])
                    ->orderBy('booking_start', 'desc')
                    ->get();
        
        //\Log::info("Found {$bookings->count()} bookings for homeowner {$homeowner->id}");

        return response()->json($bookings, 200);
    }

    // Grouped booking history (upcoming + past)
    public function history(Request $request) {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        $bookings = Booking::where('homeowner_id', $homeowner->id)
                            ->with(['tradie', 'service'])
                            ->orderBy('booking_start', 'desc')
                            ->get();

        $now = Carbon::now();

        $upcoming = $bookings->filter(function($b) use ($now) {
            return Carbon::parse($b->booking_start)->gt($now);
        })->values();

        $past = $bookings->filter(function($b) use ($now) {
            return Carbon::parse($b->booking_start)->lte($now);
        })->values();

        return response()->json([
            'upcoming' => $upcoming,
            'past' => $past
        ], 200);
    }

    // Show booking details (with logs)
    public function show(Request $request, $id) {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        $booking = Booking::where('id', $id)
                          ->where('homeowner_id', $homeowner->id)
                          ->with(['tradie', 'service', 'logs'])
                          ->firstOrFail();

        return response()->json($booking, 200);
    }

    // Update booking
    public function update(Request $request, $id)
    {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        $booking = Booking::where('id', $id)->where('homeowner_id', $homeowner->id)->firstOrFail();

        $request->validate([
            'booking_start' => 'required|date|after:now',
            'booking_end' => 'required|date|after:booking_start'
        ]);

        if (!$this->isAvailable($booking->tradie_id, $request->booking_start, $request->booking_end, $booking->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Tradie not available in the new time slot.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $booking->update([
                'booking_start' => $request->booking_start,
                'booking_end' => $request->booking_end
            ]);

            BookingLog::create([
                'booking_id' => $booking->id,
                'user_id' => $homeowner->id,
                'action' => 'updated',
                'notes' => 'Booking updated.'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully.',
                'booking' => $booking
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking.'
            ], 500);
        }
    }

    // Cancel booking
    public function cancel(Request $request, $id)
    {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        $booking = Booking::where('id', $id)->where('homeowner_id', $homeowner->id)->firstOrFail();

        if ($booking->status == 'canceled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking already canceled.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $booking->status = 'canceled';
            $booking->save();

            BookingLog::create([
                'booking_id' => $booking->id,
                'user_id' => $homeowner->id,
                'action' => 'canceled',
                'notes' => 'Booking canceled.'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking canceled successfully.',
                'booking' => $booking
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.'
            ], 500);
        }
    }
}
