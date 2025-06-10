<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Используем DB для прямых запросов
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'keyboards'],
            ['name' => 'mice'],
            ['name' => 'headphones'],
            ['name' => 'carpets'],
        ];

        foreach ($categories as $categoryData) {
            DB::table('categories')->insert([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        
    }
}