<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Homeowner;
use App\Models\User; // Admin user
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;


class HomeownerPageTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin', // assuming you have a 'role' column to distinguish admins
        ]);
    }

    public function test_homeowner_page_loads_successfully(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)
            ->get('/homeowner-page');

        $response->assertStatus(200);
    }

    public function test_homeowner_records_are_displayed(): void
    {
        $admin = $this->createAdminUser();

        $homeowners = Homeowner::factory()->count(3)->create();

        $response = $this->actingAs($admin)
            ->get('/homeowner-page');

        $response->assertStatus(200);

        foreach ($homeowners as $h) {
            $response->assertSee($h->first_name);
            $response->assertSee($h->last_name);
        }
    }

    public function test_homeowner_search_works(): void
    {
        $admin = $this->createAdminUser();

        $matching = Homeowner::factory()->create(['first_name' => 'Helena']);
        $nonMatching = Homeowner::factory()->create(['first_name' => 'Maria']);

        $response = $this->actingAs($admin)
            ->get('/homeowner-page?table_search=Helena');

        $response->assertStatus(200);
        $response->assertSee('Helena');
        $response->assertDontSee('Maria');
    }

    //sample testing for livewire filter component
    // public function test_homeowner_status_filter_works()
    // {
    //     // 1. Setup Data
    //     $admin = $this->createAdminUser(); // Use the helper method for consistency
    //     $active = Homeowner::factory()->create(['status' => 'active']);
    //     $inactive = Homeowner::factory()->create(['status' => 'inactive']);

    //     // 2. Simulate User Interaction via Livewire Test
    //     $response = Livewire::actingAs($admin)
    //         // Test the component instance (assuming 'HomeownerTable' is correct)
    //         ->test('HomeownerPage') 
    //         // FIX: Use the simpler 'filters.status' property name, which is common 
    //         // in packaged table components like Filament or PowerGrid.
    //         ->set('filters.status', 'active'); 

    //     // 3. Assertions
    //     $response->assertSee($active->first_name)
    //              ->assertDontSee($inactive->first_name);
    // }
}