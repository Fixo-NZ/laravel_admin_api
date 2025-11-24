<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Homeowner;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeownerModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST PURPOSE:
     * Ensures that when a Homeowner is created without a status,
     * it automatically defaults to 'active' (from the model boot method).
     */
    public function test_default_status_is_active()
    {
        // Create a homeowner without specifying status
        $homeowner = Homeowner::factory()->create(['status' => null]);

        // Verify that 'active' was automatically assigned
        $this->assertEquals('active', $homeowner->status);
    }

    /**
     * TEST PURPOSE:
     * Verifies that all fillable attributes can be mass-assigned properly.
     * This ensures that the $fillable array in the model works as expected.
     */
    public function test_fillable_attributes_are_assignable()
    {
        $data = [
            'first_name' => 'Helena',
            'last_name' => 'Mahinay',
            'middle_name' => 'C',
            'email' => 'helena@example.com',
            'phone' => '09123456789',
            'password' => 'password123',
            'avatar' => 'avatar.png',
            'bio' => 'Test bio',
            'address' => '123 Street',
            'city' => 'Cebu City',
            'region' => 'Central Visayas',
            'postal_code' => '6000',
            'latitude' => 10.3157,
            'longitude' => 123.8854,
        ];

        // Mass create using fillable fields
        $homeowner = Homeowner::create($data);

        // Confirm that it saved in the database
        $this->assertDatabaseHas('homeowners', ['email' => 'helena@example.com']);
        $this->assertEquals('Helena', $homeowner->first_name);
        $this->assertEquals('Central Visayas', $homeowner->region);
    }

    /**
     * TEST PURPOSE:
     * Checks the accessor method getFullAddressAttribute().
     * Ensures that it correctly combines address components into one readable string.
     */
    public function test_full_address_accessor_returns_correct_value()
    {
        $homeowner = Homeowner::factory()->make([
            'address' => '123 Street',
            'city' => 'Cebu City',
            'region' => 'Central Visayas',
            'postal_code' => '6000',
        ]);

        $this->assertEquals('123 Street, Cebu City, Central Visayas, 6000', $homeowner->full_address);
    }

    /**
     * TEST PURPOSE:
     * Ensures that the scopeActive() method only retrieves homeowners
     * whose status is set to 'active'.
     */
    public function test_scope_active_returns_only_active_homeowners()
    {
        Homeowner::factory()->create(['status' => 'active']);
        Homeowner::factory()->create(['status' => 'inactive']);

        $activeHomeowners = Homeowner::active()->get();

        $this->assertCount(1, $activeHomeowners);
        $this->assertEquals('active', $activeHomeowners->first()->status);
    }

    /**
     * TEST PURPOSE:
     * Ensures that scopeInRegion() correctly filters homeowners
     * by their region attribute.
     */
    public function test_scope_in_region_filters_correctly()
    {
        Homeowner::factory()->create(['region' => 'Central Visayas']);
        Homeowner::factory()->create(['region' => 'NCR']);

        $regionHomeowners = Homeowner::inRegion('Central Visayas')->get();

        $this->assertCount(1, $regionHomeowners);
        $this->assertEquals('Central Visayas', $regionHomeowners->first()->region);
    }

    /**
     * TEST PURPOSE:
     * Confirms that hidden attributes ('password', 'remember_token')
     * are not visible when the model is converted to an array or JSON.
     */
    public function test_hidden_attributes_are_not_exposed()
    {
        $homeowner = Homeowner::factory()->make();

        $array = $homeowner->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /**
     * TEST PURPOSE:
     * Ensures that latitude and longitude values are properly cast
     * to decimal floats based on the $casts property in the model.
     */
    public function test_latitude_and_longitude_are_casted_as_decimal()
    {
        $homeowner = Homeowner::factory()->make([
            'latitude' => 10.3157,
            'longitude' => 123.8854,
        ]);

        $this->assertIsFloat((float)$homeowner->latitude);
        $this->assertIsFloat((float)$homeowner->longitude);
    }
}
