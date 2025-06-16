<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{
    /**
     * Обрабатывает создание нового заказа.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Проверяем, авторизован ли пользователь
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Для оформления заказа необходимо войти в аккаунт.');
        }

        $user = auth()->user();
        $cart = session()->get('cart', []);

        Log::info('OrderController@store: Cart content from session', $cart);
        Log::info('OrderController@store: Request data', $request->all());

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Ваша корзина пуста.');
        }

        // Проверяем наличие товаров на складе перед началом транзакции
        $productQuantitiesInCart = [];
        // ВАЖНО: Если корзина в сессии имеет числовые индексы (0, 1, 2), как было в вашем dd(),
        // то этот цикл не будет работать корректно, потому что $productId будет 0, 1, 2...
        // Убедитесь, что CartController.php сохраняет product_id как ключ!
        foreach ($cart as $productId => $itemDetails) {
            // Если $productId в $cart - это реальный ID продукта:
            $productQuantitiesInCart[$productId] = $itemDetails['quantity'];
            // Если $productId в $cart - это числовой индекс (0, 1, 2), то это неправильно
            // и нужно будет пересмотреть, как корзина формируется в сессии.
            // Но исходя из моих последних исправлений CartController, теперь $productId должен быть верным.
        }

        $productsInCart = Product::whereIn('id', array_keys($productQuantitiesInCart))->get()->keyBy('id');

        foreach ($productQuantitiesInCart as $productId => $quantityInCart) {
            $product = $productsInCart->get($productId);

            if (!$product) {
                Log::warning("OrderController@store: Product ID {$productId} not found in database while checking quantity.");
                return redirect()->route('cart.index')->with('error', "Один из товаров в вашей корзине не найден.");
            }

            // ИСПРАВЛЕНИЕ: Используем поле 'quantity' для проверки количества.
            // Если хотите использовать аксессор, это будет $product->quantity_available.
            // Для прямой работы с полем БД, используйте $product->quantity. Оба варианта подойдут,
            // но важно быть последовательным. Я использую $product->quantity здесь.
            if ($product->quantity < $quantityInCart) {
                Log::warning("OrderController@store: Insufficient quantity for product {$product->id} ({$product->name}). Available: {$product->quantity}, In cart: {$quantityInCart}.");
                return redirect()->route('cart.index')->with('error', "Недостаточно товара \"{$product->name}\" на складе. Доступно: {$product->quantity}. В корзине: {$quantityInCart}");
            }
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . time() . '-' . uniqid(),
                'total_amount' => 0,
                'status' => 'pending',
            ]);

            $totalAmount = 0;
            $orderItemsData = [];
            
            // Если cart в сессии был ассоциативным массивом (product_id => itemDetails), то этот цикл правильный.
            foreach ($cart as $productId => $itemDetails) { 
                $product = $productsInCart->get($productId);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'cart' => 'Один из товаров в корзине не найден (ошибка обработки).'
                    ])->redirectTo(route('cart.index'));
                }

                // ИСПРАВЛЕНИЕ: Уменьшаем 'quantity'
                $product->decrement('quantity', $itemDetails['quantity']); 

                $orderItemsData[] = new OrderItem([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $itemDetails['quantity'],
                ]);
                $totalAmount += ($product->price * $itemDetails['quantity']);
            }

            $order->items()->saveMany($orderItemsData);
            $order->total_amount = $totalAmount;
            $order->save();

            session()->forget('cart');

            DB::commit();

            Log::info('OrderController@store: Order successfully created', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            return redirect()->route('profile.edit')->with('success', 'Заказ №' . $order->order_number . ' успешно оформлен!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage(), [
                'exception_message' => $e->getMessage(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'cart_state_on_error' => $cart
            ]);

            return redirect()->route('cart.index')->with('error', 'Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте еще раз.');
        }
    }

    /**
     * Отображает историю заказов пользователя.
     */
    public function index()
    {
        $userOrders = Auth::user()->orders()
                           ->with(['items.product']) // Загружаем товары и их продукты
                           ->latest()
                           ->paginate(10);

        return Inertia::render('Profile/Orders', [ // Убедитесь, что это правильный путь к компоненту для истории заказов
            'userOrders' => $userOrders->through(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => __('statuses.' . $order->status), // <-- ПЕРЕВОД СТАТУСА ЗДЕСЬ
                'created_at' => $order->created_at->format('d.m.Y H:i'),
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'product_image_url' => $item->product ? $item->product->main_image_url : asset('images/default_product.png'),
                    'product_slug' => $item->product->slug ?? null,
                ])
            ])
        ]);
    }
}