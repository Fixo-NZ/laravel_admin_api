<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tradie;
use Illuminate\Database\Seeder;

class TradieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tradie::factory()
            ->count(30)
            ->create();
    }
}
