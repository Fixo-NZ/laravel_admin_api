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
            'first_name' => 'Helena ',
            'last_name' => 'Mahinay',   
            'middle_name' => 'Mahinay',    // single 'name' column
            'email' => 'helenarica.mahinay@lorma.edu',
            'password' => Hash::make("123"),
            'role' => 'admin',                // mark as admin
            'status' => 'active',             // mark as active
        ]);
       // ✅ Create Homeowners and Tradies first
        $homeowners = Homeowner::factory(10)->create();
        $tradies = Tradie::factory(10)->create();

        // ✅ Then run ScheduleSeeder (it can now safely reference homeowner_id)
        $this->call([
            ScheduleSeeder::class,
        ]);

        // Optionally create more users
        User::factory(10)->create();
    }
}
