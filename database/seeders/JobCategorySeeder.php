<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Note: Migration uses 'category_name' but model fillable uses 'name'
        // Using DB::table to insert directly to match migration structure
        $jobCategories = [
            [
                'category_name' => 'Plumbing',
                'description' => 'Plumbing installation, repair, and maintenance services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_name' => 'Electrical',
                'description' => 'Electrical work, wiring, and installation services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_name' => 'Carpentry',
                'description' => 'Carpentry and woodworking services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_name' => 'Painting',
                'description' => 'Interior and exterior painting services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_name' => 'Roofing',
                'description' => 'Roof installation, repair, and maintenance',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_name' => 'HVAC',
                'description' => 'Heating, ventilation, and air conditioning services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_name' => 'Flooring',
                'description' => 'Floor installation and repair services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_name' => 'Landscaping',
                'description' => 'Garden design and landscaping services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('job_categories')->insert($jobCategories);

        $this->command->info('✅ Created ' . count($jobCategories) . ' job categories');
    }
}

