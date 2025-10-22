<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_page_loads_successfully()
    {
        // Create a sample user
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Acting as the created user
        $response = $this->actingAs($user)->get('/admin-page');

        $response->assertStatus(200);
        $response->assertSee('Admin Accounts');
    }

    public function test_admin_table_displays_user_data()
    {
        $users = User::factory()->count(3)->create();

        /** @var \App\Models\User $first */
        $first = $users->first();

        // Fixed route
        $response = $this->actingAs($first)->get('/admin-page');

        foreach ($users as $u) {
            $response->assertSee($u->first_name);
            $response->assertSee($u->last_name);
        }
    }

    public function test_admin_status_column_displays()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['status' => 'active']);

        // Fixed route
        $response = $this->actingAs($user)->get('/admin-page');

        $response->assertSee('Active');
    }
}
