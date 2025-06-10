<?php

// app/Models/OrderItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order_item extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price'
    ];

    // Связь с заказом
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Связь с товаром
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}