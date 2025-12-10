<?php

namespace Tests\Feature;

use App\Models\Tradie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TradieLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_tradie_can_login()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(), // Ensure email is verified
            'status' => 'active', // Ensure account is active
        ]);

        $response = $this->postJson('/api/tradie/login', [
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
                        'business_name',
                        'license_number',
                        'years_experience',
                        'hourly_rate',
                        'address',
                        'city',
                        'region',
                        'postal_code',
                        'service_radius',
                        'availability_status',
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
                        'user_type' => 'tradie',
                    ]
                ]
            ]);
    }

    public function test_tradie_cannot_login_with_invalid_credentials()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/tradie/login', [
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

    public function test_tradie_cannot_login_with_nonexistent_email()
    {
        $response = $this->postJson('/api/tradie/login', [
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

    public function test_tradie_cannot_login_with_inactive_account()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/tradie/login', [
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

    public function test_tradie_cannot_login_with_unverified_email()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null, // Email not verified
            'status' => 'active', // Ensure account is active so we test email verification specifically
        ]);

        $response = $this->postJson('/api/tradie/login', [
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

    public function test_tradie_login_requires_email()
    {
        $response = $this->postJson('/api/tradie/login', [
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

    public function test_tradie_login_requires_password()
    {
        $response = $this->postJson('/api/tradie/login', [
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

    public function test_tradie_login_requires_valid_email_format()
    {
        $response = $this->postJson('/api/tradie/login', [
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

    public function test_tradie_can_logout()
    {
        $tradie = Tradie::factory()->create();
        $token = $tradie->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tradie/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
    }

    public function test_tradie_logout_requires_authentication()
    {
        $response = $this->postJson('/api/tradie/logout');

        $response->assertStatus(401);
    }

    public function test_tradie_can_get_profile()
    {
        $tradie = Tradie::factory()->create();
        $token = $tradie->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tradie/me');

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
                        'business_name',
                        'license_number',
                        'insurance_details',
                        'years_experience',
                        'hourly_rate',
                        'address',
                        'city',
                        'region',
                        'postal_code',
                        'latitude',
                        'longitude',
                        'service_radius',
                        'availability_status',
                        'status',
                        'user_type',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'user_type' => 'tradie',
                    ]
                ]
            ]);
    }

    public function test_tradie_profile_requires_authentication()
    {
        $response = $this->getJson('/api/tradie/me');

        $response->assertStatus(401);
    }

    public function test_tradie_login_revokes_existing_tokens()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        // Create some existing tokens
        $tradie->createToken('old-token-1');
        $tradie->createToken('old-token-2');

        $this->assertEquals(2, $tradie->tokens()->count());

        $response = $this->postJson('/api/tradie/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        // Should only have the new token
        $this->assertEquals(1, $tradie->fresh()->tokens()->count());
    }
}