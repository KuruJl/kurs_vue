<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Application;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Главная страница
Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Дашборд (требует аутентификации и верификации)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Маршруты, требующие аутентификации
Route::middleware('auth')->group(function () {
    // Профиль пользователя
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Маршруты корзины
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}/remove', [CartController::class, 'remove'])->name('cart.remove');

    // Маршрут для оформления заказа
    Route::get('/checkout', function () {
        return Inertia::render('CheckoutPage'); // Или твой контроллер
    })->name('checkout');

});

// Маршрут для страницы каталога
Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');

// Маршруты для страниц без аутентификации
Route::get('/support', function () {
    return Inertia::render('Support');
});
Route::get('/square', function () {
    return Inertia::render('Square');
});

// Маршрут для страницы товара по slug
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');


require __DIR__.'/auth.php';