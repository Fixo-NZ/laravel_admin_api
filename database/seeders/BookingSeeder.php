<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\BookingLog;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'first_name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Ensure there are some services
        $services = Service::all();
        if ($services->isEmpty()) {
            $serviceNames = ['Plumbing', 'Electrical', 'Carpentry', 'Painting', 'Cleaning'];
            foreach ($serviceNames as $name) {
                Service::create([
                    'name' => $name,
                    'description' => "$name service",
                    'category' => 'general',
                    'is_active' => true,
                ]);
            }
            $services = Service::all();
        }

        $homeowners = Homeowner::all();
        $tradies = Tradie::all();

        if ($homeowners->isEmpty() || $tradies->isEmpty()) {
            // Nothing to seed bookings for
            return;
        }

        // Create bookings for random pairs
        $count = 30;
        for ($i = 0; $i < $count; $i++) {
            $homeowner = $homeowners->random();
            $tradie = $tradies->random();
            $service = $services->random();

            // Randomize start between -10 and +30 days
            $start = Carbon::now()->addDays(rand(-10, 30))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
            $end = (clone $start)->addHours(1 + rand(0, 3));

            $statusOptions = ['pending', 'confirmed', 'completed', 'canceled'];
            $status = $statusOptions[array_rand($statusOptions)];

            $booking = Booking::create([
                'homeowner_id' => $homeowner->id,
                'tradie_id' => $tradie->id,
                'service_id' => $service->id,
                'booking_start' => $start->toDateTimeString(),
                'booking_end' => $end->toDateTimeString(),
                'status' => $status,
                'total_price' => rand(50, 500),
            ]);

            BookingLog::create([
                'booking_id' => $booking->id,
                'user_id' => $homeowner->id,
                'action' => 'created',
                'notes' => 'Seeded booking',
            ]);
        }
    }
}
