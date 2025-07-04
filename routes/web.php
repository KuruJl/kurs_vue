    <?php

    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\ProductController;
    use App\Http\Controllers\HomeController;
    use App\Http\Controllers\CartController;
    use App\Http\Controllers\OrderController;
    use Illuminate\Support\Facades\Route;
    use Inertia\Inertia;
    use Illuminate\Foundation\Application;
    use App\Http\Controllers\Admin\OrderController as AdminOrderController; 
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{product}/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
                                                            

        // Маршрут для оформления заказа
        Route::post('/checkout', [OrderController::class, 'store'])
    ->middleware(['auth', 'verified']) // Только для авторизованных и подтвержденных пользователей
    ->name('checkout.store');

// Роут для просмотра истории заказов в профиле
Route::get('/profile/orders', [OrderController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('profile.orders');
    });

    Route::middleware(['auth'])->prefix('admin')->group(function () {
        Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products.index');
        Route::get('/products/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/products/{product}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('admin.products.destroy');

            // Роут для списка всех заказов
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            // Роут для просмотра деталей конкретного заказа
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            // Роут для обновления статуса заказа (используем PUT метод)
            Route::put('/orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

            Route::put('/orders/{order}/update-items', [AdminOrderController::class, 'updateItems'])->name('orders.updateItems');

            Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');

 Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)
         ->names('admin.categories');
    });
    
    // Маршрут для страницы каталога
    Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');

    // Маршруты для страниц без аутентификации
    Route::get('/support', function () {
        return Inertia::render('Support');
    });


    // Маршрут для страницы товара по id
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    require __DIR__.'/auth.php';