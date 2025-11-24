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
        Storage::disk('public')->makeDirectory('uploads/job_photos');

        // Ensure we have homeowners
        if (Homeowner::count() === 0) {
            Homeowner::factory()->count(3)->create();
        }

        $homeowners = Homeowner::all();

        // Retrieve existing categories & services
        $categories = ServiceCategory::with('services')->get();

        if ($categories->isEmpty() || $categories->pluck('services')->flatten()->isEmpty()) {
            $this->command->warn('⚠️ No service categories or services found. Run ServiceSeeder first.');
            return;
        }

        foreach ($homeowners as $homeowner) {
            // Pick a random category with at least 1 service
            $category = $categories->random();
            $relatedServices = $category->services->pluck('id')->toArray();

            // Create job offer
            $jobOffer = HomeownerJobOffer::create([
                'homeowner_id' => $homeowner->id,
                'service_category_id' => $category->id,
                'job_type' => fake()->randomElement(['standard', 'urgent', 'recurrent']),
                'frequency' => fake()->optional()->randomElement(['daily', 'weekly', 'monthly', 'custom']),
                'start_date' => fake()->dateTimeBetween('now', '+5 days'),
                'end_date' => fake()->dateTimeBetween('+6 days', '+20 days'),
                'preferred_date' => fake()->dateTimeBetween('now', '+10 days'),
                'title' => fake()->sentence(3),
                'job_size' => fake()->randomElement(['small', 'medium', 'large']),
                'description' => fake()->paragraph(),
                'address' => fake()->address(),
                'latitude' => fake()->latitude(10.0, 14.0),
                'longitude' => fake()->longitude(120.0, 125.0),
                'status' => fake()->randomElement(['pending', 'open', 'in_progress', 'completed']),

                // 🔥 NEW FIELDS ADDED
                'start_time' => fake()->dateTimeBetween('now', '+5 days'),
                'end_time'   => fake()->dateTimeBetween('+6 days', '+20 days'),
                'rescheduled_at' => fake()->optional()->dateTimeBetween('-5 days', 'now'),
            ]);

            // Attach 1–3 random services under the selected category
            if (!empty($relatedServices)) {
                $jobOffer->services()->attach(
                    fake()->randomElements($relatedServices, rand(1, min(3, count($relatedServices))))
                );
            }

            // Create fake photos (simulate uploads)
            foreach (range(1, 2) as $i) {
                $fileName = "job_" . uniqid() . "_{$i}.jpg";
                $filePath = "uploads/job_photos/{$fileName}";

                // Generate a placeholder image
                try {
                    $imageContent = file_get_contents('https://via.placeholder.com/600x400');
                    Storage::disk('public')->put($filePath, $imageContent);
                } catch (\Exception $e) {
                    Storage::disk('public')->put($filePath, 'placeholder image content');
                }

                // Insert photo record
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

        $this->command->info('✅ Homeowner job offers successfully seeded with times and rescheduled_at.');
    }
}
