<?php

namespace Tests\Feature;

use App\Models\Homeowner;
use App\Models\Schedule;
use App\Notifications\ScheduleNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_fetches_all_schedules_with_homeowner_data()
    {
        $homeowner = Homeowner::factory()->create();
        $schedule = Schedule::factory()->create(['homeowner_id' => $homeowner->id]);

        $response = $this->getJson('/api/schedules');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'schedules' => [
                         [
                             'id',
                             'title',
                             'start_time',
                             'end_time',
                             'homeowner' => ['id', 'first_name', 'last_name', 'email']
                         ]
                     ]
                 ]);
    }

    #[Test]
    public function it_reschedules_an_existing_schedule()
    {
        $schedule = Schedule::factory()->create([
            'start_time' => now(),
            'end_time' => now()->addHour(),
        ]);

        $payload = [
            'start_time' => now()->addDays(1)->toDateTimeString(),
            'end_time'   => now()->addDays(1)->addHours(2)->toDateTimeString(),
        ];

        $response = $this->postJson("/api/schedules/{$schedule->id}/reschedule", $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Schedule successfully rescheduled',
                 ]);

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'start_time' => $payload['start_time'],
            'end_time' => $payload['end_time'],
        ]);
    }

    #[Test]
    public function it_cancels_a_schedule()
    {
        $schedule = Schedule::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/schedules/{$schedule->id}/cancel");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Schedule successfully cancelled',
                 ]);

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'status' => 'cancelled',
        ]);
    }
}
