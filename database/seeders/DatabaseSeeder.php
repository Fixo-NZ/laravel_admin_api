<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Homeowner;
use App\Models\Payment;
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
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        Payment::factory(20)->create();
        User::factory(10)->create();
        Homeowner::factory(10)->create();
        Tradie::factory(10)->create();
        // Seed bookings after homeowners, tradies and services exist
        $this->call(BookingSeeder::class);
    }
}
