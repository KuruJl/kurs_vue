<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'description',
        'image', // Название колонки для пути к изображению
        'in_stock', // Название колонки для булевого флага наличия
        'quantity', // Название колонки для количества товара на складе
        'slug',
        'feature',
    ];

    // Добавляем аксессоры в $appends, чтобы они автоматически включались при сериализации модели
    protected $appends = ['image_url', 'quantity_available', 'is_in_stock']; // Добавляем все три аксессора

    /**
     * Получить категорию, к которой принадлежит продукт.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Получить заказы, в которых участвует продукт.
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    /**
     * Аксессор для получения полного URL изображения продукта.
     * Возвращает string
     */
    public function getImageUrlAttribute(): string
    {
        // Проверяем, существует ли путь к изображению в колонке 'image'
        if ($this->image) {
            // asset() генерирует полный URL, storage/ - это публичная папка для симлинка
            return asset('storage/' . $this->image);
        }
        // Если изображения нет, возвращаем путь к изображению-заглушке
        return asset('images/default_product.png'); // Убедитесь, что этот файл существует
    }

    /**
     * Аксессор для получения доступного количества товара.
     * Возвращает int
     */
    public function getQuantityAvailableAttribute(): int
    {
        // Возвращаем значение из колонки 'quantity' в БД, приводим к int
        return (int) $this->attributes['quantity'];
    }

    /**
     * Аксессор для проверки, есть ли товар в наличии.
     * Возвращает bool
     */
    public function getIsInStockAttribute(): bool
    {
        // Возвращаем значение из колонки 'in_stock' в БД, приводим к bool
        // Также можно добавить проверку на quantity > 0
        return (bool) $this->attributes['in_stock'] && $this->attributes['quantity'] > 0;
    }
}