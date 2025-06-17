<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order; // Импортируем модель Order
use App\Models\User;  // Импортируем модель User для eager loading (связь с заказом)
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule; // Для валидации статуса

class OrderController extends Controller
{

  public function __construct()
    {
        // ПРАВИЛЬНЫЙ СПОСОБ использования уже зарегистрированного middleware 'is_admin'
        // Этот middleware (App\Http\Middleware\CheckAdmin) уже содержит логику проверки прав администратора.
        $this->middleware('is_admin');
    }
    // Список доступных статусов заказа.
    // Рекомендуется вынести это в Enum (если Laravel 9+) или отдельный файл конфигурации
    // для большей чистоты и возможности перевода.
    const AVAILABLE_STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    /**
     * Отображает список всех заказов для административной панели.
     */
    public function index()
    {
        // Загружаем заказы с информацией о пользователе, который их сделал.
        // Используем latest() для сортировки от новых к старым.
        // paginate() для постраничного вывода, чтобы не загружать все заказы сразу.
        $orders = Order::with('user')
                       ->latest()
                       ->paginate(10); // Показываем по 10 заказов на странице

        // Передаем данные в Inertia-компонент.
        // Метод ->through() используется для форматирования данных для Vue,
        // сохраняя при этом метаданные пагинации.
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders->through(fn ($order) => [
                'id' => $order->id,
                
                'order_number' => $order->order_number,
                // Безопасный доступ к имени пользователя, если user может быть null (хотя не должен быть)
                'user_name' => $order->user->name ?? 'Неизвестный пользователь', 
                'total_amount' => $order->total_amount,
                'status' => __('statuses.' . $order->status), // <-- Добавляем перевод
                'created_at' => $order->created_at->format('d.m.Y H:i'),
            ]),
            'availableStatuses' => self::AVAILABLE_STATUSES, // Передаем доступные статусы во Vue для выпадающего списка
        ]);
    }

    /**
     * Отображает детали конкретного заказа для административной панели.
     * Используем Model Binding: Laravel автоматически найдет Order по {order} из URL.
     */
    public function show($id) // Изменяем с Order $order на $id, чтобы вручную контролировать поиск
    {
        // Eager load relationships user, items, and product for each item
        $order = Order::with(['user', 'items.product'])->find($id);
    
        // Если заказ не найден, передаем null во Vue
        // Vue компонент обработает это через v-if="order"
        if (!$order) {
            return Inertia::render('Admin/Orders/Show', [
                'order' => null, // Явно передаем null
                'availableStatuses' => self::AVAILABLE_STATUSES,
            ]);
        }
    
        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => __('statuses.' . $order->status), // <-- Добавляем перевод
                'created_at' => $order->created_at->format('d.m.Y H:i'),
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'product_slug' => $item->product->slug ?? null,
                    'product_image_url' => $item->product ? $item->product->main_image_url : asset('images/default_product.png'),
                ]),
            ],
            'availableStatuses' => self::AVAILABLE_STATUSES,
        ]);
    }

    /**
     * Обновляет статус заказа.
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Валидация: статус обязателен и должен быть одним из предопределенных значений.
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(self::AVAILABLE_STATUSES)],
        ]);

        // Обновляем статус заказа в базе данных.
        $order->update(['status' => $validated['status']]);

        // Опционально: Отправить уведомление клиенту о смене статуса.
        // Для этого понадобится настроить Laravel Notifications.
        // Пример: Notification::send($order->user, new OrderStatusUpdated($order));

        // Возвращаемся на предыдущую страницу с сообщением об успехе.
        return redirect()->back()->with('success', 'Статус заказа №' . $order->order_number . ' успешно обновлен на ' . $order->status);
    }
}