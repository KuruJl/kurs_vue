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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2)->unsigned(); // 1. Цена не может быть отрицательной
            $table->text('description');
            $table->text('feature')->nullable(); // 2. Характеристики могут отсутствовать
            $table->string('slug')->unique();
            $table->unsignedInteger('quantity')->default(0); // 3. Более правильный тип и значение по умолчанию
            $table->timestamps();    
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
