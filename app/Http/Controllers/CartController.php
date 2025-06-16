<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Отображает содержимое корзины.
     */
    public function index(): Response
    {
        $cart = session()->get('cart', []);
        Log::info('Cart content from session (raw) on index page LOAD:', $cart);
        Log::info('Session ID on index page LOAD:', ['id' => session()->getId()]);

        $detailedCartItems = [];
        $total = 0;

        foreach ($cart as $id => $details) {
            $product = Product::find($id); 
            
            if ($product) {
                $imageUrl = $product->main_image_url; 
                
                $detailedCartItems[] = [
                    'id' => $id,
                    'name' => $product->name,
                    'image' => $imageUrl,
                    'price' => $product->price,
                    'quantity' => $details['quantity'],
                    'slug' => $product->slug, 
                    'max_available' => $product->quantity_available, // Используем аксессор quantity_available
                ];
                $total += $product->price * $details['quantity'];
            } else {
                Log::warning("Product ID {$id} not found in database for cart, removing it from session.");
                unset($cart[$id]);
                session()->put('cart', $cart);
                session()->flash('info', 'Один из товаров в вашей корзине больше недоступен и был удален.');
            }
        }
        
        return Inertia::render('Cart', [
            'cart' => array_values($detailedCartItems),
            'total' => $total,
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
            $cart = session()->get('cart', []);
            
            $currentQuantityInCart = $cart[$product->id]['quantity'] ?? 0;
            $requestedQuantityToAdd = $validated['quantity'];

            // ИСПРАВЛЕНИЕ: Используем аксессор quantity_available для проверки доступности
            if ($product->quantity_available < ($currentQuantityInCart + $requestedQuantityToAdd)) {
                return back()->withErrors([
                    'message' => 'Недостаточно товара "' . $product->name . '" на складе. Доступно: ' . $product->quantity_available . '. В корзине: ' . $currentQuantityInCart
                ])->withInput();
            }

            if (isset($cart[$product->id])) {
                $cart[$product->id]['quantity'] += $requestedQuantityToAdd;
            } else {
                $cart[$product->id] = [
                    "name" => $product->name,
                    "quantity" => $requestedQuantityToAdd,
                    "price" => $product->price,
                    "image" => $product->main_image_url, 
                    "max_available" => $product->quantity_available // ИСПРАВЛЕНИЕ: Используем аксессор quantity_available
                ];
            }

            session()->put('cart', $cart);

            Log::info('Product added to cart', [
                'product_id' => $product->id,
                'quantity_added' => $requestedQuantityToAdd,
                'cart_item_new_quantity' => $cart[$product->id]['quantity'],
                'cart_total_items_count' => count($cart),
                'cart_total_units_count' => array_sum(array_column($cart, 'quantity'))
            ]);

            return back()->with([
                'success' => 'Товар "' . $product->name . '" добавлен в корзину!',
                'cartCount' => array_sum(array_column($cart, 'quantity'))
            ]);

        } catch (\Exception $e) {
            Log::error('Cart store error: ' . $e->getMessage(), [
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
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:0',
            ]);

            $cart = session()->get('cart', []);
            $product = Product::find($id);

            if (!$product) {
                if (isset($cart[$id])) {
                    unset($cart[$id]);
                    session()->put('cart', $cart);
                    session()->flash('error', 'Товар не найден и удален из корзины.');
                } else {
                    session()->flash('info', 'Товар уже отсутствовал в корзине.');
                }
                return redirect()->back();
            }

            if (isset($cart[$id])) {
                $requestedQuantity = $request->quantity;

                if ($requestedQuantity > 0) {
                    // ИСПРАВЛЕНИЕ: Используем аксессор quantity_available для проверки доступности
                    if ($product->quantity_available < $requestedQuantity) {
                        return redirect()->back()->withErrors(['message' => 'Недостаточно товара на складе для обновления. Доступно: ' . $product->quantity_available]);
                    }
                    $cart[$id]['quantity'] = $requestedQuantity;
                    // ИСПРАВЛЕНИЕ: Обновляем max_available, используя аксессор quantity_available
                    $cart[$id]['max_available'] = $product->quantity_available; 
                    session()->put('cart', $cart);
                    session()->flash('success', 'Количество товара "' . $product->name . '" обновлено.');
                } else {
                    unset($cart[$id]);
                    session()->put('cart', $cart);
                    session()->flash('success', 'Товар "' . $product->name . '" удален из корзины!');
                }
            } else {
                session()->flash('info', 'Товар не найден в корзине для обновления.');
            }
            
            Log::info('Cart item updated', [
                'product_id' => $id,
                'new_quantity' => $request->quantity,
                'cart_total_units_count' => array_sum(array_column($cart, 'quantity'))
            ]);

            return redirect()->back();

        } catch (\Exception $e) {
            Log::error('Cart update error: ' . $e->getMessage(), [
                'product_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['message' => 'Произошла ошибка при обновлении корзины. Пожалуйста, попробуйте позже.']);
        }
    }

    /**
     * Удаляет товар из корзины.
     */
    public function remove($id)
    {
        try {
            $cart = session()->get('cart', []);
            $productName = 'товар';

            if (isset($cart[$id])) {
                $product = Product::find($id);
                if ($product) {
                    $productName = $product->name;
                }
                unset($cart[$id]);
                session()->put('cart', $cart);
                session()->flash('success', 'Товар "' . $productName . '" удален из корзины!');

                Log::info('Cart item removed', [
                    'product_id' => $id,
                    'cart_total_units_count' => array_sum(array_column($cart, 'quantity'))
                ]);
            } else {
                session()->flash('info', 'Товар уже был удален или не найден в корзине.');
            }
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Cart remove error: ' . $e->getMessage(), [
                'product_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['message' => 'Произошла ошибка при удалении товара из корзины. Пожалуйста, попробуйте позже.']);
        }
    }
}