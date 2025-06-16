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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Связь с заказом
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Связь с продуктом
            $table->string('product_name'); // Название продукта на момент заказа (чтобы сохранить историю)
            $table->decimal('price', 10, 2); // Цена продукта на момент заказа
            $table->integer('quantity'); // Количество
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
