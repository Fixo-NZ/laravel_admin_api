<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Service;
use App\Models\Booking;
use App\Models\BookingLog;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class BookingHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_returns_upcoming_and_past_bookings()
    {
        $homeowner = Homeowner::factory()->create();
        Sanctum::actingAs($homeowner);

        $tradie = Tradie::factory()->create();
        $service = Service::create(["name" => "Test Service", "description" => "", "category" => "general", "is_active" => true]);

        $pastStart = Carbon::now()->subDays(5)->toDateTimeString();
        $pastEnd = Carbon::now()->subDays(5)->addHour()->toDateTimeString();

        $futureStart = Carbon::now()->addDays(5)->toDateTimeString();
        $futureEnd = Carbon::now()->addDays(5)->addHour()->toDateTimeString();

        $past = Booking::create([
            'homeowner_id' => $homeowner->id,
            'tradie_id' => $tradie->id,
            'service_id' => $service->id,
            'booking_start' => $pastStart,
            'booking_end' => $pastEnd,
            'status' => 'completed'
        ]);

        $future = Booking::create([
            'homeowner_id' => $homeowner->id,
            'tradie_id' => $tradie->id,
            'service_id' => $service->id,
            'booking_start' => $futureStart,
            'booking_end' => $futureEnd,
            'status' => 'pending'
        ]);

        $response = $this->getJson('/api/bookings/history');
        $response->assertStatus(200)
                 ->assertJsonStructure(['upcoming', 'past']);

        $json = $response->json();
        $this->assertCount(1, $json['upcoming']);
        $this->assertCount(1, $json['past']);

        $this->assertEquals($future->id, $json['upcoming'][0]['id']);
        $this->assertEquals($past->id, $json['past'][0]['id']);
    }

    public function test_show_returns_booking_with_logs()
    {
        $homeowner = Homeowner::factory()->create();
        Sanctum::actingAs($homeowner);

        $tradie = Tradie::factory()->create();
        $service = Service::create(["name" => "Detail Service", "description" => "", "category" => "general", "is_active" => true]);

        $booking = Booking::create([
            'homeowner_id' => $homeowner->id,
            'tradie_id' => $tradie->id,
            'service_id' => $service->id,
            'booking_start' => Carbon::now()->addDay()->toDateTimeString(),
            'booking_end' => Carbon::now()->addDay()->addHour()->toDateTimeString(),
            'status' => 'confirmed'
        ]);

        BookingLog::create([
            'booking_id' => $booking->id,
            'user_id' => $homeowner->id,
            'action' => 'created',
            'notes' => 'Test log'
        ]);

        $response = $this->getJson('/api/bookings/' . $booking->id);
        $response->assertStatus(200)
                 ->assertJsonPath('id', $booking->id)
                 ->assertJsonStructure(['id','service','tradie','logs']);

        $data = $response->json();
        $this->assertNotEmpty($data['logs']);
        $this->assertEquals('Test log', $data['logs'][0]['notes']);
    }
}
