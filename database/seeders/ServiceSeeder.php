<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Homeowner;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $homeowners = Homeowner::all();
        $categories = Category::all();

        if ($homeowners->isEmpty()) {
            $this->command->error('❌ No homeowners found. Please run HomeownerSeeder first.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->error('❌ No categories found. Please run CategorySeeder first.');
            return;
        }

        $servicesData = [
            ['job_description' => 'Plumbing', 'category_name' => 'Plumbing'],
            ['job_description' => 'Electrical Installation', 'category_name' => 'Electrical'],
            ['job_description' => 'Carpentry Work', 'category_name' => 'Carpentry'],
            ['job_description' => 'Painting Interior', 'category_name' => 'Painting'],
            ['job_description' => 'Roof Repair', 'category_name' => 'Roofing'],
        ];

        $created = 0;

        foreach ($servicesData as $data) {
            // Pick a random homeowner
            $homeowner = $homeowners->random();

            // Find category by name
            $category = $categories->firstWhere('category_name', $data['category_name']);
            if (!$category) continue;

            Service::create([
                'homeowner_id'    => $homeowner->id,
                'job_categoryid'  => $category->id,
                'job_description' => $data['job_description'],
                'location'        => $homeowner->address ?? 'Unknown Address', // ✅ Add location
                'status'          => 'Pending',
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
            ]);

            $created++;
        }

        $this->command->info("✅ Created {$created} services with homeowners and location");
    }
}
