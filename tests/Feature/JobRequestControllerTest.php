<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\JobRequest;
use App\Models\Homeowner;

class JobRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_job_request()
    {
        $homeowner = Homeowner::factory()->create();
        $payload = [
            'homeowner_id' => $homeowner->id,
            'service_type' => 'Plumbing',
            'location' => 'Auckland',
            'budget' => 150.00,
            'description' => 'Fix leaking tap',
        ];

        $response = $this->postJson('/api/job-requests', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Job request created successfully!'
            ]);
        $this->assertDatabaseHas('job_requests', [
            'homeowner_id' => $homeowner->id,
            'service_type' => 'Plumbing',
            'location' => 'Auckland',
            'budget' => 150.00,
            'description' => 'Fix leaking tap',
        ]);
    }

    public function test_store_validation_error()
    {
        $response = $this->postJson('/api/job-requests', []);
        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    public function test_index_lists_job_requests()
    {
        $homeowner = Homeowner::factory()->create();
        JobRequest::factory()->count(2)->create(['homeowner_id' => $homeowner->id]);

        $response = $this->getJson('/api/job-requests');
        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_show_returns_job_request()
    {
        $homeowner = Homeowner::factory()->create();
        $job = JobRequest::factory()->create(['homeowner_id' => $homeowner->id]);

        $response = $this->getJson('/api/job-requests/' . $job->id);
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $job->id,
                'homeowner_id' => $homeowner->id
            ]);
    }
}
