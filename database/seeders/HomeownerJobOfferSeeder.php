<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Homeowner;
use App\Models\HomeownerJobOffer;
use App\Models\Service;
use App\Models\ServiceCategory;

class HomeownerJobOfferSeeder extends Seeder
{
    public function run(): void
    {
        // Reset job photo directory
        Storage::disk('public')->deleteDirectory('uploads/job_photos');
        Storage::disk('public')->makeDirectory('uploads/job_photos');

        // Ensure homeowners exist
        if (Homeowner::count() === 0) {
            Homeowner::factory()->count(3)->create();
        }

        $homeowners = Homeowner::all();
        $categories = ServiceCategory::with('services')->get();

        if ($categories->isEmpty() || $categories->pluck('services')->flatten()->isEmpty()) {
            $this->command->warn('⚠ No categories/services found. Run ServiceSeeder first.');
            return;
        }

       
        foreach ($homeowners as $homeowner) {

        $jobOfferCount = rand(1, 5);

        for ($j = 0; $j < $jobOfferCount; $j++) {

        $category = $categories->random();
        $services = $category->services->pluck('id')->toArray();

        $jobType = fake()->randomElement(['standard', 'urgent', 'recurrent']);

        $preferredDate = null;
        $startDate = null;
        $endDate = null;
        $frequency = null;

        if (in_array($jobType, ['standard', 'urgent'])) {
            $preferredDate = fake()->dateTimeBetween('now', '+10 days');
        }

        if ($jobType === 'recurrent') {
            $frequency = fake()->randomElement(['daily', 'weekly', 'monthly', 'custom']);
            $startDate = fake()->dateTimeBetween('now', '+5 days');
            $endDate   = fake()->dateTimeBetween('+6 days', '+30 days');
        }

        $jobOffer = HomeownerJobOffer::create([
            'number' => 'JOB-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),

            'homeowner_id' => $homeowner->id,
            'service_category_id' => $category->id,

            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),

            'job_type' => $jobType,
            'job_size' => fake()->randomElement(['small', 'medium', 'large']),

            'budget' => fake()->randomFloat(2, 500, 5000),
            'final_budget' => null,

            'preferred_date' => $preferredDate,
            'frequency' => $frequency,
            'start_date' => $startDate,
            'end_date' => $endDate,

            'address' => fake()->address(),
            'latitude' => fake()->latitude(10.0, 14.0),
            'longitude' => fake()->longitude(120.0, 125.0),

            'status' => fake()->randomElement([
                'open','assigned', 'in_progress', 'completed', 'expired', 'cancelled'
            ]),
        ]);

        // Attach services
        if (!empty($services)) {
            $jobOffer->services()->sync(
                fake()->randomElements($services, rand(1, min(3, count($services))))
            );
        }

        // Photos
        $photos = rand(0, 2);
        for ($i = 0; $i < $photos; $i++) {
            $fileName = "job_" . uniqid() . "_{$i}.jpg";
            $filePath = "uploads/job_photos/{$fileName}";

            try {
                $imageContent = file_get_contents(
                    'https://via.placeholder.com/600x400?text=Job+Photo'
                );
                Storage::disk('public')->put($filePath, $imageContent);
            } catch (\Exception $e) {
                Storage::disk('public')->put($filePath, 'placeholder image');
            }

            DB::table('job_offer_photos')->insert([
                'job_offer_id' => $jobOffer->id,
                'file_path' => $filePath,
                'original_name' => $fileName,
                'file_size' => Storage::disk('public')->size($filePath),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            }
        }
    }

        $this->command->info('Homeowner job offers successfully seeded.');
    }
}
