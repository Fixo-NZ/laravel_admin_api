<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\TradieRecommendationController;
use App\Models\Job;
use App\Models\Tradie;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TradieRecommendationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    /** @test */
    public function recommend_returns_404_if_job_not_found()
    {
        $controller = new TradieRecommendationController();
        $response = $controller->recommend(9999);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(false, $response->getData()->success);
    }

    /** @test */
    public function recommend_returns_empty_if_no_tradies_found()
    {
        $category = Category::factory()->create();
        $job = Job::factory()->create(['category_id' => $category->id]);
        $controller = new TradieRecommendationController();
        $response = $controller->recommend($job->id);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData()->data);
    }

    /** @test */
    public function recommend_returns_tradies_with_expected_fields()
    {
        $category = Category::factory()->create(['category_name' => 'Plumbing']);
        $job = Job::factory()->create(['category_id' => $category->id]);
        $tradie = Tradie::factory()->create([
            'availability_status' => 'available',
            'status' => 'active',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'business_name' => 'JD Plumbing',
            'city' => 'Auckland',
            'region' => 'Auckland',
            'years_experience' => 5,
            'rating' => 4.8,
        ]);
        // Attach skill/category relation if needed
        if (method_exists($tradie, 'skills')) {
            $tradie->skills()->create(['skill_name' => $category->category_name]);
        }

        $controller = new TradieRecommendationController();
        $response = $controller->recommend($job->id);
        $this->assertEquals(200, $response->getStatusCode());
        $respData = $response->getData();
        $data = property_exists($respData, 'recommendations') ? $respData->recommendations : (property_exists($respData, 'data') ? $respData->data : []);
        $this->assertNotEmpty($data);
        $tradieArr = (array)$data[0];
        $this->assertArrayHasKey('id', $tradieArr);
        $this->assertArrayHasKey('name', $tradieArr);
        $this->assertArrayHasKey('occupation', $tradieArr);
        $this->assertArrayHasKey('rating', $tradieArr);
        $this->assertArrayHasKey('service_area', $tradieArr);
        $this->assertArrayHasKey('years_experience', $tradieArr);
    }
}
