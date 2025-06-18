<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Inertia\Response;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(): Response
    {
        $cart = $this->cartService->getCart();

        Log::info('Cart content from DB on index page LOAD:', ['cart_id' => $cart->id, 'items_count' => $cart->items->count()]);
        Log::info('Session ID on index page LOAD (for context):', ['id' => session()->getId()]);

        $detailedCartItems = [];
        $total = 0;

        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;

            if ($product) {
                $imageUrl = $product->main_image_url; 
                
                $detailedCartItems[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $imageUrl,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'slug' => $product->slug, 
                    'max_available' => $product->quantity_available,
                ];
                $total += $product->price * $cartItem->quantity;
            } else {
                Log::warning("Product ID {$cartItem->product_id} not found in database for cart item {$cartItem->id}, removing it from cart.");
                $cartItem->delete();
                session()->flash('info', 'Один из товаров в вашей корзине больше недоступен и был удален.');
            }
        }
        
        return Inertia::render('Cart', [
            'cart' => array_values($detailedCartItems),
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1|max:100'
            ]);

            $product = Product::findOrFail($validated['product_id']);
            $cart = $this->cartService->getCart();

            $currentCartItem = $cart->items()->where('product_id', $product->id)->first();
            $currentQuantityInCart = $currentCartItem ? $currentCartItem->quantity : 0;

            if ($product->quantity_available < ($currentQuantityInCart + $validated['quantity'])) {
                return back()->withErrors([
                    'message' => 'Недостаточно товара "' . $product->name . '" на складе. Доступно: ' . $product->quantity_available . '. В корзине: ' . $currentQuantityInCart
                ])->withInput();
            }

            $this->cartService->addProduct($product, $validated['quantity']);

            $updatedCart = $this->cartService->getCart();
            $cartTotalUnitsCount = $updatedCart->items->sum('quantity');

            Log::info('Product added to cart (DB)', [
                'product_id' => $product->id,
                'quantity_added' => $validated['quantity'],
                'cart_id' => $updatedCart->id,
                'cart_item_new_quantity' => ($currentCartItem ? $currentCartItem->quantity : 0) + $validated['quantity'],
                'cart_total_items_count' => $updatedCart->items->count(),
                'cart_total_units_count' => $cartTotalUnitsCount
            ]);

            return redirect()->route('cart.index')->with([
                'success' => 'Товар "' . $product->name . '" добавлен в корзину!',
                'cartCount' => $cartTotalUnitsCount
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


    public function update(Request $request, Product $product)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:0',
            ]);

            $requestedQuantity = $request->quantity;

            if ($requestedQuantity === 0) {
                return $this->remove($product->id);
            }

            if ($product->quantity_available < $requestedQuantity) {
                return redirect()->back()->withErrors(['message' => 'Недостаточно товара на складе для обновления. Доступно: ' . $product->quantity_available]);
            }

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

    public function remove(Product $product)
    {
        try {
            $productName = $product->name;

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
                'product_id' => isset($product) ? $product->id : 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['message' => 'Произошла ошибка при удалении товара из корзины. Пожалуйста, попробуйте позже.']);
        }
    }
}