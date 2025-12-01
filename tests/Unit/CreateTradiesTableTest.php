<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreateTradiesTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tradies_table_has_expected_columns()
    {
        $this->artisan('migrate');

        $columns = Schema::getColumnListing('tradies');

        $expected = [
            'id',
            'user_id',
            'business_name',
            'phone',
            'address',
            'created_at',
            'updated_at',
        ];

        foreach ($expected as $column) {
            $this->assertContains($column, $columns);
        }
    }
}
