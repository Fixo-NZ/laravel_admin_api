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

        User::factory()->create([
            'name' => 'Elijah',
            'email' => 'elijahgarrence.delarosa@lorma.edu',
            'password' => Hash::make("lorma3EG"),
            'status' => 'active'
        ]);

        User::factory(3)->create();

        Homeowner::factory(10)->create();

        Tradie::factory(10)->create();

        Tradie::factory()->create([
            'first_name' => 'John',
            'email' => 'john.example@email.com',
            'phone' => '09987654321',
            'password' => Hash::make("tradie123"),
            'status' => 'active'
        ]);

        Tradie::factory()->create([
            'first_name' => 'Elijah',
            'email' => 'elijahgarrence.delarosa@lorma.edu',
            'phone' => '09123456789',
            'password' => Hash::make("tradie456"),
            'status' => 'active'
        ]);
    }
}
