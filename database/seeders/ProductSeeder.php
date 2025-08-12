<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Size;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = Brand::all();
        $sizes = Size::where('category', 'tire')->get();

        $products = [
            [
                'name' => 'Michelin Primacy MXM4',
                'description' => 'All-season touring tire with excellent wet and dry performance',
                'price' => 189.99,
                'stock_quantity' => 50,
                'brand' => 'Michelin',
                'model' => 'Primacy MXM4',
                'size' => '205/55R16',
                'category' => 'tire',
            ],
            [
                'name' => 'Bridgestone Turanza QuietTrack',
                'description' => 'Premium touring tire with noise reduction technology',
                'price' => 175.99,
                'stock_quantity' => 45,
                'brand' => 'Bridgestone',
                'model' => 'Turanza QuietTrack',
                'size' => '225/45R17',
                'category' => 'tire',
            ],
            [
                'name' => 'Goodyear Eagle F1 Asymmetric 6',
                'description' => 'Ultra-high performance summer tire for sports cars',
                'price' => 245.99,
                'stock_quantity' => 30,
                'brand' => 'Goodyear',
                'model' => 'Eagle F1 Asymmetric 6',
                'size' => '235/40R18',
                'category' => 'tire',
            ],
            [
                'name' => 'Continental ExtremeContact DWS06 Plus',
                'description' => 'All-season ultra-high performance tire',
                'price' => 265.99,
                'stock_quantity' => 35,
                'brand' => 'Continental',
                'model' => 'ExtremeContact DWS06 Plus',
                'size' => '245/35R19',
                'category' => 'tire',
            ],
            [
                'name' => 'Pirelli P Zero',
                'description' => 'Ultra-high performance summer tire for luxury vehicles',
                'price' => 385.99,
                'stock_quantity' => 25,
                'brand' => 'Pirelli',
                'model' => 'P Zero',
                'size' => '255/30R20',
                'category' => 'tire',
            ],
            [
                'name' => 'Michelin Defender LTX M/S',
                'description' => 'All-terrain tire for trucks and SUVs',
                'price' => 295.99,
                'stock_quantity' => 40,
                'brand' => 'Michelin',
                'model' => 'Defender LTX M/S',
                'size' => '265/35R21',
                'category' => 'tire',
            ],
            [
                'name' => 'Bridgestone Dueler H/L Alenza Plus',
                'description' => 'Highway terrain tire for luxury SUVs',
                'price' => 325.99,
                'stock_quantity' => 30,
                'brand' => 'Bridgestone',
                'model' => 'Dueler H/L Alenza Plus',
                'size' => '275/40R22',
                'category' => 'tire',
            ],
            [
                'name' => 'Goodyear Wrangler All-Terrain Adventure',
                'description' => 'All-terrain tire for off-road adventures',
                'price' => 355.99,
                'stock_quantity' => 35,
                'brand' => 'Goodyear',
                'model' => 'Wrangler All-Terrain Adventure',
                'size' => '285/35R23',
                'category' => 'tire',
            ],
        ];

        foreach ($products as $productData) {
            $brand = $brands->where('name', $productData['brand'])->first();
            $size = $sizes->where('name', $productData['size'])->first();

            Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'stock_quantity' => $productData['stock_quantity'],
                'brand' => $productData['brand'],
                'model' => $productData['model'],
                'size' => $productData['size'],
                'category' => $productData['category'],
                'is_active' => true,
            ]);
        }
    }
} 