<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Review;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_review()
    {
        $review = Review::factory()->create([
            'rating' => 5,
            'feedback' => 'Excellent service!',
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'feedback' => 'Excellent service!',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_homeowner()
    {
        $homeowner = Homeowner::factory()->create();
        $review = Review::factory()->create(['homeowner_id' => $homeowner->id]);

        $this->assertInstanceOf(Homeowner::class, $review->homeowner);
        $this->assertEquals($homeowner->id, $review->homeowner->id);
    }

    /** @test */
    public function it_belongs_to_a_tradie()
    {
        $tradie = Tradie::factory()->create();
        $review = Review::factory()->create(['tradie_id' => $tradie->id]);

        $this->assertInstanceOf(Tradie::class, $review->tradie);
        $this->assertEquals($tradie->id, $review->tradie->id);
    }

    /** @test */
    public function it_can_have_nullable_job_id()
    {
        $review = Review::factory()->create(['job_id' => null]);

        $this->assertNull($review->job_id);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'job_id' => null,
        ]);
    }

    /** @test */
    public function it_can_have_nullable_homeowner_id()
    {
        $review = Review::factory()->create(['homeowner_id' => null]);

        $this->assertNull($review->homeowner_id);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'homeowner_id' => null,
        ]);
    }

    /** @test */
    public function it_can_have_nullable_tradie_id()
    {
        $review = Review::factory()->create(['tradie_id' => null]);

        $this->assertNull($review->tradie_id);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'tradie_id' => null,
        ]);
    }

    /** @test */
    public function it_casts_images_to_array()
    {
        $review = Review::factory()->create([
            'images' => ['image1.jpg', 'image2.jpg'],
        ]);

        $this->assertIsArray($review->images);
        $this->assertCount(2, $review->images);
    }

    /** @test */
    public function it_scopes_approved_reviews()
    {
        Review::factory()->create(['status' => 'approved']);
        Review::factory()->create(['status' => 'pending']);
        Review::factory()->create(['status' => 'approved']);

        $approvedReviews = Review::approved()->get();

        $this->assertCount(2, $approvedReviews);
    }

    /** @test */
    public function it_calculates_tradie_average_rating()
    {
        $tradie = Tradie::factory()->create();
        
        Review::factory()->create(['tradie_id' => $tradie->id, 'rating' => 5, 'status' => 'approved']);
        Review::factory()->create(['tradie_id' => $tradie->id, 'rating' => 4, 'status' => 'approved']);
        Review::factory()->create(['tradie_id' => $tradie->id, 'rating' => 3, 'status' => 'approved']);

        $average = Review::getTradieAverageRating($tradie->id);

        $this->assertEquals(4.0, $average);
    }

    /** @test */
    public function it_counts_tradie_reviews()
    {
        $tradie = Tradie::factory()->create();
        
        Review::factory()->count(3)->create([
            'tradie_id' => $tradie->id,
            'status' => 'approved',
        ]);

        $count = Review::getTradieReviewCount($tradie->id);

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_gets_rating_breakdown()
    {
        $tradie = Tradie::factory()->create();
        
        Review::factory()->count(2)->create(['tradie_id' => $tradie->id, 'rating' => 5, 'status' => 'approved']);
        Review::factory()->count(3)->create(['tradie_id' => $tradie->id, 'rating' => 4, 'status' => 'approved']);
        Review::factory()->create(['tradie_id' => $tradie->id, 'rating' => 3, 'status' => 'approved']);

        $breakdown = Review::getTradieRatingBreakdown($tradie->id);

        $this->assertEquals(2, $breakdown[5]);
        $this->assertEquals(3, $breakdown[4]);
        $this->assertEquals(1, $breakdown[3]);
    }
}
