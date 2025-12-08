<?php

namespace Database\Seeders;

use App\Models\Tradie;
use App\Models\Service;
use Illuminate\Database\Seeder;

class TradieServiceSeeder extends Seeder
{
    public function run(): void
    {
        $tradies = Tradie::all();
        $services = Service::all();

        if ($tradies->isEmpty() || $services->isEmpty()) {
            $this->command->error('❌ Make sure Tradies and Services are seeded first.');
            return;
        }

        $created = 0;

        foreach ($tradies as $tradie) {
            // Attach 2-4 random services to each tradie
            $servicesToAttach = $services->shuffle()->take(rand(2, 4));

            foreach ($servicesToAttach as $service) {
                if (!$tradie->services()->where('services.id', $service->id)->exists()) {
                    $tradie->services()->attach($service->id, [
                        'base_rate' => rand(50, 150),
                    ]);
                    $created++;
                }
            }
        }

        $this->command->info("✅ Created {$created} tradie-service relationships");
    }
}
