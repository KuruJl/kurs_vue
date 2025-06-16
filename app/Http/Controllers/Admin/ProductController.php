<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category; // Добавил Category, так как используется в create и edit
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        // ПРАВИЛЬНЫЙ СПОСОБ использования уже зарегистрированного middleware 'is_admin'
        // Этот middleware (App\Http\Middleware\CheckAdmin) уже содержит логику проверки прав администратора.
        $this->middleware('is_admin');
    }

    public function index()
    {
        $products = Product::with('images')->latest()->paginate(10);
        return Inertia::render('Admin/Products/Index', compact('products'));
    }

    public function create()
    {
        // Убедитесь, что модель Category импортирована
        $categories = Category::all();
        return Inertia::render('Admin/Products/Create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug', // <-- ДОБАВИТЬ ЭТУ СТРОКУ
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'feature' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'is_main' => $key === 0
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Товар добавлен');
    }

    public function edit(Product $product)
    {
        $product->load('images');
        // Убедитесь, что модель Category импортирована
        $categories = Category::all();
        return Inertia::render('Admin/Products/Edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // <-- ДЛЯ МЕТОДА UPDATE ТАКЖЕ НУЖНО ДОБАВИТЬ ВАЛИДАЦИЮ SLUG
            // Важно игнорировать текущий slug продукта при проверке уникальности:
            'slug' => 'required|string|max:255|unique:products,slug,' . $product->id, // <-- ДОБАВИТЬ ЭТУ СТРОКУ
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'feature' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'new_images' => 'nullable|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'integer'
        ]);

        $product->update($validated);

        // Удаление изображений
        if (!empty($validated['deleted_images'])) {
            $imagesToDelete = ProductImage::whereIn('id', $validated['deleted_images'])
                ->where('product_id', $product->id)
                ->get();
            
            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        // Добавление новых изображений
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Товар обновлен');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        
        $product->delete();
        return redirect()->back()->with('success', 'Товар удален');
    }
}