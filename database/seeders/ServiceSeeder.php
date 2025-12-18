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
        // Temporary Datas for Service Categories and Services (Based on Figma Design)
        $categories = [
            [
                'category_name' => 'Electrical',
                'category_subtitle' => 'Lighting installation, wiring, outlet and switch installation, ceiling fan setup, generator installation, CCTV installation, etc.',
                'category_icon' => 'electrical'
            ],
            [
                'category_name' => 'Painting',
                'category_subtitle' => 'Exterior and interior painting, crack filling and repairs, wall plastering, roof painting, fence painting, decorative finishes, etc.',
                'category_icon' => 'painting'
            ],
            [
                'category_name' => 'Plumbing',
                'category_subtitle' => 'Pipe installation and repair, leak detection, faucet replacement, bathroom renovation, drainage systems, water heater installation, etc.',
                'category_icon' => 'plumbing'
            ],
            [
                'category_name' => 'Carpentry',
                'category_subtitle' => 'Furniture making, cabinet installation, wood framing, door and window fitting, shelving, custom woodwork, etc.',
                'category_icon' => 'carpentry'
            ],
            [
                'category_name' => 'Appliance Repair',
                'category_subtitle' => 'Refrigerator repair, washing machine repair, oven and stove service, microwave repair, dishwasher maintenance, etc.',
                'category_icon' => 'appliance-repair'
            ],
            [
                'category_name' => 'Fencing & Decking',
                'category_subtitle' => 'Fence installation and repair, deck construction, railing installation, pergolas, outdoor wooden structures, etc.',
                'category_icon' => 'fencing'
            ],
            [
                'category_name' => 'Pest Control',
                'category_subtitle' => 'Termite treatment, rodent control, bed bug extermination, cockroach treatment, fumigation services, etc.',
                'category_icon' => 'pest-control'
            ],
            [
                'category_name' => 'Drywall & Plastering',
                'category_subtitle' => 'Drywall installation, plaster repair, wall patching, ceiling repair, skim coating, etc.',
                'category_icon' => 'drywall'
            ],
            [
                'category_name' => 'Window & Door',
                'category_subtitle' => 'Window installation, door installation, lock replacement, sliding door repair, glass replacement, etc.',
                'category_icon' => 'windows'
            ],
            [
                'category_name' => 'HVAC',
                'category_subtitle' => 'Air conditioning installation and repair, heat pump setup, duct cleaning, thermostat installation, ventilation systems, etc.',
                'category_icon' => 'hvac'
            ],
            [
                'category_name' => 'Masonry',
                'category_subtitle' => 'Bricklaying, stonework, concrete repair, chimney repair, patio and walkway construction, etc.',
                'category_icon' => 'masonry'
            ],
            [
                'category_name' => 'Flooring',
                'category_subtitle' => 'Tile installation, timber and laminate flooring, carpet laying, floor sanding and polishing, epoxy flooring, etc.',
                'category_icon' => 'flooring'
            ],
            [
                'category_name' => 'Gardening',
                'category_subtitle' => 'Garden design, lawn mowing, hedge trimming, tree pruning, landscaping, irrigation system installation, etc.',
                'category_icon' => 'gardening'
            ],
            [
                'category_name' => 'Roofing',
                'category_subtitle' => 'Roof installation, roof repair, gutter installation, waterproofing, roof cleaning, metal and tile roofing, etc.',
                'category_icon' => 'roofing'
            ],
            [   
                'category_name' => 'Other Services',
                'category_subtitle' => 'General handyman services, home maintenance, odd jobs, furniture assembly, moving help, etc.',
                'status' => 'inactive',
            ]
        ];

        $services = [
            // Electrical (category_id = 1)
            ['name' => 'Electrical Installation', 'description' => 'New electrical installations and wiring', 'category_id' => 1],
            ['name' => 'Electrical Repair', 'description' => 'Repair and maintenance of electrical systems', 'category_id' => 1],
            ['name' => 'Lighting Installation', 'description' => 'Indoor and outdoor lighting installation', 'category_id' => 1],

            // Painting (category_id = 2)
            ['name' => 'Interior Painting', 'description' => 'Interior house and room painting', 'category_id' => 2],
            ['name' => 'Exterior Painting', 'description' => 'Exterior house and building painting', 'category_id' => 2],

            // Plumbing (category_id = 3)
            ['name' => 'Plumbing Installation', 'description' => 'New plumbing installations and pipe work', 'category_id' => 3],
            ['name' => 'Plumbing Repair', 'description' => 'Repair leaks, blockages, and plumbing issues', 'category_id' => 3],
            ['name' => 'Bathroom Renovation', 'description' => 'Complete bathroom renovation and plumbing', 'category_id' => 3],

            // Carpentry (category_id = 4)
            ['name' => 'Cabinet Installation', 'description' => 'Custom cabinet installation and repairs', 'category_id' => 4],
            ['name' => 'Wood Framing', 'description' => 'Wood framing for homes and extensions', 'category_id' => 4],

            // Appliance Repair (category_id = 5)
            ['name' => 'Refrigerator Repair', 'description' => 'Fix cooling issues', 'category_id' => 5],
            ['name' => 'Washing Machine Repair', 'description' => 'Repair washing machines', 'category_id' => 5],

            // Fencing & Decking (category_id = 6)
            ['name' => 'Fence Installation', 'description' => 'New fence installation and repairs', 'category_id' => 6],
            ['name' => 'Deck Building', 'description' => 'Deck construction and outdoor structures', 'category_id' => 6],

            // Pest Control (category_id = 7)
            ['name' => 'Termite Treatment', 'description' => 'Protect home from termites', 'category_id' => 7],
            ['name' => 'Rodent Control', 'description' => 'Safe rodent extermination', 'category_id' => 7],

            // Drywall & Plastering (category_id = 8)
            ['name' => 'Drywall Installation', 'description' => 'Install and finish drywall', 'category_id' => 8],
            ['name' => 'Plaster Repair', 'description' => 'Patch and repair plaster walls', 'category_id' => 8],

            // Window & Door (category_id = 9)
            ['name' => 'Window Installation', 'description' => 'Install new windows', 'category_id' => 9],
            ['name' => 'Door Replacement', 'description' => 'Install or replace doors', 'category_id' => 9],

            // HVAC (category_id = 10)
            ['name' => 'Heat Pump Installation', 'description' => 'Heat pump installation and setup', 'category_id' => 10],
            ['name' => 'Air Conditioning', 'description' => 'Air conditioning installation and repair', 'category_id' => 10],

            // Masonry (category_id = 11)
            ['name' => 'Bricklaying', 'description' => 'Brick and block wall construction', 'category_id' => 11],
            ['name' => 'Concrete Repair', 'description' => 'Repair cracked or damaged concrete', 'category_id' => 11],

            // Flooring (category_id = 12)
            ['name' => 'Flooring Installation', 'description' => 'Timber, tile, and carpet flooring installation', 'category_id' => 12],
            ['name' => 'Floor Sanding', 'description' => 'Floor sanding and polishing services', 'category_id' => 12],

            // Gardening (category_id = 13)
            ['name' => 'Garden Design', 'description' => 'Garden design and landscaping', 'category_id' => 13],
            ['name' => 'Lawn Maintenance', 'description' => 'Lawn care and garden maintenance', 'category_id' => 13],
            ['name' => 'Tree Services', 'description' => 'Tree removal, pruning, and arborist services', 'category_id' => 13],

            // Roofing (category_id = 14)
            ['name' => 'Roof Installation', 'description' => 'New roof installation and replacement', 'category_id' => 14],
            ['name' => 'Roof Repair', 'description' => 'Roof repairs and maintenance', 'category_id' => 14],
            ['name' => 'Gutter Installation', 'description' => 'Gutter installation and repair', 'category_id' => 14],
        ];



       // 1. Insert Categories
        foreach ($categories as $category) {
            \DB::table('service_categories')->insert([
                'name'     => $category['category_name'],
                'description' => $category['category_subtitle'],
                'icon' => $category['category_icon'] ?? 'none',
                'status' => $category['status'] ?? 'active',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        // 2. Insert Services with correct category_id
        foreach ($services as $service) {
            \DB::table('services')->insert([
                'name'        => $service['name'],
                'description' => $service['description'],
                'category_id' => $service['category_id'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }   
        
    }
}
