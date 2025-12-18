<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Review;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_all_reviews()
    {
        Review::factory()->count(3)->create(['status' => 'approved']);

        $response = $this->getJson('/api/feedback/reviews');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'rating',
                        'date',
                        'comment',
                        'likes',
                        'isLiked',
                        'mediaPaths',
                        'contractorId',
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_submit_a_review_without_authentication()
    {
        $data = [
            'name' => 'John Doe',
            'rating' => 5,
            'comment' => 'Excellent service!',
            'mediaPaths' => [],
        ];

        $response = $this->postJson('/api/feedback/reviews', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'rating',
                    'date',
                    'comment',
                    'likes',
                    'isLiked',
                    'mediaPaths',
                    'contractorId',
                ]
            ]);

        $this->assertDatabaseHas('reviews', [
            'rating' => 5,
            'feedback' => 'Excellent service!',
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function it_validates_required_rating()
    {
        $data = [
            'name' => 'John Doe',
            'comment' => 'Great work',
        ];

        $response = $this->postJson('/api/feedback/reviews', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    /** @test */
    public function it_validates_rating_range()
    {
        $data = [
            'rating' => 6, // Invalid: max is 5
            'comment' => 'Test',
        ];

        $response = $this->postJson('/api/feedback/reviews', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    /** @test */
    public function it_can_submit_review_with_contractor_id()
    {
        $tradie = Tradie::factory()->create();

        $data = [
            'rating' => 4,
            'comment' => 'Good work',
            'contractorId' => $tradie->id,
        ];

        $response = $this->postJson('/api/feedback/reviews', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reviews', [
            'rating' => 4,
            'tradie_id' => $tradie->id,
        ]);
    }

    /** @test */
    public function it_can_delete_a_review()
    {
        $review = Review::factory()->create();

        $response = $this->deleteJson("/api/feedback/reviews/{$review->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_non_existent_review()
    {
        $response = $this->deleteJson('/api/feedback/reviews/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_toggle_like_on_review()
    {
        $review = Review::factory()->create(['helpful_count' => 0]);

        $response = $this->patchJson("/api/feedback/reviews/{$review->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'likes' => 1,
                    'isLiked' => true,
                ]
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'helpful_count' => 1,
        ]);
    }

    /** @test */
    public function it_can_toggle_unlike_on_review()
    {
        $review = Review::factory()->create(['helpful_count' => 1]);

        $response = $this->patchJson("/api/feedback/reviews/{$review->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'likes' => 0,
                    'isLiked' => false,
                ]
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'helpful_count' => 0,
        ]);
    }

    /** @test */
    public function it_returns_string_ids_in_response()
    {
        $review = Review::factory()->create();

        $response = $this->getJson('/api/feedback/reviews');

        $response->assertStatus(200);
        
        $data = $response->json('data.0');
        $this->assertIsString($data['id']);
        
        if ($data['contractorId'] !== null) {
            $this->assertIsString($data['contractorId']);
        }
    }

    /** @test */
    public function it_handles_anonymous_reviews()
    {
        $data = [
            'rating' => 5,
            'comment' => 'Anonymous feedback',
        ];

        $response = $this->postJson('/api/feedback/reviews', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reviews', [
            'rating' => 5,
            'feedback' => 'Anonymous feedback',
            'homeowner_id' => null,
            'tradie_id' => null,
        ]);
    }

    /** @test */
    public function it_returns_empty_array_for_null_media_paths()
    {
        $review = Review::factory()->create(['images' => null]);

        $response = $this->getJson('/api/feedback/reviews');

        $response->assertStatus(200);
        
        $data = $response->json('data.0');
        $this->assertIsArray($data['mediaPaths']);
        $this->assertEmpty($data['mediaPaths']);
    }
}
