<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        // Получаем заказы пользователя, загружая связанные позиции заказа и продукты
        $orders = $request->user()->orders()
                    ->with(['items.product']) // Загружаем order items и их продукты
                    ->latest() // Сортируем по дате создания, новейшие сверху
                    ->get();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail && ! $request->user()->hasVerifiedEmail(),
            'status' => session('status'),
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('d.m.Y H:i'),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'product_slug' => $item->product->slug ?? null,
                            // Аксессор main_image_url должен быть доступен,
                            // но если нет, используем images->first()->path
                            'product_image_url' => $item->product->main_image_url ?? ($item->product->images->first()->path ?? '/images/default-product.png'),
                        ];
                    }),
                ];
            }),
            // Добавим пропс 'user' напрямую из request, если вы его используете в Edit.vue
            'user' => $request->user()->toArray(),
            // Если вы используете flash-сообщения 'success' через сессию, убедитесь, что они доступны здесь.
            // Inertia автоматически делает flash-сообщения доступными через page.props.flash
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
