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
        User::factory()->create([
            'first_name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

<<<<<<< HEAD
        // Homeowner::factory(10)->create();

        $this->call([
            TradieSeeder::class,
            HomeownerSeeder::class
=======
        User::factory(10)->create();
        Homeowner::factory(10)->create();
        Tradie::factory(10)->create();

        // Seed bookings after homeowners, tradies and services exist
        $this->call(BookingSeeder::class);

        Tradie::factory()->create([
            'first_name' => 'John',
            'email' => 'john.example@email.com',
            'phone' => '09987654321',
            'password' => Hash::make("tradie123"),
            'status' => 'active'
>>>>>>> origin/g8/registration_and_email_verification
        ]);
    }
}
