<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Tradie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SendOtp;
use App\Models\Otp;
use Mockery;
use Tests\TestCase;

class TradieResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $otpServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the OTP service
        $this->otpServiceMock = Mockery::mock('App\Services\OtpService');
        $this->app->instance('App\Services\OtpService', $this->otpServiceMock);

        Notification::fake();
    }

    #[Test]
    public function it_can_request_password_reset_with_valid_email_for_tradie()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ]);

        $this->otpServiceMock
            ->shouldReceive('generateOtp')
            ->with($tradie->phone)
            ->once()
            ->andReturn('123456');

        $response = $this->postJson('/api/tradie/reset-password-request', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'OTP sent successfully'
            ]);

        Notification::assertSentTo($tradie, SendOtp::class);
    }

    #[Test]
    public function it_fails_password_reset_request_with_invalid_email_for_tradie()
    {
        $response = $this->postJson('/api/tradie/reset-password-request', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given email is invalid.'
                ]
            ]);
    }

    #[Test]
    public function it_fails_password_reset_request_with_missing_email_for_tradie()
    {
        $response = $this->postJson('/api/tradie/reset-password-request', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ]);
    }

    #[Test]
    public function it_fails_password_reset_request_when_tradie_not_found()
    {
        $response = $this->postJson('/api/tradie/reset-password-request', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'The given email does not exist as a user.'
                ]
            ]);
    }

    #[Test]
    public function it_fails_password_reset_request_when_otp_generation_fails_for_tradie()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ]);

        $this->otpServiceMock
            ->shouldReceive('generateOtp')
            ->with($tradie->phone)
            ->once()
            ->andReturn(null);

        $response = $this->postJson('/api/tradie/reset-password-request', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'OTP_ERROR',
                    'message' => 'Failed to generate OTP. Please try again.'
                ]
            ]);
    }

    #[Test]
    public function it_can_verify_otp_with_valid_credentials_for_tradie()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ]);

        $this->otpServiceMock
            ->shouldReceive('verifyOtp')
            ->with($tradie->phone, '123456')
            ->once()
            ->andReturn(true);

        $response = $this->postJson('/api/tradie/verify-otp', [
            'email' => 'test@example.com',
            'otp_code' => '123456'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'OTP successfully verified.'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'password_reset_token',
                    'expires_at'
                ]
            ]);
    }

    #[Test]
    public function it_fails_otp_verification_with_invalid_otp_for_tradie()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ]);

        $this->otpServiceMock
            ->shouldReceive('verifyOtp')
            ->with($tradie->phone, '999999')
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/api/tradie/verify-otp', [
            'email' => 'test@example.com',
            'otp_code' => '999999'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'OTP_VERIFICATION_ERROR',
                    'message' => 'Failed to verify OTP. Please try again.'
                ]
            ]);
    }

    #[Test]
    public function it_fails_otp_verification_with_invalid_email_for_tradie()
    {
        $response = $this->postJson('/api/tradie/verify-otp', [
            'email' => 'invalid-email',
            'otp_code' => '123456'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ]);
    }

    #[Test]
    public function it_fails_otp_verification_with_invalid_otp_format_for_tradie()
    {
        $response = $this->postJson('/api/tradie/verify-otp', [
            'email' => 'test@example.com',
            'otp_code' => '12345' // Only 5 digits
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ]);
    }

    public function it_fails_otp_verification_when_user_not_found_for_tradie()
    {
        $response = $this->postJson('/api/tradie/verify-otp', [
            'email' => 'nonexistent@example.com',
            'otp_code' => '123456'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'TRADIE_NOT_FOUND'
                ]
            ]);
    }

    public function it_deletes_existing_password_reset_tokens_on_otp_verification_for_tradie()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ]);

        // Create existing password reset tokens
        $tradie->createToken('password-reset-token');
        $tradie->createToken('password-reset-token');

        $this->assertEquals(2, $tradie->tokens()->where('name', 'password-reset-token')->count());

        $this->otpServiceMock
            ->shouldReceive('verifyOtp')
            ->with($tradie->phone, '123456')
            ->once()
            ->andReturn(true);

        $response = $this->postJson('/api/tradie/verify-otp', [
            'email' => 'test@example.com',
            'otp_code' => '123456'
        ]);

        $response->assertStatus(200);

        // Should only have the newly created token
        $this->assertEquals(1, $tradie->fresh()->tokens()->where('name', 'password-reset-token')->count());
    }

    #[Test]
    public function it_can_reset_password_with_valid_token_for_tradie()
    {
        $tradie = Tradie::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword')
        ]);

        $token = $tradie->createToken('password-reset-token', ['reset-password']);

        $response = $this->putJson('/api/tradie/reset-password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ], [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset successfully.'
            ]);

        // Verify password was changed
        $this->assertTrue(Hash::check('newpassword123', $tradie->fresh()->password));
    }

    #[Test]
    public function it_fails_password_reset_with_short_password_for_tradie()
    {
        $tradie = Tradie::factory()->create();
        $token = $tradie->createToken('password-reset-token', ['reset-password']);

        $response = $this->putJson('/api/tradie/reset-password', [
            'new_password' => 'short',
            'new_password_confirmation' => 'short'
        ], [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ]);
    }

    #[Test]
    public function it_fails_password_reset_with_mismatched_confirmation_for_tradie()
    {
        $tradie = Tradie::factory()->create();
        $token = $tradie->createToken('password-reset-token', ['reset-password']);

        $response = $this->putJson('/api/tradie/reset-password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword'
        ], [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ]);
    }

    #[Test]
    public function it_fails_password_reset_without_authentication_for_tradie()
    {
        $response = $this->putJson('/api/tradie/reset-password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_revokes_all_tokens_after_password_reset_for_tradie()
    {
        $tradie = Tradie::factory()->create();

        // Create multiple tokens
        $resetToken = $tradie->createToken('password-reset-token', ['reset-password']);
        $tradie->createToken('auth-token');
        $tradie->createToken('another-auth-token');

        $this->assertEquals(3, $tradie->tokens()->count());

        $response = $this->putJson('/api/tradie/reset-password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ], [
            'Authorization' => 'Bearer ' . $resetToken->plainTextToken
        ]);

        $response->assertStatus(200);

        // All tokens should be revoked
        $this->assertEquals(0, $tradie->fresh()->tokens()->count());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}