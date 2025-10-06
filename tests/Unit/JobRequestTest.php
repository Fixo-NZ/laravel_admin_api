<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\JobRequest;
use App\Models\Homeowner;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $job = new JobRequest([
            'homeowner_id' => 1,
            'service_type' => 'Electrical',
            'location' => 'Wellington',
            'budget' => 200.00,
            'description' => 'Install new light',
        ]);
        $this->assertEquals('Electrical', $job->service_type);
        $this->assertEquals('Wellington', $job->location);
        $this->assertEquals(200.00, $job->budget);
        $this->assertEquals('Install new light', $job->description);
    }

    public function test_homeowner_relationship()
    {
        $homeowner = Homeowner::factory()->create();
        $job = JobRequest::factory()->create(['homeowner_id' => $homeowner->id]);
        $this->assertInstanceOf(Homeowner::class, $job->homeowner);
        $this->assertEquals($homeowner->id, $job->homeowner->id);
    }
}
