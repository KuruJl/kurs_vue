<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category; // <--- ШАГ 1: ДОБАВЬТЕ ЭТОТ ИМПОРТ
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Отображает главную страницу с бестселлерами и категориями.
     */
    public function index(): Response
    {
        // Логика для бестселлеров остается без изменений
        $bestSellers = Product::with('images')
                                ->inRandomOrder()
                                ->limit(3)
                                ->get();

        // --- ШАГ 2: АКТИВИРУЙТЕ ЭТОТ БЛОК ---
        // Получаем все категории из базы данных
        $categories = Category::all()->map(fn($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ]);
        // ------------------------------------

        return Inertia::render('Main', [
            'bestSellers' => $bestSellers->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'image_url' => $product->main_image_url,
                ];
            }),
            // --- ШАГ 3: ПЕРЕДАЙТЕ КАТЕГОРИИ ВО VUE ---
            'categories' => $categories, 
        ]);
    }
}