<?php

namespace Database\Seeders;

use App\Models\TradieComplaint;
use App\Models\Tradie;
use App\Models\Homeowner;
use Illuminate\Database\Seeder;

class TradieComplaintSeeder extends Seeder
{
    public function run(): void
    {
        // Create 15 pending complaints
        TradieComplaint::factory()
            ->count(15)
            ->pending()
            ->create();

        // Create 20 approved complaints
        TradieComplaint::factory()
            ->count(20)
            ->approved()
            ->create();

        // Create 10 dismissed complaints
        TradieComplaint::factory()
            ->count(10)
            ->dismissed()
            ->create();

        // Create 5 complaints without a homeowner (anonymous complaints)
        TradieComplaint::factory()
            ->count(5)
            ->withoutHomeowner()
            ->create();

        // If you want to create complaints for existing tradies/homeowners
        // Make sure you have some tradies and homeowners already seeded
        if (Tradie::count() > 0 && Homeowner::count() > 0) {
            $tradies = Tradie::inRandomOrder()->limit(5)->get();
            $homeowners = Homeowner::inRandomOrder()->limit(5)->get();

            foreach ($tradies as $tradie) {
                TradieComplaint::factory()
                    ->count(rand(1, 3))
                    ->create([
                        'tradie_id' => $tradie->id,
                        'homeowner_id' => $homeowners->random()->id,
                    ]);
            }
        }
    }
}
