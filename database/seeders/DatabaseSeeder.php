<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Homeowner;
use App\Models\Tradie;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed admin user
        User::factory()->create([
            'first_name' => 'Chris Laurence',
            'last_name' => 'Marzan',
            'middle_name' => 'Mostoles',    // single 'name' column
            'email' => 'chrislaurence.marza@lorma.edu',
            'password' => Hash::make("laurence26"),
            'role' => 'admin',                // mark as admin
            'status' => 'active',             // mark as active
        ]);

        // Homeowner::factory(10)->create();

        $this->call([
            TradieSeeder::class,
            HomeownerSeeder::class
        ]);
    }
}
