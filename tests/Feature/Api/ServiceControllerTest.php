<?php

namespace Tests\Feature\Api;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_all_service_categories()
    {
        ServiceCategory::factory()->count(3)->create(['status' => 'active']);
        ServiceCategory::factory()->create(['status' => 'inactive']);
        ServiceCategory::factory()->create(['status' => 'suspended']); // Should not be included

        $response = $this->getJson('/api/jobs/categories');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'All Service Categories fetched successfully',
                 ])
                 ->assertJsonCount(4, 'data'); // 3 active + 1 inactive
    }

    /** @test */
    public function it_can_fetch_a_specific_category()
    {
        $category = ServiceCategory::factory()->create();

        $response = $this->getJson("/api/jobs/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Specific Category fetched successfully',
                     'data' => [
                         'id' => $category->id,
                         'name' => $category->name,
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_category()
    {
        $response = $this->getJson('/api/jobs/categories/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Service category not found',
                 ]);
    }

    /** @test */
    public function it_can_fetch_services_under_a_specific_category()
    {
        $category = ServiceCategory::factory()->create();
        Service::factory()->count(2)->create(['category_id' => $category->id]);
        Service::factory()->create(); // Different category

        $response = $this->getJson("/api/jobs/categories/{$category->id}/services");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Specific Service fetched successfully',
                     'data' => [
                         'category' => [
                             'id' => $category->id,
                             'name' => $category->name,
                         ],
                         'services' => [
                             ['id' => $category->services->first()->id],
                             ['id' => $category->services->last()->id],
                         ]
                     ]
                 ])
                 ->assertJsonCount(2, 'data.services');
    }

    /** @test */
    public function it_returns_404_for_nonexistent_category_in_services()
    {
        $response = $this->getJson('/api/jobs/categories/999/services');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Category not found',
                 ]);
    }

    /** @test */
    public function it_can_fetch_all_services()
    {
        Service::factory()->count(3)->create();

        $response = $this->getJson('/api/jobs/services');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'All services fetched successfully',
                 ])
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_fetch_a_specific_service()
    {
        $service = Service::factory()->create();

        $response = $this->getJson("/api/jobs/services/{$service->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Specific Service fetched successfully',
                     'data' => [
                         'id' => $service->id,
                         'name' => $service->name,
                         'category' => [
                             'id' => $service->category->id,
                             'name' => $service->category->name,
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_service()
    {
        $response = $this->getJson('/api/jobs/services/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Service not found',
                 ]);
    }
}
