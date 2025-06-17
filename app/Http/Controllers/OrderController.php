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
        Log::info('OrderController@store: Checkout process initiated.');

        if (!Auth::check()) {
            Log::warning('OrderController@store: Attempted checkout by unauthenticated user.');
            return redirect()->route('login')->with('error', 'Для оформления заказа необходимо войти в аккаунт.');
        }

        $user = Auth::user();

        $cart = $this->cartService->getCart();

        Log::info('OrderController@store: Current cart state from CartService', [
            'cart_id' => $cart->id ?? 'N/A',
            'cart_user_id' => $cart->user_id ?? 'N/A',
            'cart_guest_token' => $cart->guest_token ?? 'N/A',
            'cart_items_count' => $cart->items->count(),
            'cart_items_data' => $cart->items->map(fn($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price_at_addition' => $item->price_at_addition
            ])->toArray()
        ]);

        if ($cart->items->isEmpty()) {
            Log::warning('OrderController@store: Attempted checkout with an empty cart (from DB).');
            return redirect()->route('cart.index')->with('error', 'Ваша корзина пуста.');
        }

        $productIdsInCart = $cart->items->pluck('product_id')->unique()->toArray();
        $productsInCartDB = Product::whereIn('id', $productIdsInCart)->get()->keyBy('id');

        foreach ($cart->items as $cartItem) {
            $product = $productsInCartDB->get($cartItem->product_id);

            if (!$product) {
                Log::warning("OrderController@store: Product ID {$cartItem->product_id} not found in database for cart item ID {$cartItem->id}. Removing from cart and redirecting.");
                $cartItem->delete();
                return redirect()->route('cart.index')->with('error', "Один из товаров в вашей корзине ({$cartItem->product_name}?) не найден и был удален. Пожалуйста, проверьте корзину.");
            }

            // Проверка доступного количества с использованием аксессора
            if ($product->quantity_available < $cartItem->quantity) {
                Log::warning("OrderController@store: Insufficient quantity for product {$product->id} ({$product->name}). Available: {$product->quantity_available}, In cart: {$cartItem->quantity}.");
                return redirect()->route('cart.index')->with('error', "Недостаточно товара \"{$product->name}\" на складе. Доступно: {$product->quantity_available}. В корзине: {$cartItem->quantity}");
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

            foreach ($cart->items as $cartItem) {
                $product = $productsInCartDB->get($cartItem->product_id);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'cart' => 'Произошла ошибка: товар в корзине не найден. Пожалуйста, обновите корзину.'
                    ])->redirectTo(route('cart.index'));
                }

                // --- АКТИВИРУЙТЕ И ИСПРАВЬТЕ ЭТУ СТРОКУ ---
                Log::info('OrderController: Before product quantity decrement in transaction', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_to_decrement' => $cartItem->quantity,
                    'current_quantity_in_db_before_decrement' => $product->quantity, // Используйте реальное поле
                ]);

                // *** ВАЖНОЕ ИЗМЕНЕНИЕ: Используем 'quantity' вместо 'quantity_available' ***
                $product->decrement('quantity', $cartItem->quantity); // <-- Вот где должно быть реальное уменьшение

                $product->refresh(); // Обновим модель, чтобы получить свежие данные

                Log::info('OrderController: After product quantity decrement in transaction', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_decremented_by' => $cartItem->quantity,
                    'quantity_in_db_after_decrement_and_refresh' => $product->quantity, // Используем реальное поле
                ]);
                // --- КОНЕЦ ИЗМЕНЕНИЙ ---

                $orderItemsData[] = new OrderItem([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $cartItem->price_at_addition,
                    'quantity' => $cartItem->quantity,
                ]);

                $totalAmount += ($cartItem->price_at_addition * $cartItem->quantity);
            }

            $order->items()->saveMany($orderItemsData);
            $order->total_amount = $totalAmount;
            $order->save();

            $this->cartService->clearCart();

            DB::commit();

            Log::info('OrderController@store: Order successfully created.', ['order_id' => $order->id, 'order_number' => $order->order_number, 'user_id' => $user->id]);

            return redirect()->route('profile.edit')->with('success', 'Заказ №' . $order->order_number . ' успешно оформлен!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage(), [
                'exception_message' => $e->getMessage(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'cart_state_on_error' => $cart->items->map(fn($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity])->toArray(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('cart.index')->with('error', 'Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте еще раз.');
        }
    }

    public function index()
    {
        $userOrders = Auth::user()->orders()
                                   ->with(['items.product'])
                                   ->latest()
                                   ->paginate(10);

        return Inertia::render('Profile/Orders', [
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