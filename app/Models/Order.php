<?php

// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'address'
    ];

    // Связь с пользователем
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Связь с товарами через промежуточную таблицу
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
                   ->withPivot('quantity', 'price')
                   ->withTimestamps();
    }

    // Связь с элементами заказа
    public function items()
    {
        return $this->hasMany(Order::class);
    }
}