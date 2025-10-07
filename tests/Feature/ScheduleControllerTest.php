<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Homeowner;
use App\Models\Schedule;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ScheduleNotification;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_a_schedule_and_sends_notification()
    {
        // Prevent real notifications
        Notification::fake();

        // Create a homeowner to receive notification
        $homeowner = Homeowner::factory()->create(['id' => 1]);

        // Data to send in POST request
        $data = [
            'title' => 'Test Meeting',
            'description' => 'Discuss testing',
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'color' => '#ff0000',
        ];

        // Send POST request to store schedule
        $response = $this->postJson('/api/schedules', $data);

        // Check response is successful
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Schedule created and email sent to homeowner!',
                     'schedule' => [
                         'title' => 'Test Meeting',
                     ],
                 ]);

        // Check that the schedule is actually in the database
        $this->assertDatabaseHas('schedules', [
            'title' => 'Test Meeting',
            'description' => 'Discuss testing',
        ]);

        // Check that the notification was sent to the homeowner
        Notification::assertSentTo(
            [$homeowner],
            ScheduleNotification::class
        );
    }
    /** @test */
public function it_returns_all_schedules()
{
    // Create some schedules
    Schedule::factory()->count(2)->create();

    $response = $this->getJson('/api/schedules');

    $response->assertStatus(200)
             ->assertJsonCount(2); // Checks if it returned 2 items
}
public function reschedule(Request $request, Schedule $schedule)
{
    $validated = $request->validate([
        'start_time' => 'required|date',
        'end_time'   => 'required|date|after:start_time',
    ]);

    $schedule->reschedule($validated['start_time'], $validated['end_time']);

    return response()->json([
        'message' => 'Schedule successfully rescheduled',
        'schedule' => $schedule
    ], 200);
}

public function cancel(Schedule $schedule)
{
    $schedule->cancel();

    return response()->json([
        'message' => 'Schedule successfully cancelled',
        'schedule' => $schedule
    ], 200);
}

}
