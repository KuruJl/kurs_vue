<?php

namespace App\Http\Controllers;

use App\Models\Category; // Убедись, что это импортировано
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log; // <-- ДОБАВЬТЕ ЭТУ СТРОКУ

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category'); // Начинаем с eager loading категории

        // --- Добавление логики фильтрации (если она есть) ---
        // Например, для minPrice, maxPrice, category_id, search
        if ($request->filled('min_price')) { // Используйте filled() чтобы проверить, что значение не пустое
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        // ----------------------------------------------------

        // Используем paginate() для пагинации
        // Например, 12 продуктов на страницу.
        // withQueryString() сохраняет параметры фильтрации в URL пагинации.
        $products = $query->paginate(12)->withQueryString();

        // Логирование для отладки
      

        return Inertia::render('Catalog', [
            'products' => $products, // Inertia автоматически сериализует пагинатор в нужный формат для Vue
            'categories' => \App\Models\Category::all(), // Если вы передаете категории для фильтрации
            // Передаем текущие значения фильтров для инициализации полей в Vue
            'initialMinPrice' => $request->min_price ? (int)$request->min_price : null,
            'initialMaxPrice' => $request->max_price ? (int)$request->max_price : null,
            'initialCategoryId' => $request->category_id ? (int)$request->category_id : null,
            'initialSearch' => $request->search,
        ]);
    }

    /**
     * Отображает страницу с деталями товара.
     */
    public function show(Product $product) // Laravel автоматически найдет продукт по slug
    {
        // Получаем связанные товары (из той же категории, исключая текущий)
        $relatedProducts = Product::where('category_id', $product->category_id)
                                    ->where('id', '!=', $product->id)
                                    ->limit(4) // Ограничиваем количество связанных товаров
                                    ->get();

        // Логируем, что отправляется в ProductPage.vue
        \Log::info('Data sent to ProductPage.vue:', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image_url' => $product->image_url,
            'product_quantity_available' => $product->quantity_available,
            'related_products_count' => $relatedProducts->count(),
        ]);

        return Inertia::render('ProductPage', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}