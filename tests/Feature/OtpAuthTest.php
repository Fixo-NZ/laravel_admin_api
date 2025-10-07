<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Otp;
use App\Models\Tradie;
use Tests\TestCase;

class OtpAuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_otp_generation_and_validation_for_new_tradie()
    {
        $phoneNumber = '09123456789';

        // Generate OTP
        $response = $this->postJson('/api/tradie/request-otp', ['phone' => $phoneNumber]);
        $response->assertStatus(201);
        $this->assertArrayHasKey('otp_code', $response->json());
        $otpCode = $response->json('otp_code');

        // Validate OTP
        $validateResponse = $this->postJson('/api/tradie/verify-otp', [
            'phone' => $phoneNumber,
            'otp_code' => $otpCode,
        ]);
        $validateResponse->assertStatus(200);
        $validateResponse->assertJson([
            'status' => 'new_user',
            'message' => 'OTP verification successful, proceed to registration',
        ]);
    }

    public function test_otp_generation_and_validation_for_existing_tradie()
    {
        $phoneNumber = '09123456789';

        // Create a dummy tradie
        $tradie = Tradie::factory()->create(['phone' => $phoneNumber]);

        // Generate OTP
        $response = $this->postJson('/api/tradie/request-otp', ['phone' => $phoneNumber]);
        $response->assertStatus(201);
        $this->assertArrayHasKey('otp_code', $response->json());
        $otpCode = $response->json('otp_code');

        // Validate OTP
        $validateResponse = $this->postJson('/api/tradie/verify-otp', [
            'phone' => $phoneNumber,
            'otp_code' => $otpCode,
        ]);


        $validateResponse->assertStatus(200);
        $validateResponse->assertJson([
            'status' => 'existing_user',
            'message' => 'OTP verification successful, Tradie automatically logged in',
                'user' => $tradie->toArray(),
                'authorisation' => [
                    'type' => 'Bearer',
                ],
        ]);
        $validateResponse->assertJsonPath('authorisation.access_token', fn ($token) => is_string($token) && !empty($token));
    }

    public function test_otp_generation_invalid_phone_error()
    {
        // Test with invalid phone number
        $response = $this->postJson('/api/tradie/request-otp', ['phone' => 'invalid_phone']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_invalid_otp_error()
    {
        $phoneNumber = '09123456789';

        // Generate OTP
        $response = $this->postJson('/api/tradie/request-otp', ['phone' => $phoneNumber]);
        $response->assertStatus(201);

        // Use an invalid OTP code
        $invalidOtpCode = '000000';

        // Attempt to validate an invalid OTP
        $response = $this->postJson('/api/tradie/verify-otp', [
            'phone' => $phoneNumber,
            'otp_code' => $invalidOtpCode,
        ]);
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Invalid or expired OTP']);
    }

    public function test_expired_otp_error()
    {
        $phoneNumber = '09123456789';

        // Generate OTP
        $response = $this->postJson('/api/tradie/request-otp', ['phone' => $phoneNumber]);
        $response->assertStatus(201);
        $otpCode = $response->json('otp_code');

        // Simulate OTP expiration by updating the created_at timestamp
        Otp::where('phone', $phoneNumber)->update(['expires_at' => now()->subMinutes(6)]);

        // Attempt to validate the expired OTP
        $validateResponse = $this->postJson('/api/tradie/verify-otp', [
            'phone' => $phoneNumber,
            'otp_code' => $otpCode,
        ]);
        $validateResponse->assertStatus(400);
        $validateResponse->assertJson(['message' => 'Invalid or expired OTP']);
    }
}
