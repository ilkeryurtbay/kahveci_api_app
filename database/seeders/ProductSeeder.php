<?php


// database/seeders/ProductSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use File;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $productsJson = File::get(database_path('products.json'));
        $products = json_decode($productsJson, true);

        foreach ($products as $productData) {
            // Remove 'product_id' key and use 'id' instead
            unset($productData['product_id']);

            Product::create($productData);
        }
    }
}
