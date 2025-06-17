<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product; // Ваша модель Product
use Illuminate\Support\Facades\Log;
use Inertia\Response;
use App\Services\CartService; // Импортируем CartService

class CartController extends Controller
{
    protected $cartService;

    // Внедряем CartService через конструктор
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Отображает содержимое корзины.
     */
    public function index(): Response
    {
        // Получаем корзину из сервиса (она уже будет загружена из БД)
        $cart = $this->cartService->getCart();
        // Загружаем связанные продукты для каждого элемента корзины

        // Логирование теперь будет относиться к БД-корзине, если нужно
        Log::info('Cart content from DB on index page LOAD:', ['cart_id' => $cart->id, 'items_count' => $cart->items->count()]);
        Log::info('Session ID on index page LOAD (for context):', ['id' => session()->getId()]); // Оставим для отладки сессии, но корзина уже не зависит от неё

        $detailedCartItems = [];
        $total = 0;

        // Перебираем элементы корзины, полученные из БД
        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product; // Продукт уже загружен благодаря load('items.product')

            // Этот блок if ($product) { ... } else { ... } будет менее актуален,
            // если при удалении продукта из БД вы также удаляете его из cart_items
            // (благодаря onDelete('cascade') в миграции CartItem, если настроено правильно)
            if ($product) {
                // Если аксессор main_image_url у вас в модели Product
                $imageUrl = $product->main_image_url; 
                
                $detailedCartItems[] = [
                    'id' => $product->id, // ID продукта, а не cartItem ID
                    'name' => $product->name,
                    'image' => $imageUrl,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity, // Количество из cartItem
                    'slug' => $product->slug, 
                    'max_available' => $product->quantity_available, // Используем аксессор quantity_available
                ];
                $total += $product->price * $cartItem->quantity;
            } else {
                // Если по какой-то причине продукт не найден (например, был удален из БД, но не из корзины)
                // Этот код удалит элемент корзины из БД.
                Log::warning("Product ID {$cartItem->product_id} not found in database for cart item {$cartItem->id}, removing it from cart.");
                $cartItem->delete(); // Удаляем элемент корзины, у которого нет продукта
                // Нет необходимости вызывать session()->put, так как работаем с БД
                session()->flash('info', 'Один из товаров в вашей корзине больше недоступен и был удален.');
            }
        }
        
        return Inertia::render('Cart', [
            'cart' => array_values($detailedCartItems),
            'total' => $total, // Общая сумма
        ]);
    }

    /**
     * Добавляет товар в корзину или обновляет его количество.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1|max:100'
            ]);

            $product = Product::findOrFail($validated['product_id']);
            $cart = $this->cartService->getCart(); // Получаем корзину до проверки количества

            $currentCartItem = $cart->items()->where('product_id', $product->id)->first();
            $currentQuantityInCart = $currentCartItem ? $currentCartItem->quantity : 0;

            if ($product->quantity_available < ($currentQuantityInCart + $validated['quantity'])) {
                // Используем return back() здесь, так как мы хотим остаться на странице товара
                // и показать ошибку. Но для этого нужно убедиться, что props.cartCount
                // в хедере обновляется через Shared Data.
                return back()->withErrors([
                    'message' => 'Недостаточно товара "' . $product->name . '" на складе. Доступно: ' . $product->quantity_available . '. В корзине: ' . $currentQuantityInCart
                ])->withInput();
            }

            $this->cartService->addProduct($product, $validated['quantity']);

            $updatedCart = $this->cartService->getCart(); // Получаем обновленную корзину
            $cartTotalUnitsCount = $updatedCart->items->sum('quantity');

            Log::info('Product added to cart (DB)', [
                'product_id' => $product->id,
                'quantity_added' => $validated['quantity'],
                'cart_id' => $updatedCart->id,
                'cart_item_new_quantity' => ($currentCartItem ? $currentCartItem->quantity : 0) + $validated['quantity'],
                'cart_total_items_count' => $updatedCart->items->count(),
                'cart_total_units_count' => $cartTotalUnitsCount
            ]);

            // *** Ключевое изменение: Редирект на страницу корзины после успешного добавления ***
            // Это гарантирует, что Inertia сделает новый GET запрос на /cart
            // и CartController@index загрузит свежие данные.
            return redirect()->route('cart.index')->with([
                'success' => 'Товар "' . $product->name . '" добавлен в корзину!',
                'cartCount' => $cartTotalUnitsCount // Передаем для обновления глобального счетчика
            ]);

        } catch (\Exception $e) {
            Log::error('Cart store error (DB): ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'message' => 'Произошла ошибка при добавлении в корзину. Пожалуйста, попробуйте позже.'
            ])->withInput();
        }
    }


    /**
     * Обновляет количество товара в корзине.
     */
    public function update(Request $request, Product $product) // Изменил $id на Product $product для Route Model Binding
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:0', // 0 для удаления
            ]);

            $requestedQuantity = $request->quantity;

            // Если количество 0, это означает удаление
            if ($requestedQuantity === 0) {
                return $this->remove($product->id); // Переиспользуем метод удаления
            }

            // Проверка доступности (логика из вашего контроллера)
            if ($product->quantity_available < $requestedQuantity) {
                return redirect()->back()->withErrors(['message' => 'Недостаточно товара на складе для обновления. Доступно: ' . $product->quantity_available]);
            }

            // Используем метод сервиса для обновления количества
            $success = $this->cartService->updateProductQuantity($product, $requestedQuantity);

            if ($success) {
                $cartTotalUnitsCount = $this->cartService->getCart()->items->sum('quantity');

                Log::info('Cart item updated (DB)', [
                    'product_id' => $product->id,
                    'new_quantity' => $requestedQuantity,
                    'cart_total_units_count' => $cartTotalUnitsCount
                ]);
                return redirect()->back()->with('success', 'Количество товара "' . $product->name . '" обновлено.')->with('cartCount', $cartTotalUnitsCount);
            } else {
                return redirect()->back()->with('info', 'Товар не найден в корзине для обновления.');
            }

        } catch (\Exception $e) {
            Log::error('Cart update error (DB): ' . $e->getMessage(), [
                'product_id' => $product->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['message' => 'Произошла ошибка при обновлении корзины. Пожалуйста, попробуйте позже.']);
        }
    }

    /**
     * Удаляет товар из корзины.
     */
    public function remove(Product $product) // Изменил $id на Product $product для Route Model Binding
    {
        try {
            $productName = $product->name; // Получаем имя товара до удаления, если product существует

            $success = $this->cartService->removeProduct($product);

            if ($success) {
                $cartTotalUnitsCount = $this->cartService->getCart()->items->sum('quantity');

                Log::info('Cart item removed (DB)', [
                    'product_id' => $product->id,
                    'cart_total_units_count' => $cartTotalUnitsCount
                ]);
                return redirect()->back()->with('success', 'Товар "' . $productName . '" удален из корзины!')->with('cartCount', $cartTotalUnitsCount);
            } else {
                return redirect()->back()->with('info', 'Товар уже был удален или не найден в корзине.');
            }
        } catch (\Exception $e) {
            Log::error('Cart remove error (DB): ' . $e->getMessage(), [
                'product_id' => isset($product) ? $product->id : 'unknown', // Добавлено isset
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['message' => 'Произошла ошибка при удалении товара из корзины. Пожалуйста, попробуйте позже.']);
        }
    }
}