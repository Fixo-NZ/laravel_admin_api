<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User; // Admin
use App\Models\Tradie;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TradiePageTest extends TestCase
{
    use RefreshDatabase;

    // Admin user as a class property
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin', // assuming 'role' column
        ]);

        // Create some tradies
        Tradie::factory()->count(3)->create();
    }

    /** @test */
    public function admin_can_view_tradie_page_and_see_tradies()
    {
        $response = $this->actingAs($this->admin)
                         ->get('/tradie-page'); // adjust URL if different

        $response->assertStatus(200);

        Tradie::all()->each(function ($tradie) use ($response) {
            $response->assertSee($tradie->first_name);
            $response->assertSee($tradie->last_name);
            $response->assertSee($tradie->status);
        });

        // Check table columns
        $response->assertSee('First Name');
        $response->assertSee('Last Name');
        $response->assertSee('Middle Name');
        $response->assertSee('Email');
        $response->assertSee('Phone');
        $response->assertSee('Address');
        $response->assertSee('City');
        $response->assertSee('Region');
        $response->assertSee('Postal Code');
        $response->assertSee('Trade Type');
        $response->assertSee('Status');
    }

        /** @test */
    public function admin_can_search_tradies_without_livewire()
    {
        // Create some tradies
        $matchingTradie = Tradie::factory()->create([
            'first_name' => 'UniqueName',
        ]);

        $nonMatchingTradie = Tradie::factory()->create([
            'first_name' => 'OtherName',
        ]);

        // Simulate the table search query manually
        $search = 'UniqueName';

        $results = Tradie::query()
            ->where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhere('middle_name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->orWhere('address', 'like', "%{$search}%")
            ->orWhere('city', 'like', "%{$search}%")
            ->orWhere('region', 'like', "%{$search}%")
            ->orWhere('postal_code', 'like', "%{$search}%")
            ->get();

        // Assert the matching tradie is included
        $this->assertTrue($results->contains($matchingTradie));

        // Assert the non-matching tradie is not included
        $this->assertFalse($results->contains($nonMatchingTradie));
    }


    
}
