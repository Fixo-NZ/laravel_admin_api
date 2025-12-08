<?php

namespace Database\Seeders;

use App\Models\JobRequest;
use App\Models\Homeowner;
use App\Models\JobCategories;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class JobRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $homeowners = Homeowner::all();
        $jobCategories = JobCategories::all();

        if ($homeowners->isEmpty()) {
            $this->command->error('❌ No homeowners found. Please run HomeownerSeeder first.');
            return;
        }

        if ($jobCategories->isEmpty()) {
            $this->command->error('❌ No job categories found. Please run JobCategorySeeder first.');
            return;
        }

        $jobTitles = [
            'Urgent Plumbing Repair',
            'Electrical Installation Needed',
            'Carpentry Work Required',
            'House Painting Project',
            'Roof Repair Service',
            'HVAC System Installation',
            'Flooring Replacement',
            'Garden Landscaping',
        ];

        $jobDescriptions = [
            'Need urgent plumbing repair for leaking pipes',
            'Install new electrical wiring for home renovation',
            'Custom carpentry work for kitchen cabinets',
            'Complete interior painting of 3-bedroom house',
            'Repair damaged roof tiles after storm',
            'Install new heat pump system',
            'Replace old carpet with new flooring',
            'Design and install new garden landscape',
        ];

        $jobTypes = ['urgent', 'standard', 'recurring'];
        $statuses = ['pending', 'active', 'assigned', 'completed', 'cancelled'];
        $locations = [
            ['location' => 'Auckland, New Zealand', 'lat' => -36.8485, 'lng' => 174.7633],
            ['location' => 'Wellington, New Zealand', 'lat' => -41.2865, 'lng' => 174.7762],
            ['location' => 'Christchurch, New Zealand', 'lat' => -43.5321, 'lng' => 172.6362],
            ['location' => 'Hamilton, New Zealand', 'lat' => -37.7870, 'lng' => 175.2793],
            ['location' => 'Dunedin, New Zealand', 'lat' => -45.8741, 'lng' => 170.5036],
        ];

        $created = 0;
        foreach ($homeowners as $homeowner) {
            // Create 1-2 job requests per homeowner
            $jobCount = rand(1, 2);
            
            for ($i = 0; $i < $jobCount; $i++) {
                $titleIndex = array_rand($jobTitles);
                $category = $jobCategories->shuffle()->first();
                $jobType = $jobTypes[array_rand($jobTypes)];
                $status = $statuses[array_rand($statuses)];
                $locationData = $locations[array_rand($locations)];

                JobRequest::create([
                    'homeowner_id' => $homeowner->id,
                    'job_category_id' => $category->id,
                    'title' => $jobTitles[$titleIndex],
                    'description' => $jobDescriptions[$titleIndex] ?? 'Job description',
                    'job_type' => $jobType,
                    'status' => $status,
                    'budget' => rand(500, 5000),
                    'location' => $locationData['location'],
                    'latitude' => $locationData['lat'],
                    'longitude' => $locationData['lng'],
                    'scheduled_at' => $status === 'assigned' || $status === 'active' 
                        ? Carbon::now()->addDays(rand(1, 7)) 
                        : null,
                ]);
                $created++;
            }
        }

        $this->command->info("✅ Created {$created} job requests");
    }
}

