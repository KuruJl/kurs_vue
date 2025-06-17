<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\CartService; // Импортируем наш CartService

class MergeCartOnLogin
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Вызываем метод слияния корзин в нашем сервисе
        $this->cartService->mergeGuestCartToUser($event->user->id);
    }
}