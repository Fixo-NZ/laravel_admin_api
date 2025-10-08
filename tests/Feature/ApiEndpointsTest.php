<?php

namespace Tests\Feature;

use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Service as Job;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommend_tradies_endpoint_returns_404_for_missing_job()
    {
        $response = $this->getJson('/api/jobs/9999/recommend-tradies');
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    public function test_homeowner_auth_register_login_logout_and_me()
    {
        $payload = [
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '021234567',
        ];

        // Register
        $res = $this->postJson('/api/homeowner/register', $payload);
        $res->assertStatus(201);

        // Login
        $res = $this->postJson('/api/homeowner/login', [
            'email' => $payload['email'],
            'password' => $payload['password'],
        ]);
        $res->assertStatus(200);
        $token = $res->json('data.token');
        $this->assertNotEmpty($token);

        // Me
        $res = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/homeowner/me');
        $res->assertStatus(200);

        // Logout
        $res = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/homeowner/logout');
        $res->assertStatus(200);
    }

    public function test_tradie_auth_register_login_logout_and_me()
    {
        $payload = [
            'name' => 'Bob Builder',
            'email' => 'bob@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '029876543',
        ];

        // Register
        $res = $this->postJson('/api/tradie/register', $payload);
        $res->assertStatus(201);

        // Login
        $res = $this->postJson('/api/tradie/login', [
            'email' => $payload['email'],
            'password' => $payload['password'],
        ]);
        $res->assertStatus(200);
        $token = $res->json('data.token');
        $this->assertNotEmpty($token);

        // Me
        $res = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tradie/me');
        $res->assertStatus(200);

        // Logout
        $res = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/tradie/logout');
        $res->assertStatus(200);
    }

    public function test_protected_user_route_requires_auth()
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_recommend_tradies_returns_recommendations()
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

        if (method_exists($tradie, 'skills')) {
            $tradie->skills()->create(['skill_name' => $category->category_name]);
        }

        $res = $this->getJson('/api/jobs/' . $job->id . '/recommend-tradies');
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $this->assertNotEmpty($res->json('recommendations'));
    }
}
