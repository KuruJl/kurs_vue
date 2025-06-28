<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Для оформления заказа необходимо войти в аккаунт.');
        }

        $cart = $this->cartService->getCart();
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Ваша корзина пуста.');
        }

        try {
            // Запускаем транзакцию, чтобы все операции были атомарными
            $order = DB::transaction(function () use ($cart, $user) {

                // 1. Блокируем строки продуктов для обновления, чтобы избежать гонки запросов
                $productIdsInCart = $cart->items->pluck('product_id');
                $products = Product::whereIn('id', $productIdsInCart)->lockForUpdate()->get()->keyBy('id');
                
                // 2. Повторная проверка наличия на складе ВНУТРИ транзакции
                foreach ($cart->items as $cartItem) {
                    $product = $products->get($cartItem->product_id);
                    if (!$product || $product->quantity < $cartItem->quantity) {
                        // Если товара не хватает, выбрасываем исключение, чтобы откатить транзакцию
                        throw new \Exception("Недостаточно товара \"{$product->name}\". На складе: {$product->quantity}, в корзине: {$cartItem->quantity}");
                    }
                }

                // 3. Создаем заказ
                $createdOrder = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . time() . '-' . uniqid(),
                    'total_amount' => 0, // Посчитаем ниже
                    'status' => 'completed',
                ]);

                $totalAmount = 0;

                // 4. Создаем позиции заказа и СПИСЫВАЕМ ТОВАР
                foreach ($cart->items as $cartItem) {
                    $product = $products->get($cartItem->product_id);
                    
                    // Списываем товар со склада
                    $product->decrement('quantity', $cartItem->quantity);

                    $createdOrder->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->price, // Можно брать актуальную цену
                        'quantity' => $cartItem->quantity,
                    ]);

                    $totalAmount += ($product->price * $cartItem->quantity);
                }

                // 5. Обновляем итоговую сумму заказа
                $createdOrder->total_amount = $totalAmount;
                $createdOrder->save();
                
                return $createdOrder;
            });

            // 6. Очищаем корзину только после успешной транзакции
            $this->cartService->clearCart();

            Log::info('Order successfully created and stock updated.', ['order_id' => $order->id]);
            
            // Вместо profile.edit, лучше на страницу "Мои заказы"
            return redirect()->route('profile.edit')->with('success', 'Заказ №' . $order->order_number . ' успешно оформлен!');

        } catch (\Exception $e) {
            // Ловим ошибку (например, нехватка товара)
            Log::error('Order creation failed within transaction: ' . $e->getMessage());
            // Возвращаемся в корзину с понятным сообщением
            return redirect()->route('cart.index')->withErrors(['cart' => $e->getMessage()]);
        }
    }

    public function index()
    {
        $userOrders = Auth::user()->orders()
                                   ->with(['items.product'])
                                   ->latest()
                                   ->paginate(10);

        return Inertia::render('Profile/Edit', [
            'userOrders' => $userOrders->through(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => __('statuses.' . $order->status),
                'created_at' => $order->created_at->format('d.m.Y H:i'),
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'product_image_url' => $item->product ? $item->product->main_image_url : asset('images/default_product.png'),
                    'product_slug' => $item->product->slug ?? null,
                ])
            ])->toArray()
        ]);
    }
    
}