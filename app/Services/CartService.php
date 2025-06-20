<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Получает или создает корзину для текущего пользователя (авторизованного или гостя).
     */
    public function getCart(bool $createIfNotExists = true): ?Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $guestToken = Cookie::get('guest_cart_token');
        if ($guestToken) {
            $cart = Cart::where('guest_token', $guestToken)->whereNull('user_id')->first();
            if ($cart) {
                return $cart;
            }
        }

        if (!$createIfNotExists) {
            return null;
        }

        $newGuestToken = Str::uuid()->toString();
        $cart = Cart::create(['guest_token' => $newGuestToken]);
        Cookie::queue('guest_cart_token', $newGuestToken, 60 * 24 * 30); // 30 дней

        return $cart;
    }

    /**
     * Добавляет товар в корзину. НЕ ИЗМЕНЯЕТ количество на складе.
     */
    public function addProduct(Product $product, int $quantity = 1): void
    {
        $cart = $this->getCart();

        $cartItem = $cart->items()->firstOrNew(['product_id' => $product->id]);

        $cartItem->quantity += $quantity;
        
        // Записываем цену на момент добавления, если это новый товар в корзине
        if (!$cartItem->exists) {
            $cartItem->price_at_addition = $product->price;
        }
        
        $cartItem->save();
    }

    /**
     * Обновляет количество товара в корзине. НЕ ИЗМЕНЯЕТ количество на складе.
     */
    public function updateProductQuantity(Product $product, int $newQuantity): bool
    {
        $cart = $this->getCart(false);

        if (!$cart) {
            return false;
        }

        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if (!$cartItem) {
            return false; // Товара нет в корзине
        }

        if ($newQuantity > 0) {
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            // Если количество 0 или меньше, удаляем товар
            $cartItem->delete();
        }
        
        return true;
    }

    /**
     * Удаляет товар из корзины. НЕ ИЗМЕНЯЕТ количество на складе.
     */
    public function removeProduct(Product $product): bool
    {
        $cart = $this->getCart(false);

        if ($cart) {
            // Удаляем товар и возвращаем количество удаленных строк (0 или 1)
            return $cart->items()->where('product_id', $product->id)->delete() > 0;
        }

        return false;
    }

    /**
     * Полностью очищает корзину. НЕ ИЗМЕНЯЕТ количество на складе.
     */
    public function clearCart(): void
    {
        $cart = $this->getCart(false);

        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
            if (Cookie::has('guest_cart_token')) {
                Cookie::queue(Cookie::forget('guest_cart_token'));
            }
        }
    }
    
    /**
     * Объединяет гостевую корзину с корзиной пользователя после входа.
     * НЕ ИЗМЕНЯЕТ количество на складе.
     */
    public function mergeGuestCartToUser(int $userId): void
    {
        $guestToken = Cookie::get('guest_cart_token');
        if (!$guestToken) return;

        $guestCart = Cart::where('guest_token', $guestToken)->with('items')->first();
        if (!$guestCart || $guestCart->items->isEmpty()) return;

        $userCart = Cart::firstOrCreate(['user_id' => $userId]);

        foreach ($guestCart->items as $guestItem) {
            $userCartItem = $userCart->items()->firstOrNew(['product_id' => $guestItem->product_id]);
            $userCartItem->quantity += $guestItem->quantity;

            if (!$userCartItem->exists) {
                // Пытаемся получить актуальную цену, если это новый товар
                $product = Product::find($guestItem->product_id);
                $userCartItem->price_at_addition = $product ? $product->price : $guestItem->price_at_addition;
            }
            
            $userCartItem->save();
        }

        $guestCart->delete(); // Удаляем гостевую корзину после слияния
        Cookie::queue(Cookie::forget('guest_cart_token'));
    }
}