<?php

namespace App\Http\Controllers;

use App\Models\Product; // Не забудьте импортировать модель Product
use Inertia\Inertia;
use Inertia\Response; // Добавлено для явного указания типа возврата

class HomeController extends Controller
{
    /**
     * Отображает главную страницу с бестселлерами и категориями (опционально).
     */
    public function index(): Response // Явно указываем тип возврата
    {
        // Загружаем 3 бестселлера или просто случайных продукта с изображениями.
        // Предполагаем, что у вас есть ProductModel и связь 'images'.
        $bestSellers = Product::with('images')
                                ->inRandomOrder() // Для примера берем случайные, можете изменить логику
                                ->limit(3)
                                ->get();

        // Если вы хотите также динамически выводить категории на главной,
        // раскомментируйте это и передайте 'categories' в Inertia::render
        // use App\Models\Category;
        // $categories = Category::all();

        return Inertia::render('Main', [
            'bestSellers' => $bestSellers->map(function ($product) {
                // Используем аксессор 'main_image_url', который мы настроили в модели Product.
                // Он уже должен возвращать полный URL или заглушку.
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug, // Важно для красивых URL
                    'price' => $product->price,
                    'image_url' => $product->main_image_url, // Используем аксессор
                    // 'description' => $product->description, // Можно добавить, если нужно
                ];
            }),
            // 'categories' => $categories, // Если вы хотите динамические категории
        ]);
    }
}