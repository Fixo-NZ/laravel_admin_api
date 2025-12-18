<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Service;
use App\Models\Booking;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class BookingOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_booking()
    {
        $owner = Homeowner::factory()->create();
        $other = Homeowner::factory()->create();

        $tradie = Tradie::factory()->create();
        $service = Service::create(["name" => "Ownership Service", "description" => "", "category" => "general", "is_active" => true]);

        $booking = Booking::create([
            'homeowner_id' => $owner->id,
            'tradie_id' => $tradie->id,
            'service_id' => $service->id,
            'booking_start' => Carbon::now()->addDay()->toDateTimeString(),
            'booking_end' => Carbon::now()->addDay()->addHour()->toDateTimeString(),
            'status' => 'pending'
        ]);

        // Other user should not be able to view
        Sanctum::actingAs($other);
        $this->getJson('/api/bookings/' . $booking->id)->assertStatus(404);

        // Other user should not be able to update
        $this->putJson('/api/bookings/' . $booking->id, [
            'booking_start' => Carbon::now()->addDays(2)->toDateTimeString(),
            'booking_end' => Carbon::now()->addDays(2)->addHour()->toDateTimeString(),
        ])->assertStatus(404);

        // Other user should not be able to cancel
        $this->postJson('/api/bookings/' . $booking->id . '/cancel')->assertStatus(404);

        // Owner can view/update/cancel
        Sanctum::actingAs($owner);
        $this->getJson('/api/bookings/' . $booking->id)->assertStatus(200);

        $resp = $this->putJson('/api/bookings/' . $booking->id, [
            'booking_start' => Carbon::now()->addDays(3)->toDateTimeString(),
            'booking_end' => Carbon::now()->addDays(3)->addHour()->toDateTimeString(),
        ])->assertStatus(200)->json();

        $this->postJson('/api/bookings/' . $booking->id . '/cancel')->assertStatus(200);
    }
}
