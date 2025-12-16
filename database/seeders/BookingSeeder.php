<?php

namespace Database\Seeders;

use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\BookingLog;
use Illuminate\Database\Seeder;
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

        // Only run in local or testing environment
        if (!app()->environment(['local', 'testing'])) {
            $this->command->warn('Skipping BookingSeeder in non-local environment.');
            return;
        }

        $homeowners = Homeowner::all();
        $tradies = Tradie::all();
        $services = Service::all();

        if ($homeowners->isEmpty()) {
            $this->command->error('❌ No homeowners found. Please run HomeownerSeeder first.');
            return;
        }

        if ($tradies->isEmpty()) {
            $this->command->error('❌ No tradies found. Please run TradieSeeder first.');
            return;
        }

        if ($services->isEmpty()) {
            $this->command->error('❌ No services found. Please run ServiceSeeder first.');
            return;
        }

        $created = 0;

        foreach ($homeowners as $homeowner) {
            $homeownerServices = $services->where('homeowner_id', $homeowner->id);

            if ($homeownerServices->isEmpty()) continue;

            // Create 2–4 bookings per homeowner
            $bookingCount = rand(2, 4);

            for ($i = 0; $i < $bookingCount; $i++) {
                $service = $homeownerServices->random();
                $tradie = $tradies->random();

                $daysOffset = rand(-10, 30); // Past 10 days to future 30 days
                $start = Carbon::now()->addDays($daysOffset)
                    ->addHours(rand(9, 17))->minute(0)->second(0);
                $end = (clone $start)->addHours(rand(1, 4));

                // Status: past = completed/canceled, future = pending/confirmed
                $status = $start->isPast()
                    ? (rand(0,1) === 0 ? 'completed' : 'canceled')
                    : ['pending', 'pending', 'confirmed'][array_rand(['pending', 'pending', 'confirmed'])];

                $booking = Booking::create([
                    'homeowner_id' => $homeowner->id,
                    'tradie_id' => $tradie->id,
                    'service_id' => $service->id,
                    'booking_start' => $start,
                    'booking_end' => $end,
                    'status' => $status,
                    'total_price' => rand(100, 800),
                ]);

                BookingLog::create([
                    'booking_id' => $booking->id,
                    'user_id' => $homeowner->id,
                    'action' => 'created',
                    'notes' => 'Booking created via seeder',
                ]);

                $created++;
            }
        }

        $this->command->info("✅ Created {$created} bookings");
    }

    /**
     * Seed sample bookings for a specific homeowner.
     * Call this when a new homeowner is created.
     */
    public static function seedForHomeowner(Homeowner $homeowner)
    {
        if (!app()->environment(['local', 'testing'])) return;

        $tradies = Tradie::all();
        $services = Service::where('homeowner_id', $homeowner->id)->get();
        if ($tradies->isEmpty() || $services->isEmpty()) return;

        $statuses = ['pending', 'confirmed', 'completed', 'canceled'];
        $bookingCount = rand(2, 4);

        for ($i = 0; $i < $bookingCount; $i++) {
            $service = $services->random();
            $tradie = $tradies->random();

            $daysOffset = rand(-10, 30);
            $start = Carbon::now()->addDays($daysOffset)
                ->addHours(rand(9, 17))->minute(0)->second(0);
            $end = (clone $start)->addHours(rand(1, 4));

            $status = $start->isPast()
                ? (rand(0,1) === 0 ? 'completed' : 'canceled')
                : ['pending','pending','confirmed'][array_rand(['pending','pending','confirmed'])];

            $booking = Booking::create([
                'homeowner_id' => $homeowner->id,
                'tradie_id' => $tradie->id,
                'service_id' => $service->id,
                'booking_start' => $start,
                'booking_end' => $end,
                'status' => $status,
                'total_price' => rand(100,800),
            ]);

            BookingLog::create([
                'booking_id' => $booking->id,
                'user_id' => $homeowner->id,
                'action' => 'created',
                'notes' => 'Booking created automatically for new homeowner',
            ]);
        }
    }
}
