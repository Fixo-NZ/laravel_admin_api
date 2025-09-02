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
            'name' => 'helena ',
            'email' => 'helenarica.mahinay@lorma.edu',
            'password' => Hash::make("123"),
        ]);

        Homeowner::factory(10)->create();

        Tradie::factory(10)->create();
    }
}
