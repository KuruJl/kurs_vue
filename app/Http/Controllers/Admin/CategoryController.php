<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin');
    }

    /**
     * Отображает список всех категорий.
     */
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories
        ]);
    }

    /**
     * Показывает форму для создания новой категории.
     */
    public function create()
    {
        return Inertia::render('Admin/Categories/Create');
    }

    /**
     * Сохраняет новую категорию в базе данных.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'required|string|max:255|unique:categories,slug',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Категория успешно создана.');
    }

    /**
     * Показывает форму для редактирования категории.
     */
    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category
        ]);
    }

    /**
     * Обновляет категорию в базе данных.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Категория успешно обновлена.');
    }

    /**
     * Удаляет категорию.
     */
    public function destroy(Category $category)
    {
        // Проверка, есть ли у категории связанные товары
        if ($category->products()->count() > 0) {
            return redirect()->back()->withErrors(['error' => 'Нельзя удалить категорию, так как к ней привязаны товары.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Категория успешно удалена.');
    }
}