<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Plumbing',
            'Electrical',
            'Carpentry',
            'Painting',
            'Roofing',
            'HVAC',
            'Flooring',
            'Landscaping',
            'Cleaning',
            'Handyman',
        ];

        foreach ($categories as $categoryName) {
            Category::create([
                'category_name' => $categoryName,
            ]);
        }

        $this->command->info('✅ Created ' . count($categories) . ' categories');
    }
}

