<?php

namespace Database\Seeders;

use App\Models\Homeowner;
use Illuminate\Database\Seeder;

class HomeownerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 homeowners using the HomeownerFactory
        Homeowner::factory()
            ->count(10)
            ->create();
    }
}
