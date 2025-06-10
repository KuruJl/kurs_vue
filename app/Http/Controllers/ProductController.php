<?php

namespace App\Http\Controllers;

use App\Models\Category; // Убедись, что это импортировано
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query();

        // Применение фильтров по категориям
        if ($request->has('category') && is_array($request->input('category'))) {
            $products->whereIn('category_id', $request->input('category'));
        }

        // Применение фильтров по цене
        if ($request->filled('min_price')) {
            $products->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $products->where('price', '<=', $request->input('max_price'));
        }

        $filteredProducts = $products->paginate(10)->withQueryString();
        
        // Для каталога, если ты используешь подход с переводом на бэкенде:
        // $categories = Category::all()->map(function ($category) {
        //     return [
        //         'id' => $category->id,
        //         'name' => __("categories." . $category->name),
        //         'original_name' => $category->name,
        //     ];
        // });
        // Если используешь перевод на фронте, то просто Category::all():
        $categories = Category::all();

        return Inertia::render('Catalog', [
            'products' => $filteredProducts,
            'categories' => $categories,
            'filters' => $request->only(['category', 'min_price', 'max_price']),
        ]);
    }

    /**
     * Отображает страницу с деталями товара.
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
                            ->with('category') // Загружаем категорию для отображения, если нужно
                            ->firstOrFail(); // Если товар не найден, вернется 404

        // Получаем несколько случайных товаров для секции "ТАКЖЕ ПОКУПАЮТ"
        // Исключаем текущий товар, чтобы не показывать его в рекомендациях
        $relatedProducts = Product::where('id', '!=', $product->id)
                                  ->inRandomOrder()
                                  ->limit(4) // Ограничиваем до 4 товаров, как на старой странице
                                  ->get();

        return Inertia::render('ProductPage', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'description' => $product->description,
                'image_url' => $product->image, // Используем аксессор getImageUrlAttribute
                'in_stock' => $product->in_stock,
                'quantity_available' => $product->quantity,
                'slug' => $product->slug,
                'category_name' => $product->category ? $product->category->name : null, // Имя категории
            ],
            'relatedProducts' => $relatedProducts->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $p->price,
                    'image_url' => $p->image,
                    'slug' => $p->slug,
                ];
            }),
        ]);
    }
}