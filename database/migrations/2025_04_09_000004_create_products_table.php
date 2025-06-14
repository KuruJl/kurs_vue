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
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Пример с внешним ключом
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->text('description');
            $table->text('feature');
            $table->string('image');
            $table->boolean('in_stock')->default(true);
            $table->string('slug')->unique();
            $table->integer('quantity')->default(100)->unsigned();
            $table->timestamps();
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
