<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Job;
use App\Models\Review;
use App\Notifications\ReviewResponseNotification;
use Tests\TestCase;

class ReviewResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_tradie_can_respond_to_review(): void
    {
        Notification::fake();

        $homeowner = Homeowner::factory()->create();
        $tradie = Tradie::factory()->create();
        $job = Job::query()->create([
            'user_id' => $homeowner->id,
            'provider_id' => $tradie->id,
            'title' => 'Test Job',
            'description' => 'Test',
            'status' => 'completed',
            'price' => 100,
        ]);

        $review = Review::query()->create([
            'job_id' => $job->id,
            'homeowner_id' => $homeowner->id,
            'tradie_id' => $tradie->id,
            'rating' => 5,
            'feedback' => 'Great job!',
            'status' => 'approved',
        ]);

        $token = $tradie->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/reviews/{$review->id}/response", [
            'content' => 'Thank you for your feedback!',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                 ]);

        Notification::assertSentTo(
            [$homeowner],
            ReviewResponseNotification::class
        );
    }

    public function test_only_one_response_per_tradie_per_review(): void
    {
        $homeowner = Homeowner::factory()->create();
        $tradie = Tradie::factory()->create();
        $job = Job::query()->create([
            'user_id' => $homeowner->id,
            'provider_id' => $tradie->id,
            'title' => 'Test Job',
            'description' => 'Test',
            'status' => 'completed',
            'price' => 100,
        ]);

        $review = Review::query()->create([
            'job_id' => $job->id,
            'homeowner_id' => $homeowner->id,
            'tradie_id' => $tradie->id,
            'rating' => 5,
            'feedback' => 'Great job!',
            'status' => 'approved',
        ]);

        $token = $tradie->createToken('test-token')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/reviews/{$review->id}/response", [
            'content' => 'First response',
        ])->assertStatus(201);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/reviews/{$review->id}/response", [
            'content' => 'Second response',
        ])->assertStatus(409);
    }

    public function test_tradie_can_update_response(): void
    {
        $homeowner = Homeowner::factory()->create();
        $tradie = Tradie::factory()->create();
        $job = Job::query()->create([
            'user_id' => $homeowner->id,
            'provider_id' => $tradie->id,
            'title' => 'Test Job',
            'description' => 'Test',
            'status' => 'completed',
            'price' => 100,
        ]);

        $review = Review::query()->create([
            'job_id' => $job->id,
            'homeowner_id' => $homeowner->id,
            'tradie_id' => $tradie->id,
            'rating' => 5,
            'feedback' => 'Great job!',
            'status' => 'approved',
        ]);

        $token = $tradie->createToken('test-token')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/reviews/{$review->id}/response", [
            'content' => 'Initial',
        ])->assertStatus(201);

        $update = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/reviews/{$review->id}/response", [
            'content' => 'Updated content',
        ]);

        $update->assertStatus(200)
               ->assertJson([
                   'success' => true,
               ]);
    }
}
