<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Michelin', 'description' => 'Premium tire manufacturer known for quality and performance'],
            ['name' => 'Bridgestone', 'description' => 'Leading tire brand with innovative technology'],
            ['name' => 'Goodyear', 'description' => 'American tire company with long history of excellence'],
            ['name' => 'Continental', 'description' => 'German engineering for superior tire performance'],
            ['name' => 'Pirelli', 'description' => 'Italian luxury tire brand for high-performance vehicles'],
            ['name' => 'Dunlop', 'description' => 'British tire manufacturer specializing in motorcycle and car tires'],
            ['name' => 'Yokohama', 'description' => 'Japanese tire company with advanced technology'],
            ['name' => 'BF Goodrich', 'description' => 'American brand known for off-road and performance tires'],
            ['name' => 'Falken', 'description' => 'Japanese brand offering value and performance'],
            ['name' => 'Toyo', 'description' => 'Japanese manufacturer of high-quality tires'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
} 