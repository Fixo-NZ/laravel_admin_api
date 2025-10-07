<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Homeowner;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_list_of_services()
    {
        Service::factory()->count(3)->create();

        $resp = $this->getJson('/api/services');

        $resp->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function store_creates_a_service()
    {
        $homeowner = Homeowner::factory()->create();
        $category = Category::factory()->create();

        $payload = [
            'homeowner_id' => $homeowner->id,
            'job_categoryid' => $category->id,
            'job_description' => 'Fix sink',
            'location' => 'Auckland',
            'status' => 'Pending',
            'createdAt' => now()->toDateTimeString(),
            'updatedAt' => now()->toDateTimeString(),
            'rating' => 4,
        ];

        $resp = $this->postJson('/api/services', $payload);

        $resp->assertStatus(201)
            ->assertJsonFragment(['job_description' => 'Fix sink']);

        $this->assertDatabaseHas('services', ['job_description' => 'Fix sink']);
    }

    /** @test */
    public function show_returns_service()
    {
        $service = Service::factory()->create();

        $resp = $this->getJson('/api/services/' . $service->job_id);

        $resp->assertStatus(200)
            ->assertJsonFragment(['job_description' => $service->job_description]);
    }

    /** @test */
    public function update_modifies_service()
    {
        $service = Service::factory()->create(['job_description' => 'Old desc']);

        $payload = ['job_description' => 'New description', 'updatedAt' => now()->toDateTimeString()];

        $resp = $this->putJson('/api/services/' . $service->job_id, $payload);

        $resp->assertStatus(200)
            ->assertJsonFragment(['job_description' => 'New description']);

        $this->assertDatabaseHas('services', ['job_description' => 'New description']);
    }

    /** @test */
    public function destroy_deletes_service()
    {
        $service = Service::factory()->create();

        $resp = $this->deleteJson('/api/services/' . $service->job_id);

        $resp->assertStatus(204);

        $this->assertDatabaseMissing('services', ['job_id' => $service->job_id]);
    }
}
