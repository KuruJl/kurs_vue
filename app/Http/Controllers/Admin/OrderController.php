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
                'status' => $order->status,
                'created_at' => $order->created_at->format('d.m.Y H:i'),
            ]),
            'availableStatuses' => self::AVAILABLE_STATUSES, // Передаем доступные статусы во Vue для выпадающего списка
        ]);
    }

    /**
     * Отображает детали конкретного заказа для административной панели.
     * Используем Model Binding: Laravel автоматически найдет Order по {order} из URL.
     */
    public function show(Order $order) // Laravel автоматически вернет 404, если Order не найден по ID
    {
        // Загружаем связанные данные: пользователя и товары заказа (с информацией о продукте).
        $order->load(['user', 'items.product']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at->format('d.m.Y H:i'),
                // Данные пользователя, который сделал заказ
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                // Форматируем список товаров в заказе
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    // Проверяем наличие продукта, так как он может быть удален из БД
                    'product_slug' => $item->product->slug ?? null,
                    'product_image_url' => $item->product ? $item->product->main_image_url : asset('images/default_product.png'),
                ]),
            ],
            'availableStatuses' => self::AVAILABLE_STATUSES, // Доступные статусы для формы обновления
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