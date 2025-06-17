<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Добавляем DB для транзакций

class CartService
{
    // ИЗМЕНЕНО: Сделаем getCart() публичным
    public function getCart(bool $createIfNotExists = true): ?Cart
    {
        $cart = null;

        if (Auth::check()) {
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['guest_token' => null] // У авторизованного пользователя не должно быть гостевого токена
            );
            Log::info('CartService: Retrieved/Created user cart.', ['user_id' => Auth::id(), 'cart_id' => $cart->id]);
        } else {
            $guestToken = Cookie::get('guest_cart_token');

            if ($guestToken) {
                $cart = Cart::where('guest_token', $guestToken)->whereNull('user_id')->first();
                if ($cart) {
                    Log::info('CartService: Retrieved guest cart.', ['guest_token' => $guestToken, 'cart_id' => $cart->id]);
                } else {
                    Log::warning('CartService: Guest cart token found but cart not in DB. Clearing token.', ['guest_token' => $guestToken]);
                    Cookie::queue(Cookie::forget('guest_cart_token')); // Очищаем битый токен
                }
            }

            if (!$cart && $createIfNotExists) {
                $guestToken = Str::uuid()->toString();
                $cart = Cart::create([
                    'guest_token' => $guestToken,
                    'user_id' => null,
                ]);
                // Устанавливаем куку со сроком жизни, например, 30 дней
                Cookie::queue('guest_cart_token', $guestToken, 60 * 24 * 30);
                Log::info('CartService: Created new guest cart and token.', ['guest_token' => $guestToken, 'cart_id' => $cart->id]);
            } elseif (!$cart && !$createIfNotExists) {
                Log::info('CartService: No cart found and createIfNotExists is false.');
            }
        }
        return $cart;
    }

    public function addProduct(Product $product, int $quantity = 1): CartItem
    {
        Log::info('CartService: addProduct called', ['product_id' => $product->id, 'quantity' => $quantity]);

        // Используем транзакцию для атомарности операций с корзиной и продуктом
        return DB::transaction(function () use ($product, $quantity) {
            $cart = $this->getCart();

            if (!$cart) {
                throw new \Exception('Не удалось получить или создать корзину.');
            }

            $cartItem = $cart->items()->firstOrNew(['product_id' => $product->id]);

            if (!$cartItem->exists) {
                $cartItem->price_at_addition = $product->price;
            }

            $currentQuantityInCart = $cartItem->exists ? $cartItem->quantity : 0;
            $newTotalQuantityInCart = $currentQuantityInCart + $quantity;

            // Перед уменьшением, убедимся, что есть достаточно запаса для добавления
            // Используем $product->quantity для проверки текущего запаса в БД
            $product->refresh(); // Убедимся, что у нас самые свежие данные о продукте
            if ($product->quantity < $quantity) {
                Log::error('CartService: Not enough stock for product (addProduct check)', [
                    'product_id' => $product->id,
                    'available_in_db' => $product->quantity,
                    'requested_add' => $quantity
                ]);
                throw new \Exception('Недостаточно товара "' . $product->name . '" на складе. Доступно: ' . $product->quantity);
            }

            // Уменьшаем количество товара на складе (используем РЕАЛЬНОЕ ПОЛЕ 'quantity')
            $product->decrement('quantity', $quantity);
            $product->refresh(); // Получаем обновленное количество

            Log::info('CartService: Product quantity decremented in DB (addProduct)', [
                'product_id' => $product->id,
                'quantity_decremented_by' => $quantity,
                'new_quantity_in_db' => $product->quantity,
            ]);

            $cartItem->quantity = $newTotalQuantityInCart;
            $cart->items()->save($cartItem);

            Log::info('CartService: Product added to cart successfully.', [
                'product_id' => $product->id,
                'quantity_added' => $quantity,
                'cart_item_quantity' => $cartItem->quantity,
                'cart_id' => $cart->id
            ]);

            return $cartItem;
        });
    }


    public function updateProductQuantity(Product $product, int $newQuantity): bool
    {
        Log::info('CartService: updateProductQuantity called', ['product_id' => $product->id, 'new_quantity' => $newQuantity]);

        return DB::transaction(function () use ($product, $newQuantity) {
            $cart = $this->getCart(false); // Не создаем новую корзину, если ее нет

            if (!$cart) {
                Log::warning('CartService: updateProductQuantity - No cart found for user/guest.', ['product_id' => $product->id]);
                return false;
            }

            $cartItem = $cart->items()->where('product_id', $product->id)->first();

            if (!$cartItem) {
                Log::warning('CartService: updateProductQuantity - Cart item not found', ['product_id' => $product->id]);
                return false;
            }

            $oldQuantityInCart = $cartItem->quantity;
            $quantityDifference = $newQuantity - $oldQuantityInCart; // Разница: положительная = добавляем, отрицательная = уменьшаем

            if ($quantityDifference == 0) {
                Log::info('CartService: updateProductQuantity - Quantity is the same, no action needed.', ['product_id' => $product->id, 'quantity' => $newQuantity]);
                return true;
            }

            // Обновляем модель продукта, чтобы получить её актуальное состояние из БД
            $product->refresh();

            Log::info('CartService: Before stock adjustment for update quantity', [
                'product_id' => $product->id,
                'current_product_quantity_in_db' => $product->quantity, // Используем реальное поле
                'old_quantity_in_cart' => $oldQuantityInCart,
                'new_quantity_in_cart_requested' => $newQuantity,
                'quantity_difference' => $quantityDifference
            ]);

            if ($quantityDifference > 0) { // Если пытаемся увеличить количество в корзине
                if ($product->quantity < $quantityDifference) { // Используем реальное поле `quantity`
                    Log::error('CartService: Not enough stock for update quantity (increase)', [
                        'product_id' => $product->id,
                        'available_in_db' => $product->quantity,
                        'needed_additional' => $quantityDifference
                    ]);
                    throw new \Exception('Недостаточно товара "' . $product->name . '" на складе для увеличения количества. Доступно: ' . $product->quantity);
                }
                $product->decrement('quantity', $quantityDifference);
                Log::info('CartService: Product quantity decremented in DB (updateProductQuantity increase)', [
                    'product_id' => $product->id,
                    'quantity_decremented_by' => $quantityDifference,
                ]);
            } elseif ($quantityDifference < 0) { // Если пытаемся уменьшить количество в корзине
                $product->increment('quantity', abs($quantityDifference));
                Log::info('CartService: Product quantity incremented in DB (updateProductQuantity decrease)', [
                    'product_id' => $product->id,
                    'quantity_incremented_by' => abs($quantityDifference),
                ]);
            }

            $product->refresh(); // Получаем обновленное количество продукта

            $cartItem->quantity = $newQuantity;
            $cartItem->save();

            // Если количество в корзине стало 0 или меньше, удаляем элемент корзины
            if ($cartItem->quantity <= 0) {
                $cartItem->delete();
                Log::info('CartService: Cart item removed due to zero/negative quantity.', ['product_id' => $product->id]);
            }

            Log::info('CartService: After stock adjustment for update quantity', [
                'product_id' => $product->id,
                'final_product_quantity_in_db' => $product->quantity, // Используем реальное поле
                'final_cart_item_quantity' => $cartItem->quantity ?? 0
            ]);

            return true;
        });
    }

    public function removeProduct(Product $product): bool
    {
        Log::info('CartService: removeProduct called', ['product_id' => $product->id]);

        return DB::transaction(function () use ($product) {
            $cart = $this->getCart(false);

            if (!$cart) {
                Log::warning('CartService: removeProduct - No cart found for user/guest.', ['product_id' => $product->id]);
                return false;
            }

            $cartItem = $cart->items()->where('product_id', $product->id)->first();

            if ($cartItem) {
                $removedQuantity = $cartItem->quantity;
                $cartItem->delete();

                // Возвращаем количество товара на склад
                $product->increment('quantity', $removedQuantity);
                $product->refresh(); // Получаем обновленное количество

                Log::info('CartService: Product removed from cart, quantity returned to stock.', [
                    'product_id' => $product->id,
                    'removed_quantity' => $removedQuantity,
                    'final_product_quantity_in_db' => $product->quantity,
                ]);
                return true;
            }

            Log::warning('CartService: removeProduct - Cart item not found for removal', ['product_id' => $product->id]);
            return false;
        });
    }

    public function clearCart()
    {
        $cart = $this->getCart(false); // Не создаем новую корзину, если ее нет

        if ($cart && $cart->exists) {
            // Перед удалением элементов корзины, возвращаем товар на склад
            foreach ($cart->items as $item) {
                try {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('quantity', $item->quantity);
                        $product->save();
                        Log::info('CartService: Returned stock for product during clearCart.', [
                            'product_id' => $product->id,
                            'quantity_returned' => $item->quantity,
                            'final_product_quantity_in_db' => $product->fresh()->quantity,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('CartService: Failed to return stock for product during clearCart.', [
                        'product_id' => $item->product_id,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }

            $cart->items()->delete();
            $cart->delete();

            // Очищаем куку гостя, если она есть
            if (Cookie::has('guest_cart_token')) {
                Cookie::queue(Cookie::forget('guest_cart_token'));
                Log::info('CartService: Guest cart token cleared after cart cleared.');
            }
            Log::info('CartService: Cart cleared and deleted.');
        } else {
            Log::info('CartService: No cart found to clear.');
        }
    }

    public function mergeGuestCartToUser(int $userId): void
    {
        Log::info('CartService: mergeGuestCartToUser called', ['user_id' => $userId]);

        $guestToken = Cookie::get('guest_cart_token');

        if (!$guestToken) {
            Log::info('CartService: No guest cart token found, no merge needed.');
            return;
        }

        $guestCart = Cart::where('guest_token', $guestToken)->whereNull('user_id')->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            Log::info('CartService: No guest cart or guest cart is empty to merge.', ['guest_token' => $guestToken]);
            Cookie::queue(Cookie::forget('guest_cart_token'));
            return;
        }

        Log::info('CartService: Guest cart found for merging.', [
            'guest_cart_id' => $guestCart->id,
            'guest_cart_items_count' => $guestCart->items->count()
        ]);

        // Используем транзакцию для слияния корзин
        DB::transaction(function () use ($userId, $guestCart) {
            $userCart = Cart::firstOrCreate(
                ['user_id' => $userId],
                ['guest_token' => null]
            );

            Log::info('CartService: User cart retrieved/created for merging.', ['user_cart_id' => $userCart->id]);

            foreach ($guestCart->items as $guestItem) {
                $product = Product::find($guestItem->product_id); // Получаем актуальные данные о продукте
                if (!$product) {
                    Log::warning('CartService: Product not found during guest cart merge, skipping item and deleting guest item.', ['guest_cart_item_id' => $guestItem->id, 'product_id' => $guestItem->product_id]);
                    $guestItem->delete();
                    continue;
                }

                $userCartItem = $userCart->items()->firstOrNew(['product_id' => $guestItem->product_id]);

                $oldUserQuantity = $userCartItem->exists ? $userCartItem->quantity : 0;
                $quantityToAddFromGuest = $guestItem->quantity;

                if (!$userCartItem->exists) {
                    $userCartItem->price_at_addition = $product->price;
                }

                $availableOnStock = $product->quantity; // Текущий запас
                $totalNeeded = $oldUserQuantity + $quantityToAddFromGuest;

                if ($totalNeeded > $availableOnStock) {
                    $canAddFromGuest = max(0, $availableOnStock - $oldUserQuantity);
                    if ($canAddFromGuest < $quantityToAddFromGuest) {
                        Log::warning('CartService: Adjusting guest quantity during merge due to insufficient stock.', [
                            'product_id' => $product->id,
                            'guest_quantity_original' => $guestItem->quantity,
                            'can_add' => $canAddFromGuest,
                            'available_on_stock' => $availableOnStock,
                            'old_user_quantity' => $oldUserQuantity,
                        ]);
                        session()->flash('info', 'Количество некоторых товаров из вашей гостевой корзины было скорректировано из-за их отсутствия на складе.');
                    }
                    $quantityToAddFromGuest = $canAddFromGuest;
                }

                if ($quantityToAddFromGuest > 0) {
                    $userCartItem->quantity += $quantityToAddFromGuest;
                    $userCartItem->save();

                    Log::info('CartService: Merged item quantity to user cart.', [
                        'product_id' => $product->id,
                        'old_user_quantity' => $oldUserQuantity,
                        'new_user_quantity' => $userCartItem->quantity,
                        'quantity_added_from_guest' => $quantityToAddFromGuest
                    ]);
                }

                $returnedToStock = $guestItem->quantity - $quantityToAddFromGuest;
                if ($returnedToStock > 0) {
                    $product->increment('quantity', $returnedToStock);
                    $product->save();
                    Log::info('CartService: Returned excess stock from guest cart during merge.', [
                        'product_id' => $product->id,
                        'returned_quantity' => $returnedToStock,
                        'final_product_quantity_in_db' => $product->fresh()->quantity,
                    ]);
                }

                $guestItem->delete(); // Удаляем элемент из гостевой корзины
            }

            $guestCart->delete(); // Удаляем саму гостевую корзину
            Cookie::queue(Cookie::forget('guest_cart_token')); // Очищаем куку гостя
            Log::info('CartService: Guest cart merged and deleted, guest token cleared.', ['guest_cart_id' => $guestCart->id, 'user_id' => $userId]);
        });
    }
}