<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::query()->with('category');
            
            // Фильтрация по цене
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            
            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
            
            // Фильтрация по категориям
            if ($request->has('category') && !empty($request->category)) {
                $query->whereIn('category_id', (array)$request->category);
            }
            
            // Поиск
            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%'.$request->search.'%');
            }
            
            $products = $query->paginate(12)->withQueryString();
            $categories = Category::all();

            return Inertia::render('Catalog', [
                'products' => $products,
                'categories' => $categories,
                'initialMinPrice' => $request->input('min_price'),
                'initialMaxPrice' => $request->input('max_price'),
                'initialCategoryIds' => array_map('intval', (array) $request->input('category', [])),
                'initialSearch' => $request->input('search'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ProductController@index: '.$e->getMessage());
            return back()->with('error', 'Произошла ошибка при загрузке каталога');
        }
    }

    public function show($product)
    {
        try {
            // Пытаемся найти товар по ID или slug
            $product = Product::with('category')
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