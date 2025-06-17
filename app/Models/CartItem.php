<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price_at_addition',
    ];

    /**
     * Получить корзину, которой принадлежит элемент.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Получить товар, связанный с элементом корзины.
     */
    public function product()
    {
        return $this->belongsTo(Product::class); // Убедитесь, что у вас есть модель Product
    }
}