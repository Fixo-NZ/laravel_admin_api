<?php

namespace Database\Seeders;

use App\Models\Homeowner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HomeownerSeeder extends Seeder
{
    public function run(): void
    {
        $homeowners = [
            [
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+64 21 123 4567',
                'address' => '123 Main Street',
                'city' => 'Auckland',
                'region' => 'Auckland',
                'postal_code' => '1010',
                'latitude' => -36.8485,
                'longitude' => 174.7633,
                'status' => 'active',
            ],
            [
                'first_name' => 'Sarah',
                'middle_name' => 'Elizabeth',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '+64 21 234 5678',
                'password' => Hash::make('password123'),
                'address' => '456 Queen Street',
                'city' => 'Wellington',
                'region' => 'Wellington',
                'postal_code' => '6011',
                'latitude' => -41.2865,
                'longitude' => 174.7762,
                'status' => 'active',
            ],
            [
                'first_name' => 'Mike',
                'middle_name' => 'James',
                'last_name' => 'Williams',
                'email' => 'mike.williams@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+64 21 345 6789',
                'address' => '789 High Street',
                'city' => 'Christchurch',
                'region' => 'Canterbury',
                'postal_code' => '8011',
                'latitude' => -43.5321,
                'longitude' => 172.6362,
                'status' => 'active',
            ],
            [
                'first_name' => 'Emma',
                'middle_name' => 'Rose',
                'last_name' => 'Brown',
                'email' => 'emma.brown@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+64 21 456 7890',
                'address' => '321 Victoria Street',
                'city' => 'Hamilton',
                'region' => 'Waikato',
                'postal_code' => '3204',
                'latitude' => -37.7870,
                'longitude' => 175.2793,
                'status' => 'active',
            ],
            [
                'first_name' => 'David',
                'middle_name' => 'Robert',
                'last_name' => 'Davis',
                'email' => 'david.davis@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+64 21 567 8901',
                'address' => '654 King Street',
                'city' => 'Dunedin',
                'region' => 'Otago',
                'postal_code' => '9016',
                'latitude' => -45.8788,
                'longitude' => 170.5028,
                'status' => 'active',
            ],
        ];

        foreach ($homeowners as $homeowner) {
            Homeowner::updateOrCreate(
                ['email' => $homeowner['email']],
                $homeowner
            );
        }

        $this->command->info('✅ Created ' . count($homeowners) . ' homeowners');
        $this->command->info('📧 Test login: john.smith@example.com / password123');
    }
}
