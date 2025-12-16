<?php

namespace Tests\Feature;

use App\Models\Homeowner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomeownerLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_homeowner_can_login()
    {
        $homeowner = Homeowner::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'status' => 'active',
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
                        'first_name',
                        'last_name',
                        'middle_name',
                        'email',
                        'phone',
                        'address',
                        'city',
                        'region',
                        'postal_code',
                        'status',
                        'user_type',
                    ],
                    'token'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'john@example.com',
                        'user_type' => 'homeowner',
                    ]
                ]
            ]);
    }

    public function test_homeowner_cannot_login_with_invalid_credentials()
    {
        $homeowner = Homeowner::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'email_verified_at' => now(),
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
                    'message' => 'The provided credentials are incorrect.',
                ]
            ]);
    }

    public function test_homeowner_cannot_login_with_nonexistent_email()
    {
        $response = $this->postJson('/api/homeowner/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'The provided credentials are incorrect.',
                ]
            ]);
    }

    public function test_homeowner_cannot_login_with_inactive_account()
    {
        $homeowner = Homeowner::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/homeowner/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_INACTIVE',
                    'message' => 'Your account is not active. Please contact support.',
                ]
            ]);
    }

    public function test_homeowner_cannot_login_with_unverified_email()
    {
        $homeowner = Homeowner::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/homeowner/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'EMAIL_NOT_VERIFIED',
                    'message' => 'Please verify your email before logging in.',
                ]
            ]);
    }

    public function test_homeowner_login_requires_email()
    {
        $response = $this->postJson('/api/homeowner/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                ]
            ]);

        $errors = $response->json()['error']['details'];
        $this->assertArrayHasKey('email', $errors);
    }

    public function test_homeowner_login_requires_password()
    {
        $response = $this->postJson('/api/homeowner/login', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                ]
            ]);

        $errors = $response->json()['error']['details'];
        $this->assertArrayHasKey('password', $errors);
    }

    public function test_homeowner_login_requires_valid_email_format()
    {
        $response = $this->postJson('/api/homeowner/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                ]
            ]);

        $errors = $response->json()['error']['details'];
        $this->assertArrayHasKey('email', $errors);
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
                'message' => 'Logged out successfully',
            ]);
    }

    public function test_homeowner_logout_requires_authentication()
    {
        $response = $this->postJson('/api/homeowner/logout');

        $response->assertStatus(401);
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
                        'first_name',
                        'last_name',
                        'middle_name',
                        'email',
                        'phone',
                        'avatar',
                        'bio',
                        'address',
                        'city',
                        'region',
                        'postal_code',
                        'latitude',
                        'longitude',
                        'status',
                        'user_type',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'user_type' => 'homeowner',
                    ]
                ]
            ]);
    }

    public function test_homeowner_profile_requires_authentication()
    {
        $response = $this->getJson('/api/homeowner/me');

        $response->assertStatus(401);
    }

    public function test_homeowner_login_revokes_existing_tokens()
    {
        $homeowner = Homeowner::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        // Create some existing tokens
        $homeowner->createToken('old-token-1');
        $homeowner->createToken('old-token-2');

        $this->assertEquals(2, $homeowner->tokens()->count());

        $response = $this->postJson('/api/homeowner/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        // Should only have the new token
        $this->assertEquals(1, $homeowner->fresh()->tokens()->count());
    }
}