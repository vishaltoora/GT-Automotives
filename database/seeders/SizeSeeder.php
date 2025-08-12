<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Size;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = [
            ['name' => '195/65R15', 'category' => 'tire', 'description' => 'Common passenger car tire size'],
            ['name' => '205/55R16', 'category' => 'tire', 'description' => 'Popular sedan tire size'],
            ['name' => '225/45R17', 'category' => 'tire', 'description' => 'Sport car tire size'],
            ['name' => '235/40R18', 'category' => 'tire', 'description' => 'Performance tire size'],
            ['name' => '245/35R19', 'category' => 'tire', 'description' => 'Luxury car tire size'],
            ['name' => '255/30R20', 'category' => 'tire', 'description' => 'High-performance tire size'],
            ['name' => '265/35R21', 'category' => 'tire', 'description' => 'SUV and truck tire size'],
            ['name' => '275/40R22', 'category' => 'tire', 'description' => 'Large SUV tire size'],
            ['name' => '285/35R23', 'category' => 'tire', 'description' => 'Premium SUV tire size'],
            ['name' => '295/30R24', 'category' => 'tire', 'description' => 'Luxury SUV tire size'],
            ['name' => '15x6.5', 'category' => 'rim', 'description' => 'Standard 15-inch rim'],
            ['name' => '16x7', 'category' => 'rim', 'description' => 'Standard 16-inch rim'],
            ['name' => '17x7.5', 'category' => 'rim', 'description' => 'Sport 17-inch rim'],
            ['name' => '18x8', 'category' => 'rim', 'description' => 'Performance 18-inch rim'],
            ['name' => '19x8.5', 'category' => 'rim', 'description' => 'Luxury 19-inch rim'],
            ['name' => '20x9', 'category' => 'rim', 'description' => 'Premium 20-inch rim'],
        ];

        foreach ($sizes as $size) {
            Size::create($size);
        }
    }
} 