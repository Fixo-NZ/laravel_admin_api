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
            'first_name' => 'Eizler ',
            'last_name' => 'Martin',   
            'middle_name' => 'Est',
            'email' => 'eizlerdylan.martin@lorma.edu',
            'password' => Hash::make("admin"),
            'role' => 'admin',
            'status' => 'active',
        ]);

        Homeowner::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'homeowner1@gmail.com',
            'password' => Hash::make("password"),
        ]);

        User::factory(10)->create();
        // Seed other users
        Homeowner::factory(20)->create();
        Tradie::factory(10)->create();

        $this->call([
            ServiceSeeder::class,
            HomeownerJobOfferSeeder::class,
        ]);
    }
}
