<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order; // Импортируем модель Order
use App\Models\User;  // Импортируем модель User для eager loading (связь с заказом)
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule; // Для валидации статуса
use Illuminate\Support\Facades\DB; // <--- ДОБАВЬТЕ ЭТОТ ИМПОРТ
use App\Models\OrderItem; // <--- ДОБАВЬТЕ ЭТОТ ИМПОРТ

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
    public function updateItems(Request $request, Order $order)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:order_items,id', // Проверяем, что ID позиции существует
            'items.*.quantity' => 'required|integer|min:0', // Количество может быть 0 для удаления
        ]);

        // Используем транзакцию, чтобы все изменения применились, либо не применился ни один
        DB::transaction(function () use ($validated, $order) {
            $newTotalAmount = 0;

            foreach ($validated['items'] as $itemData) {
                // Находим позицию заказа, убедившись, что она принадлежит именно этому заказу
                $orderItem = OrderItem::where('id', $itemData['id'])
                                      ->where('order_id', $order->id)
                                      ->firstOrFail();

                if ($itemData['quantity'] > 0) {
                    // Обновляем количество
                    $orderItem->quantity = $itemData['quantity'];
                    $orderItem->save();
                    // Добавляем к новой итоговой сумме
                    $newTotalAmount += $orderItem->price * $orderItem->quantity;
                } else {
                    // Если количество 0, удаляем позицию
                    $orderItem->delete();
                }
            }

            // Обновляем итоговую сумму заказа
            $order->total_amount = $newTotalAmount;
            $order->save();
        });
        
        return redirect()->back()->with('success', 'Состав заказа успешно обновлен.');
    }
    /**
     * Отображает детали конкретного заказа для административной панели.
     * Используем Model Binding: Laravel автоматически найдет Order по {order} из URL.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.product'])->find($id);
    
        if (!$order) {
            return Inertia::render('Admin/Orders/Show', [
                'order' => null,
                'availableStatuses' => self::AVAILABLE_STATUSES,
            ]);
        }
    
        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => __('statuses.' . $order->status),
                'created_at' => $order->created_at->format('d.m.Y H:i'),
                'user' => $order->user,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id, // <--- ВАЖНОЕ ИЗМЕНЕНИЕ! Добавляем ID позиции заказа.
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'product_slug' => $item->product->slug ?? null,
                    'product_image_url' => optional($item->product)->main_image_url,
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

    public function destroy(Order $order)
    {
        // Опционально: Возвращаем товары на склад, если заказ не был 'cancelled'.
        // Это подстраховка, если админ удаляет активный заказ.
        $processingStatuses = ['processing', 'shipped', 'delivered'];
        if (in_array($order->status, $processingStatuses)) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('quantity', $item->quantity);
                }
            }
        }

        // Удаляем сам заказ. Благодаря каскадному удалению в миграции (onDelete('cascade')),
        // все связанные order_items удалятся автоматически.
        $order->delete();
        
        // Перенаправляем на страницу со списком заказов с сообщением об успехе.
        return redirect()->route('orders.index')->with('success', 'Заказ №' . $order->order_number . ' был успешно удален.');
    }

    
}