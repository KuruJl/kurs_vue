<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Связь с пользователем
            $table->string('order_number')->unique(); // Уникальный номер заказа
            $table->decimal('total_amount', 10, 2); // Общая сумма заказа
            $table->string('status')->default('pending'); // Статус заказа (pending, processing, completed, cancelled, etc.)
            $table->timestamps(); // created_at (дата заказа), updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
