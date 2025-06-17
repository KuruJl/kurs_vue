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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade'); // Связь с таблицей carts
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Связь с таблицей products (предполагаем, что она у вас есть)
            $table->integer('quantity')->default(1); // Количество товара
            $table->decimal('price_at_addition', 10, 2); // Цена товара в момент добавления
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};