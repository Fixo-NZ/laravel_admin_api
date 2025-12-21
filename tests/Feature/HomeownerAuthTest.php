<?php

namespace Tests\Feature;

use App\Models\Homeowner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomeownerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_homeowner_can_register()
    {
        $response = $this->postJson('/api/homeowner/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '021234567',
            'city' => 'Auckland',
            'region' => 'Auckland',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'city',
                        'region',
                        'status',
                        'user_type',
                    ],
                    'token'
                ]
            ]);

        $this->assertDatabaseHas('homeowners', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    public function test_homeowner_can_login()
    {
        $homeowner = Homeowner::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/homeowner/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'user_type',
                    ],
                    'token'
                ]
            ]);
    }

    public function test_homeowner_cannot_login_with_invalid_credentials()
    {
        $homeowner = Homeowner::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/homeowner/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                ]
            ]);
    }

    public function test_homeowner_can_logout()
    {
        $homeowner = Homeowner::factory()->create();
        $token = $homeowner->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/homeowner/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);
    }

    public function test_homeowner_can_get_profile()
    {
        $homeowner = Homeowner::factory()->create();
        $token = $homeowner->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/homeowner/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'user_type',
                    ]
                ]
            ]);
    }
}
