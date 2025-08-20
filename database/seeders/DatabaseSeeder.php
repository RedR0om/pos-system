<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create cashier user
        \App\Models\User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'status' => 'active',
        ]);

        $category = Category::firstOrCreate(['name' => 'General']);
        Product::firstOrCreate([
            'sku' => 'SKU-001',
        ], [
            'name' => 'Sample Product',
            'category_id' => $category->id,
            'price' => 100,
            'cost' => 60,
            'stock' => 50,
            'is_active' => true,
        ]);

        // Canteen items
        $food = Category::firstOrCreate(['name' => 'Food']);
        $beverage = Category::firstOrCreate(['name' => 'Beverage']);

        Product::firstOrCreate([
            'sku' => 'BUR-001',
        ], [
            'name' => 'Burger',
            'category_id' => $food->id,
            'price' => 120.00,
            'cost' => 70.00,
            'stock' => 100,
            'is_active' => true,
        ]);

        Product::firstOrCreate([
            'sku' => 'MT-001',
        ], [
            'name' => 'Milk Tea',
            'category_id' => $beverage->id,
            'price' => 90.00,
            'cost' => 45.00,
            'stock' => 120,
            'is_active' => true,
        ]);
    }
}
