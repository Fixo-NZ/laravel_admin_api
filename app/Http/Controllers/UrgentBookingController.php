<?php

namespace App\Http\Controllers;

use App\Models\UrgentBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UrgentBookingController extends Controller
{
    // GET /api/urgent-bookings
    public function index()
    {
        $homeownerId = Auth::id();

        $bookings = UrgentBooking::where('homeowner_id', $homeownerId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($bookings, 200);
    }

    // POST /api/urgent-bookings
    public function store(Request $request)
    {
        $homeownerId = Auth::id();

        $validated = $request->validate([
            'job_id' => 'required|integer',
            'notes' => 'nullable|string',
            'priority_level' => 'nullable|string|max:50',
            'service_name' => 'nullable|string|max:255',
            'preferred_date' => 'nullable|string|max:255',
            'preferred_time_window' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $booking = UrgentBooking::create([
            'homeowner_id' => $homeownerId,
            'job_id' => $validated['job_id'],
            'status' => 'pending',
            'priority_level' => $validated['priority_level'] ?? null,
            'requested_at' => now(),
            'notes' => $validated['notes'] ?? null,
            'service_name' => $validated['service_name'] ?? null,
            'preferred_date' => $validated['preferred_date'] ?? null,
            'preferred_time_window' => $validated['preferred_time_window'] ?? null,
            'contact_name' => $validated['contact_name'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json([
            'message' => 'Urgent booking created successfully.',
            'booking' => $booking,
        ], 201);
    }

    // GET /api/urgent-bookings/{id}
    public function show($id)
    {
        $booking = UrgentBooking::where('id', $id)
            ->where('homeowner_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'booking' => $booking,
        ], 200);
    }

    // PUT /api/urgent-bookings/{id}
    public function update(Request $request, $id)
    {
        $booking = UrgentBooking::where('id', $id)
            ->where('homeowner_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'nullable|string|max:50',
            'tradie_id' => 'nullable|integer',
        ]);

        if (isset($validated['status'])) {
            $booking->status = $validated['status'];

            if ($booking->responded_at === null && $validated['status'] !== 'pending') {
                $booking->responded_at = now();
            }
        }

        if (isset($validated['tradie_id'])) {
            $booking->tradie_id = $validated['tradie_id'];
        }

        $booking->save();

        return response()->json([
            'message' => 'Urgent booking updated successfully.',
            'booking' => $booking,
        ], 200);
    }

    // DELETE /api/urgent-bookings/{id}
    public function destroy($id)
    {
        $booking = UrgentBooking::where('id', $id)
            ->where('homeowner_id', Auth::id())
            ->firstOrFail();

        $booking->delete();

        return response()->json([
            'message' => 'Urgent booking deleted successfully.',
        ], 200);
    }

    // GET /api/urgent-bookings/{id}/recommendations
    public function recommendations($id)
    {
        $booking = UrgentBooking::where('id', $id)
            ->where('homeowner_id', Auth::id())
            ->firstOrFail();

        // TODO: implement real recommendation logic
        return response()->json([
            'booking_id' => $booking->id,
            'recommendations' => [],
        ]);
    }
}


