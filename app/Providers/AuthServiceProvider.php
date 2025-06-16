<?php

namespace App\Providers;

use App\Models\User; // Убедитесь, что User импортирован
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate; // <--- ДОБАВЬТЕ ЭТУ СТРОКУ

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Определяем Gate для управления заказами
        Gate::define('manage-orders', function (User $user) {
            // Здесь вы должны определить логику, кто может управлять заказами.
            // Например, если у вас есть поле 'is_admin' в таблице 'users'.
            return $user->is_admin === 1; // Или 'is_admin' === true, в зависимости от типа поля.
            // Если у вас более сложная система ролей/разрешений (например, spatie/laravel-permission),
            // то здесь будет $user->hasRole('admin') или $user->can('edit orders').
        });
    }
}