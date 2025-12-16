<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Notifications\JobAcceptedNotification;
use App\Notifications\JobDeclinedNotification;
use App\Notifications\JobRequestAccepted;
use App\Notifications\JobRequestDeclined;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookedTradieController extends Controller
{
    // ACCEPT booking
    public function accept(Request $request, $id)
    {
        $tradie = $request->user();

        $booking = Booking::where('id', $id)
            ->where('tradie_id', $tradie->id)
            ->firstOrFail();

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already processed',
                'current_status' => $booking->status,
            ], 409);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'confirmed']);

            // Notify homeowner
            $booking->homeowner->notify(
                new JobRequestAccepted($booking)
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking accepted',
        ]);
    }

    // DECLINE booking
    public function decline(Request $request, $id)
    {
        $tradie = $request->user();

        $booking = Booking::where('id', $id)
            ->where('tradie_id', $tradie->id)
            ->firstOrFail();

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already processed',
                'current_status' => $booking->status,
            ], 409);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'canceled']);

            // Notify homeowner
            $booking->homeowner->notify(
                new JobRequestDeclined($booking)
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking declined',
        ]);
    }
}
