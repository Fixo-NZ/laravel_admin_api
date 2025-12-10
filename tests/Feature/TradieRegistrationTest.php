<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Registered;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Tradie;
use Tests\TestCase;

class TradieRegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function tradie_can_register_with_valid_data()
    {
        Event::fake();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/tradie/register', $data);

        $response->assertStatus(201)
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
                    'token',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'john.doe@example.com',
                        'status' => 'active',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('tradies', [
            'email' => 'john.doe@example.com',
            'status' => 'active',
        ]);

        Event::assertDispatched(Registered::class);
    }

    #[Test]
    public function registration_fails_with_missing_required_fields()
    {
        $response = $this->postJson('/api/tradie/register', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                ],
            ]);
        
        // Check if validation errors exist at all
        $this->assertArrayHasKey('error', $response->json());
        $this->assertArrayHasKey('details', $response->json()['error']);
        
        // Now check for specific fields
        $errors = $response->json()['error']['details'];
        $this->assertArrayHasKey('first_name', $errors);
        $this->assertArrayHasKey('last_name', $errors);
        $this->assertArrayHasKey('middle_name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    #[Test]
    public function registration_fails_with_invalid_email()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'email' => 'invalid-email',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/tradie/register', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ]);
        
        // Check if validation errors exist at all
        $this->assertArrayHasKey('error', $response->json());
        $this->assertArrayHasKey('details', $response->json()['error']);
        
        // Now check for specific fields
        $errors = $response->json()['error']['details'];
        $this->assertArrayHasKey('email', $errors);
    }

    #[Test]
    public function registration_fails_with_duplicate_email()
    {
        Tradie::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'email' => 'existing@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/tradie/register', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ]);
        
        // Check if validation errors exist at all
        $this->assertArrayHasKey('error', $response->json());
        $this->assertArrayHasKey('details', $response->json()['error']);
        
        // Now check for specific fields
        $errors = $response->json()['error']['details'];
        $this->assertArrayHasKey('email', $errors);
    }

    #[Test]
    public function registration_fails_with_mismatched_password_confirmation()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        $response = $this->postJson('/api/tradie/register', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ]);
    }

    #[Test]
    public function registration_fails_with_short_password()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ];

        $response = $this->postJson('/api/tradie/register', $data);

        dump($response->json());

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ]);
    }

    #[Test]
    public function tradie_can_verify_email_with_valid_link()
    {
        $tradie = Tradie::factory()->create([
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $tradie->id,
                'hash' => sha1($tradie->email),
            ]
        );

        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully.',
            ]);

        $this->assertNotNull($tradie->fresh()->email_verified_at);
    }

    #[Test]
    public function email_verification_fails_with_invalid_user()
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => 99999,
                'hash' => sha1('nonexistent@example.com'),
            ]
        );

        $response = $this->getJson($url);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'This user does not exist.',
                ],
            ]);
    }

    #[Test]
    public function email_verification_fails_with_invalid_signature()
    {
        $tradie = Tradie::factory()->create([
            'email_verified_at' => null,
        ]);

        $url = route('verification.verify', [
            'id' => $tradie->id,
            'hash' => sha1($tradie->email),
        ]);

        $response = $this->getJson($url);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Invalid signature.',
            ]);
    }

    #[Test]
    public function email_verification_fails_with_invalid_hash()
    {
        $tradie = Tradie::factory()->create([
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $tradie->id,
                'hash' => sha1('wrong@example.com'),
            ]
        );

        $response = $this->getJson($url);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_VERIFICATION',
                    'message' => 'Verification details do not match.',
                ],
            ]);
    }

    #[Test]
    public function email_verification_returns_success_if_already_verified()
    {
        $tradie = Tradie::factory()->create([
            'email_verified_at' => now(),
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $tradie->id,
                'hash' => sha1($tradie->email),
            ]
        );

        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email already verified.',
            ]);
    }

    #[Test]
    public function tradie_can_resend_verification_email()
    {
        $tradie = Tradie::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/tradie/auth/resend-email-verification', [
            'email' => $tradie->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verification email resent successfully.',
            ]);
    }

    #[Test]
    public function resend_verification_fails_with_missing_email()
    {
        $response = $this->postJson('/api/tradie/auth/resend-email-verification', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                ],
            ]);

        // Check if validation errors exist at all
        $this->assertArrayHasKey('error', $response->json());
        $this->assertArrayHasKey('details', $response->json()['error']);
        
        // Now check for specific fields
        $errors = $response->json()['error']['details'];
        $this->assertArrayHasKey('email', $errors);
    }

    #[Test]
    public function resend_verification_fails_with_invalid_email_format()
    {
        $response = $this->postJson('/api/tradie/auth/resend-email-verification', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ]);
        // Check if validation errors exist at all
        $this->assertArrayHasKey('error', $response->json());
        $this->assertArrayHasKey('details', $response->json()['error']);
        
        // Now check for specific fields
        $errors = $response->json()['error']['details'];
        $this->assertArrayHasKey('email', $errors);
    }

    #[Test]
    public function resend_verification_fails_with_nonexistent_user()
    {
        $response = $this->postJson('/api/tradie/auth/resend-email-verification', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'This user does not exist.',
                ],
            ]);
    }

    #[Test]
    public function resend_verification_returns_success_if_already_verified()
    {
        $tradie = Tradie::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/tradie/auth/resend-email-verification', [
            'email' => $tradie->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email already verified.',
            ]);
    }

    #[Test]
    public function registration_hashes_password_correctly()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->postJson('/api/tradie/register', $data);

        $tradie = Tradie::where('email', 'john.doe@example.com')->first();

        $this->assertTrue(Hash::check('password123', $tradie->password));
    }
}
