<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            // Electrical
            ['name' => 'Electrical Installation', 'description' => 'New electrical installations and wiring', 'category' => 'Electrical'],
            ['name' => 'Electrical Repair', 'description' => 'Repair and maintenance of electrical systems', 'category' => 'Electrical'],
            ['name' => 'Lighting Installation', 'description' => 'Indoor and outdoor lighting installation', 'category' => 'Electrical'],
            
            // Plumbing
            ['name' => 'Plumbing Installation', 'description' => 'New plumbing installations and pipe work', 'category' => 'Plumbing'],
            ['name' => 'Plumbing Repair', 'description' => 'Repair leaks, blockages, and plumbing issues', 'category' => 'Plumbing'],
            ['name' => 'Bathroom Renovation', 'description' => 'Complete bathroom renovation and plumbing', 'category' => 'Plumbing'],
            
            // Building & Construction
            ['name' => 'House Building', 'description' => 'New house construction and building', 'category' => 'Building'],
            ['name' => 'Home Renovation', 'description' => 'Home renovation and extension work', 'category' => 'Building'],
            ['name' => 'Deck Building', 'description' => 'Deck construction and outdoor structures', 'category' => 'Building'],
            
            // Roofing
            ['name' => 'Roof Installation', 'description' => 'New roof installation and replacement', 'category' => 'Roofing'],
            ['name' => 'Roof Repair', 'description' => 'Roof repairs and maintenance', 'category' => 'Roofing'],
            ['name' => 'Gutter Installation', 'description' => 'Gutter installation and repair', 'category' => 'Roofing'],
            
            // Painting
            ['name' => 'Interior Painting', 'description' => 'Interior house and room painting', 'category' => 'Painting'],
            ['name' => 'Exterior Painting', 'description' => 'Exterior house and building painting', 'category' => 'Painting'],
            
            // Landscaping
            ['name' => 'Garden Design', 'description' => 'Garden design and landscaping', 'category' => 'Landscaping'],
            ['name' => 'Lawn Maintenance', 'description' => 'Lawn care and garden maintenance', 'category' => 'Landscaping'],
            ['name' => 'Tree Services', 'description' => 'Tree removal, pruning, and arborist services', 'category' => 'Landscaping'],
            
            // HVAC
            ['name' => 'Heat Pump Installation', 'description' => 'Heat pump installation and setup', 'category' => 'HVAC'],
            ['name' => 'Air Conditioning', 'description' => 'Air conditioning installation and repair', 'category' => 'HVAC'],
            
            // Flooring
            ['name' => 'Flooring Installation', 'description' => 'Timber, tile, and carpet flooring installation', 'category' => 'Flooring'],
            ['name' => 'Floor Sanding', 'description' => 'Floor sanding and polishing services', 'category' => 'Flooring'],
        ];

        foreach ($services as $service) {
            \DB::table('services')->insert([
                'name' => $service['name'],
                'description' => $service['description'],
                'category' => $service['category'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
