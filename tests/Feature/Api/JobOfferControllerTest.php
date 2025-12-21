<?php

namespace Tests\Feature\Api;

use App\Models\Homeowner;
use App\Models\HomeownerJobOffer;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobOfferControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Assuming Homeowner model represents users/homeowners
        $this->user = Homeowner::factory()->create();

        /** @var \Illuminate\Contracts\Auth\Authenticatable $authUser */
        $authUser = $this->user;

        $this->actingAs($authUser); // Authenticate via Sanctum
    }

    /** @test */
    public function it_can_list_job_offers()
    {
        // Create some job offers (for any user, as index lists all)
        HomeownerJobOffer::factory()->count(3)->create();

        $response = $this->getJson('/api/jobs/job-offers');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         '*' => [
                             'id',
                             'title',
                             'category' => ['id', 'name'],
                             'services' => ['*' => ['id', 'name']],
                             'photos' => ['*' => ['id', 'file_path']],
                             'homeowner' => ['id', 'first_name', 'last_name'],
                         ]
                     ]
                 ])
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_can_show_a_job_offer()
    {
        $jobOffer = HomeownerJobOffer::factory()->create(['homeowner_id' => $this->user->id]);

        $response = $this->getJson("/api/jobs/job-offers/{$jobOffer->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $jobOffer->id,
                         'title' => $jobOffer->title,
                     ]
                 ]);
    }

    /** @test */
    public function it_cannot_show_another_users_job_offer()
    {
        $otherUser = Homeowner::factory()->create(); // Fixed: Use Homeowner instead of User
        $jobOffer = HomeownerJobOffer::factory()->create(['homeowner_id' => $otherUser->id]);

        $response = $this->getJson("/api/jobs/job-offers/{$jobOffer->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_create_a_job_offer()
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        $data = [
            'service_category_id' => $category->id,
            'title' => 'Test Job',
            'description' => 'Test description',
            'job_type' => 'standard',
            'preferred_date' => '2023-12-01',
            'job_size' => 'small',
            'address' => '123 Test St',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'services' => [$service->id],
            'photos' => [], // No photos for simplicity
        ];

        $response = $this->postJson('/api/jobs/job-offers', $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Job offer created successfully.',
                 ]);

        $this->assertDatabaseHas('homeowner_job_offers', [
            'title' => 'Test Job',
            'homeowner_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_create_a_recurrent_job_offer() // Added: Test for recurrent jobs
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        $data = [
            'service_category_id' => $category->id,
            'title' => 'Recurrent Test Job',
            'description' => 'Test recurrent description',
            'job_type' => 'recurrent',
            'frequency' => 'weekly',
            'start_date' => '2023-12-01',
            'end_date' => '2024-12-01',
            'job_size' => 'medium',
            'address' => '456 Recurrent St',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'services' => [$service->id],
            'photos' => [],
        ];

        $response = $this->postJson('/api/jobs/job-offers', $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Job offer created successfully.',
                 ]);

        $this->assertDatabaseHas('homeowner_job_offers', [
            'title' => 'Recurrent Test Job',
            'job_type' => 'recurrent',
            'frequency' => 'weekly',
            'homeowner_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_validates_job_offer_creation()
    {
        $data = [
            'title' => '', // Invalid: required
            'job_type' => 'invalid_type', // Invalid: not in list
        ];

        $response = $this->postJson('/api/jobs/job-offers', $data);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed.',
                     'errors' => [
                         'title' => ['The title field is required.'],
                         'job_type' => ['The selected job type is invalid.'],
                     ]
                 ]);
    }

    /** @test */
    public function it_can_create_a_job_offer_with_photos()
    {
        Storage::fake('public'); // Fake storage for testing

        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        // Simulate base64 image
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

        $data = [
            'service_category_id' => $category->id,
            'title' => 'Test Job with Photo',
            'job_type' => 'standard',
            'preferred_date' => '2023-12-01',
            'job_size' => 'small',
            'address' => '123 Test St',
            'services' => [$service->id],
            'photos' => [$base64Image],
        ];

        $response = $this->postJson('/api/jobs/job-offers', $data);

        $response->assertStatus(201);

        // Check if photo was stored
        $jobOffer = HomeownerJobOffer::where('title', 'Test Job with Photo')->first();
        $this->assertCount(1, $jobOffer->photos);
        Storage::disk('public')->exists($jobOffer->photos->first()->file_path);
    }

    /** @test */
    public function it_can_update_a_job_offer()
    {
        $jobOffer = HomeownerJobOffer::factory()->create(['homeowner_id' => $this->user->id]);

        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/jobs/job-offers/{$jobOffer->id}", $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Job offer updated successfully.',
                 ]);

        $this->assertDatabaseHas('homeowner_job_offers', [
            'id' => $jobOffer->id,
            'title' => 'Updated Title',
        ]);
    }

    // ...
    public function it_cannot_update_another_users_job_offer()
    {
        $otherHomeowner = Homeowner::factory()->create();
        $category = ServiceCategory::factory()->create(); // Added
        $jobOffer = HomeownerJobOffer::factory()->create([
            'homeowner_id' => $otherHomeowner->id,
            'service_category_id' => $category->id,
        ]);

        $data = [
            'service_category_id' => $category->id,
            'title' => 'Hacked Title',
            'description' => $jobOffer->description,

        ];

        $response = $this->putJson("/api/jobs/job-offers/{$jobOffer->id}", $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_delete_a_job_offer()
    {
        $jobOffer = HomeownerJobOffer::factory()->create(['homeowner_id' => $this->user->id]);

        $response = $this->deleteJson("/api/jobs/job-offers/{$jobOffer->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Job offer deleted successfully.',
                 ]);

        $this->assertDatabaseMissing('homeowner_job_offers', ['id' => $jobOffer->id]);
    }

    /** @test */
    public function it_cannot_delete_another_users_job_offer()
    {
        $otherUser = Homeowner::factory()->create(); // Fixed: Use Homeowner
        $jobOffer = HomeownerJobOffer::factory()->create(['homeowner_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/jobs/job-offers/{$jobOffer->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function it_deletes_photos_when_deleting_job_offer()
    {
        Storage::fake('public');

        $jobOffer = HomeownerJobOffer::factory()->create(['homeowner_id' => $this->user->id]);
        // Assume JobPhoto factory or create manually
        $photo = $jobOffer->photos()->create([
            'file_path' => 'uploads/job_photos/test.png',
            'original_name' => 'test.png',
            'file_size' => 100,
        ]);

        Storage::disk('public')->put('uploads/job_photos/test.png', 'fake content');

        $response = $this->deleteJson("/api/jobs/job-offers/{$jobOffer->id}");

        $response->assertStatus(200);
        $this->assertFalse(Storage::disk('public')->exists('uploads/job_photos/test.png'));
        $this->assertDatabaseMissing('job_offer_photos', ['id' => $photo->id]); // Fixed: Use correct table name
    }
}
