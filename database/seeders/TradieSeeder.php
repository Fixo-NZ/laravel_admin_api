<?php

namespace Database\Seeders;

use App\Models\Tradie;

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
<<<<<<< HEAD
        Tradie::factory()
            ->count(30)
            ->create();
=======
        Tradie::factory(10)->create();
>>>>>>> origin/g8/registration_and_email_verification
    }
}
