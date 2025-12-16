<?php

namespace Database\Seeders;

use App\Models\Homeowner;
<<<<<<< HEAD
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
=======
>>>>>>> origin/g8/registration_and_email_verification
use Illuminate\Database\Seeder;

class HomeownerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        Homeowner::factory()
            ->count(20)
=======
        // Create 10 homeowners using the HomeownerFactory
        Homeowner::factory()
            ->count(10)
>>>>>>> origin/g8/registration_and_email_verification
            ->create();
    }
}
