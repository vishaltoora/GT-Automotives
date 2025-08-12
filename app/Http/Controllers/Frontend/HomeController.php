<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;

class HomeController extends Controller
{
    /**
     * Display the home page
     */
    public function index()
    {
        $featuredProducts = Product::active()
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $brands = Brand::active()
            ->inRandomOrder()
            ->limit(8)
            ->get();

 