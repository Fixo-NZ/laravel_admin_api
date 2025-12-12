<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Models\Homeowner;
use Laravel\Sanctum\Sanctum;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rate_limiting_triggers_429()
    {
        // Temporarily reduce the api limiter for the test to 2 per minute.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(2)->by(optional($request->user())->id ?: $request->ip());
        });

        $homeowner = Homeowner::factory()->create();
        Sanctum::actingAs($homeowner);

        // First two requests should pass
        $this->getJson('/api/bookings')->assertStatus(200);
        $this->getJson('/api/bookings')->assertStatus(200);

        // Third request should be throttled
        $this->getJson('/api/bookings')->assertStatus(429);
    }
}
