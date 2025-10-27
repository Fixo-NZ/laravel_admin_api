<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Schedule;
use App\Models\Homeowner;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

     protected function setUp(): void
    {
        parent::setUp();

        dd(config('database.connections.mysql.database'));
    }
    
    /** @test */
    public function it_returns_all_schedules()
    {
        $homeowner = Homeowner::factory()->create();
        Schedule::factory()->count(3)->create([
            'homeowner_id' => $homeowner->id,
        ]);

        $response = $this->getJson('/api/schedules');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'schedules' => [
                         '*' => [
                             'id',
                             'title',
                             'description',
                             'start_time',
                             'end_time',
                             'color',
                             'status',
                             'rescheduled_at',
                             'homeowner' => [
                                 'id',
                                 'first_name',
                                 'last_name',
                                 'email',
                                 'address',
                                 'phone',
                             ],
                         ],
                     ],
                 ]);
    }

    /** @test */
    public function it_reschedules_a_schedule()
    {
        $schedule = Schedule::factory()->create([
            'status' => 'scheduled',
        ]);

        $newStart = now()->addDay()->format('Y-m-d H:i:s');
        $newEnd = now()->addDays(2)->format('Y-m-d H:i:s');

        $response = $this->postJson("/api/schedules/{$schedule->id}/reschedule", [
            'start_time' => $newStart,
            'end_time' => $newEnd,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Schedule successfully rescheduled',
                 ]);

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'status' => 'rescheduled',
        ]);
    }

    /** @test */
    public function test_it_cancels_a_schedule()
    {
    $schedule = Schedule::factory()->create();

    $response = $this->postJson("/api/schedules/{$schedule->id}/cancel");

    $response->assertStatus(200)
             ->assertJson([
                 'message' => 'Schedule successfully cancelled and deleted',
             ]);

    $this->assertDatabaseMissing('schedules', [
        'id' => $schedule->id,
    ]);
    }

}
