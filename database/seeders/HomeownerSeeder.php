<?php

namespace Database\Seeders;

use App\Models\Homeowner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomeownerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Homeowner::factory()
            ->count(20)
            ->create();
    }
}
