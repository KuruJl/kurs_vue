<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response; // Добавлено для явного указания типа возврата

class ProductController extends Controller
{
    public function index(Request $request): Response // Явно указываем тип возврата
    {
        try {
            // Загружаем категорию и ИЗОБРАЖЕНИЯ!
            $query = Product::query()->with('category', 'images'); // <-- ДОБАВЛЕНО 'images'

            // Фильтрация по цене
            if ($request->filled('min_price')) { // Используем filled() для проверки на непустое значение
                $query->where('price', '>=', $request->min_price);
            }
            
            if ($request->filled('max_price')) { // Используем filled()
                $query->where('price', '<=', $request->max_price);
            }
            
            // Фильтрация по категориям
            // $request->category может быть массивом или строкой ('1,2,3') в зависимости от того, как браузер отправляет
            // Если категория - это массив, (array)$request->category корректно.
            // Если это строка с ID, разделенными запятыми, нужно будет разобрать.
            // Vue-компонент отправляет массив `selectedCategoryIds`, поэтому ожидаем массив.
            if ($request->has('category') && !empty($request->category)) {
                // Преобразуем входящие категории в массив целых чисел
                $categoryIds = array_map('intval', (array) $request->category);
                $query->whereIn('category_id', $categoryIds);
            }
            
            // Поиск
            if ($request->filled('search')) { // Используем filled()
                $searchTerm = $request->search;
                $query->where('name', 'like', '%'.$searchTerm.'%')
                      ->orWhere('description', 'like', '%'.$searchTerm.'%'); // Добавим поиск по описанию
            }
            
            $products = $query->paginate(12)->withQueryString();
            $categories = Category::all();

            return Inertia::render('Catalog', [
                'products' => $products,
                'categories' => $categories,
                // Важно передать текущие значения фильтров обратно, чтобы Vue мог их инициализировать
                'initialMinPrice' => $request->input('min_price'),
                'initialMaxPrice' => $request->input('max_price'),
                'initialCategoryIds' => array_map('intval', (array) $request->input('category', [])), // Гарантируем массив int
                'initialSearch' => $request->input('search'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ProductController@index: '.$e->getMessage());
            return back()->with('error', 'Произошла ошибка при загрузке каталога');
        }
    }

    // ... ваш метод show()
    public function show($product)
    {
        try {
            // Пытаемся найти товар по ID или slug, загружая категорию и изображения
            $product = Product::with('category', 'images') // <-- ДОБАВЛЕНО 'images'
                ->where('id', $product)
                ->orWhere('slug', $product)
                ->firstOrFail();

            Log::debug('Product loaded:', [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'exists' => $product->exists
            ]);

            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->with('images', 'category') // <-- ДОБАВЛЕНО 'images' и 'category' для связанных товаров
                ->inRandomOrder()
                ->limit(4)
                ->get();

            return Inertia::render('ProductPage', [
                'product' => $product,
                'relatedProducts' => $relatedProducts,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Product not found: '.$product);
            abort(404, 'Товар не найден');
        } catch (\Exception $e) {
            Log::error('Error in ProductController@show: '.$e->getMessage());
            return back()->with('error', 'Произошла ошибка при загрузке товара');
        }
    }
}