<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
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
        $detailedCartItems = [];
        $total = 0;

        if ($cart) {
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                if ($product) {
                    $detailedCartItems[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->main_image_url,
                        'price' => $product->price,
                        'quantity' => $cartItem->quantity,
                        'slug' => $product->slug,
                        'max_available' => $product->quantity, // <-- Используем реальное поле quantity
                    ];
                    $total += $product->price * $cartItem->quantity;
                } else {
                    $cartItem->delete();
                    session()->flash('info', 'Один из товаров в вашей корзине больше недоступен и был удален.');
                }
            }
        }
        
        return Inertia::render('Cart', [
            'cart' => $detailedCartItems,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $cart = $this->cartService->getCart();

        $currentCartItem = $cart->items()->where('product_id', $product->id)->first();
        $currentQuantityInCart = $currentCartItem ? $currentCartItem->quantity : 0;
        $totalQuantityNeeded = $currentQuantityInCart + $validated['quantity'];

        // Проверяем, достаточно ли товара на складе
        if ($product->quantity < $totalQuantityNeeded) {
            return back()->withErrors([
                'message' => "Недостаточно товара \"{$product->name}\". На складе: {$product->quantity}, в корзине уже: {$currentQuantityInCart}."
            ]);
        }

        $this->cartService->addProduct($product, $validated['quantity']);

        return redirect()->route('cart.index')->with('success', 'Товар "' . $product->name . '" добавлен в корзину!');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate(['quantity' => 'required|integer|min:0']);

        $requestedQuantity = $validated['quantity'];

        // Проверяем наличие на складе ПЕРЕД обновлением
        if ($product->quantity < $requestedQuantity) {
            return redirect()->back()->withErrors([
                'message' => "Нельзя добавить больше, чем есть на складе. Доступно: {$product->quantity} шт."
            ]);
        }

        $this->cartService->updateProductQuantity($product, $requestedQuantity);

        return redirect()->back()->with('success', 'Количество товара обновлено.');
    }

    public function remove(Product $product)
    {
        $this->cartService->removeProduct($product);
        return redirect()->back()->with('success', 'Товар "' . $product->name . '" удален из корзины!');
    }
}