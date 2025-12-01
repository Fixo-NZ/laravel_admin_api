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
        // Ensure upload directory exists
        Storage::disk('public')->deleteDirectory('uploads/job_photos');
        Storage::disk('public')->makeDirectory('uploads/job_photos');

        // Ensure we have homeowners
        if (Homeowner::count() === 0) {
            Homeowner::factory()->count(3)->create();
        }

        $homeowners = Homeowner::all();

        // Retrieve existing categories & services
        $categories = ServiceCategory::with('services')->get();

        if ($categories->isEmpty() || $categories->pluck('services')->flatten()->isEmpty()) {
            $this->command->warn('    No service categories or services found. Run ServiceSeeder first.');
            return;
        }

        foreach ($homeowners as $homeowner) {
            // Pick a random category with at least 1 service
            $category = $categories->random();
            $relatedServices = $category->services->pluck('id')->toArray();

            $jobType = fake()->randomElement(['standard', 'recurrent']);

            $frequency = $jobType === 'recurrent'
                ? fake()->randomElement(['daily', 'weekly', 'monthly', 'custom'])
                : null;

            $jobOffer = HomeownerJobOffer::create([
                'homeowner_id' => $homeowner->id,
                'service_category_id' => $category->id,
                'job_type' => $jobType,
                'frequency' => $frequency,
                'start_date' => fake()->dateTimeBetween('now', '+5 days'),
                'end_date' => fake()->dateTimeBetween('+6 days', '+20 days'),
                'preferred_date' => fake()->dateTimeBetween('now', '+10 days'),
                'title' => fake()->sentence(3),
                'job_size' => fake()->randomElement(['small', 'medium', 'large']),
                'description' => fake()->paragraph(),
                'address' => fake()->address(),
                'latitude' => fake()->latitude(10.0, 14.0),
                'longitude' => fake()->longitude(120.0, 125.0),
                'status' => fake()->randomElement(['pending', 'open', 'in_progress', 'completed', 'expired', 'cancelled']),
            ]);

            // Attach 1–3 random services under the selected category
            if (!empty($relatedServices)) {
                $jobOffer->services()->attach(fake()->randomElements($relatedServices, rand(1, min(3, count($relatedServices)))));
            }

            $photoCount = rand(0, 1);

            for ($i = 0; $i < $photoCount; $i++) {
                $fileName = "job_" . uniqid() . "_{$i}.jpg";
                $filePath = "uploads/job_photos/{$fileName}";

                // Try to generate placeholder image
                try {
                    $imageContent = file_get_contents('https://via.placeholder.com/600x400?text=Job+Photo');
                    Storage::disk('public')->put($filePath, $imageContent);
                } catch (\Exception $e) {
                    // Fallback if external URL failed
                    Storage::disk('public')->put($filePath, 'placeholder-image-content');
                }

                // Insert DB entry
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

        $this->command->info('    Homeowner job offers successfully seeded with categories, services, and photos.');
    }
}
