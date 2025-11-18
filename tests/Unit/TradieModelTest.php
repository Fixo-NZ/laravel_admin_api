<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Tradie;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TradieModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_expected_fillable_fields()
    {
        $tradie = new Tradie();

        $this->assertEquals([
            'first_name',
            'last_name',
            'middle_name',
            'email',
            'phone',
            'password',
            'avatar',
            'bio',
            'address',
            'city',
            'region',
            'postal_code',
            'latitude',
            'longitude',
            'business_name',
            'license_number',
            'insurance_details',
            'years_experience',
            'hourly_rate',
            'availability_status',
            'service_radius',
            'status',
        ], $tradie->getFillable());
    }

    /** @test */
    public function it_can_be_created_via_factory()
    {
        $tradie = Tradie::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'email' => 'john@example.com',
            'phone' => '09123456789',
            'address' => '123 Street',
            'city' => 'CityName',
            'region' => 'RegionX',
            'postal_code' => '1000',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('tradies', [
            'id' => $tradie->id,
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function it_returns_full_name_fields_for_page_columns()
    {
        $tradie = Tradie::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'middle_name' => 'B',
        ]);

        $this->assertEquals('Alice', $tradie->first_name);
        $this->assertEquals('Smith', $tradie->last_name);
        $this->assertEquals('B', $tradie->middle_name);
    }

    /** @test */
    public function status_field_can_be_active_inactive_or_suspended()
    {
        $active = Tradie::factory()->create(['status' => 'active']);
        $inactive = Tradie::factory()->create(['status' => 'inactive']);
        $suspended = Tradie::factory()->create(['status' => 'suspended']);

        $this->assertEquals('active', $active->status);
        $this->assertEquals('inactive', $inactive->status);
        $this->assertEquals('suspended', $suspended->status);
    }

    /** @test */
    public function search_query_filters_tradies_by_multiple_fields()
    {
        Tradie::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'middle_name' => 'C',
            'email' => 'bob@example.com',
            'phone' => '09112223344',
            'address' => 'Street 1',
            'city' => 'CityA',
            'region' => 'Region1',
            'postal_code' => '1111',
        ]);

        request()->merge(['table_search' => 'Bob']);

        $results = Tradie::query()
            ->when(request('table_search'), function ($query, $search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('region', 'like', "%{$search}%")
                      ->orWhere('postal_code', 'like', "%{$search}%");
            })
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Bob', $results->first()->first_name);
    }
}
