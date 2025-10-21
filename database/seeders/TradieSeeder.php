<?php

namespace Database\Seeders;

use App\Models\Tradie;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TradieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tradie::factory(10)->create();
    }
}
