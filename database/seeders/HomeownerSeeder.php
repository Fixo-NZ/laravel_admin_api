<?php

namespace Database\Seeders;

use App\Models\Homeowner;
use Illuminate\Database\Seeder;

class HomeownerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Homeowner::create([
            'first_name'  => 'Juan',
            'last_name'   => 'Dela Cruz',
            'middle_name' => 'Reyes',
            'email'       => 'juan@example.com',
            'phone'       => '09171234567',
            'password'    => Hash::make('password'),
            'address'     => '123 Manila Street',
            'city'        => 'Manila',
            'region'      => 'NCR',
            'postal_code' => '1000',
            'latitude'    => null,
            'longitude'   => null,
            'status'      => 'active',
        ]);
    }
}
