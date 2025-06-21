<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем тестового пользователя
        User::factory()->create([
            'name'      => 's',
            'email'     => 's@gmail.com',
            'password'  => Hash::make('password'),
            'is_admin'  => 0,
          'phone' => '123'
        ]);

        // Создаем администратора
        User::factory()->create([
            'name'      => 'd',
            'email'     => 'd@gmail.com',
            'password'  => Hash::make('secure_password'), // Используйте надежный пароль!
            'is_admin'  => true,
            'phone' => '123'

        ]);

        // Дополнительные пользователи (раскомментировать при необходимости)
        // User::factory(10)->create();
    }
}