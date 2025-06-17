<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_token',
    ];

    /**
     * Получить пользователя, которому принадлежит корзина.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить элементы корзины.
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Вычислить общую стоимость корзины.
     */
    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price_at_addition * $item->quantity;
        });
    }
}