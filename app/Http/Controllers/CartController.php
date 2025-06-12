<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product; // Убедитесь, что модель Product импортирована
use Illuminate\Support\Facades\Log; // Импортируем фасад Log

class CartController extends Controller
{
    /**
     * Отображает содержимое корзины.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        Log::info('Cart content from session (raw) on index page LOAD:', $cart);
        Log::info('Session ID on index page LOAD:', ['id' => session()->getId()]);

        $cartDetails = [];
        foreach ($cart as $id => $details) {
            // Убедитесь, что все необходимые поля присутствуют
            $cartDetails[] = [ // <--- ИЗМЕНЕНИЕ ЗДЕСЬ: используем [] для добавления как элемент массива
                'id' => $id, // сохраняем ID внутри элемента, а не как ключ
                'name' => $details['name'],
                'image' => $details['image'],
                'price' => $details['price'],
                'quantity' => $details['quantity'],
            ];
        }

        // ОЧЕНЬ ВАЖНОЕ ИЗМЕНЕНИЕ:
        // Используем array_values() чтобы гарантировать, что это будет числовой индексированный массив
        // иначе JSON.encode преобразует его в объект, а Vue ожидает массив.
        $cartForVue = array_values($cartDetails);

        return Inertia::render('Cart', [
            'cart' => $cartForVue, // Передаем теперь корректный массив
            'total' => array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $cartForVue)), // Используем $cartForVue для подсчета total
        ]);
    }

    /**
     * Добавляет товар в корзину или обновляет его количество.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Находим продукт по ID. Аксессоры image_url и quantity_available
        // будут автоматически доступны благодаря $appends в модели Product.
        $product = Product::find($request->product_id);

        if (!$product) {
             return redirect()->back()->withErrors(['message' => 'Товар не найден.']);
        }

        Log::info('Product found for cart (in store method):', [
            'id' => $product->id,
            'name' => $product->name,
            'image_url' => $product->image_url,
            'quantity_available' => $product->quantity_available,
        ]);

        $cart = session()->get('cart', []);
        Log::info('Cart BEFORE update in store:', $cart); // Логируем корзину до добавления

        // Проверяем наличие на складе перед добавлением
        $currentQuantityInCart = isset($cart[$product->id]) ? $cart[$product->id]['quantity'] : 0;
        if ($product->quantity_available < ($request->quantity + $currentQuantityInCart)) {
             return redirect()->back()->withErrors([
                 'message' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity_available
             ]);
        }

        if (isset($cart[$product->id])) {
            // Товар уже в корзине, увеличиваем количество
            $cart[$product->id]['quantity'] += $request->quantity;
        } else {
            // Товар еще не в корзине, добавляем его
            $cart[$product->id] = [
                "name" => $product->name,
                "quantity" => $request->quantity,
                "price" => $product->price,
                "image" => $product->image_url, // Используем аксессор, который теперь должен быть корректным
            ];
        }

        session()->put('cart', $cart); // Сохраняем обновленную корзину в сессии
        Log::info('Cart AFTER update in store:', $cart); // Логируем корзину после добавления
        Log::info('Session ID AFTER store (should be the same):', ['id' => session()->getId()]); // Логируем ID сессии

        return redirect()->back()->with('success', 'Товар успешно добавлен в корзину!');
    }

    /**
     * Обновляет количество товара в корзине.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0', // Количество может быть 0 для удаления
        ]);

        $cart = session()->get('cart');

        if (isset($cart[$id])) {
            if ($request->quantity > 0) {
                // Проверка на наличие на складе при обновлении
                $product = Product::find($id);
                if ($product && $product->quantity_available < $request->quantity) {
                    return redirect()->back()->withErrors(['message' => 'Недостаточно товара на складе для обновления. Доступно: ' . $product->quantity_available]);
                }
                $cart[$id]['quantity'] = $request->quantity;
                session()->put('cart', $cart);
                session()->flash('success', 'Корзина обновлена!');
            } else {
                // Если количество 0, удаляем товар
                unset($cart[$id]);
                session()->put('cart', $cart);
                session()->flash('success', 'Товар удален из корзины!');
            }
        }
        return redirect()->back();
    }

    /**
     * Удаляет товар из корзины.
     */
    public function remove($id)
    {
        $cart = session()->get('cart');

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
            session()->flash('success', 'Товар удален из корзины!');
        }
        return redirect()->back();
    }
}