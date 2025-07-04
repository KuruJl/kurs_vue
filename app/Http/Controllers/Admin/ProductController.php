<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category; // Добавил Category, так как используется в create и edit
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Models\OrderItem; // <--- ДОБАВЬТЕ ЭТОТ ИМПОРТ
use Illuminate\Support\Facades\DB; // <--- ДОБАВЬТЕ ЭТОТ ИМПОРТ

class ProductController extends Controller
{
    public function __construct()
    {
        // ПРАВИЛЬНЫЙ СПОСОБ использования уже зарегистрированного middleware 'is_admin'
        // Этот middleware (App\Http\Middleware\CheckAdmin) уже содержит логику проверки прав администратора.
        $this->middleware('is_admin');
    }

    public function index(Request $request)
    {
        // --- Параметры сортировки (остаются без изменений) ---
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        $allowedSorts = ['name', 'price', 'quantity', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
    
        // --- Логика фильтрации ---
        $productsQuery = Product::query() // Начинаем построение запроса
            ->with('images')
            // 1. Фильтр по поисковой строке (ищем в названии и описании)
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            // 2. Фильтр по ID категории
            ->when($request->input('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            });
    
        // Применяем сортировку и пагинацию к уже отфильтрованному запросу
        $products = $productsQuery->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString(); // Сохраняет ВСЕ параметры запроса (и фильтры, и сортировку)
    
        // 3. Получаем все категории для выпадающего списка в фильтре
        $categories = Category::all();
    
        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories, // Передаем категории во фронтенд
            // 4. Передаем ВСЕ текущие параметры запроса для заполнения полей формы и индикаторов сортировки
            'query' => $request->only(['sort', 'direction', 'search', 'category_id']),
        ]);
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
        'slug' => 'required|string|max:255|unique:products,slug',
        'description' => 'required|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'feature' => 'nullable|string',
        'quantity' => 'required|integer|min:0',
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048' // Добавил webp на всякий случай
    ]);

    $product = Product::create($validated);

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $key => $imageFile) {
            
            // --- ЛОГИКА ИЗМЕНЕНА ЗДЕСЬ ---

            // 1. Создаем уникальное имя для файла, чтобы избежать конфликтов
            // Например: 1678886400_my_cool_photo.jpg
            $imageName = time() . '_' . $imageFile->getClientOriginalName();
            
            // 2. Перемещаем файл напрямую в папку public/images
            // public_path('images') вернет абсолютный путь к этой папке на сервере.
            $imageFile->move(public_path('images'), $imageName);

            // 3. Формируем публичный URL для сохранения в базу данных
            // Этот путь будет использоваться в <img src="..."> на фронтенде
            $publicPath = '/images/' . $imageName;

            // 4. Сохраняем в базу данных правильный путь
            $product->images()->create([
                'path' => $publicPath,
                'is_main' => $key === 0 // Первое изображение становится главным
            ]);
        }
    }

    return redirect()->route('admin.products.index')->with('success', 'Товар успешно добавлен');
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
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $publicPath = '/images/' . $imageName;
                
                $product->images()->create([
                    'path' => $publicPath,
                    'is_main' => false // или ваша логика для главного изображения
                ]);
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