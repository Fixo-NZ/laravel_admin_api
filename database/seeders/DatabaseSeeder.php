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
<<<<<<< HEAD
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
=======
            'name' => 'Justin Joseph Sanchez',
            'email' => 'justin@gmail.com',
            'password' => Hash::make("admin"),
>>>>>>> 24172d873ef38a8fa72e08a82046ccf88c100ee2
        ]);

        User::factory(10)->create();

        Homeowner::factory(10)->create();
        Tradie::factory(10)->create();

        Tradie::factory()->create([
            'first_name' => 'John',
            'email' => 'john.example@email.com',
            'phone' => '09987654321',
            'password' => Hash::make("tradie123"),
            'status' => 'active'
        ]);
    }
}
