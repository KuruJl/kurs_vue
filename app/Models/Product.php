<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'description',
        'image',
        'in_stock',
        'quantity',
        'slug',
        'feature',
    ];

    protected $appends = ['image_url', 'quantity_available', 'is_in_stock'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/default_product.png');
    }

    public function getQuantityAvailableAttribute(): int
    {
        return (int) ($this->attributes['quantity'] ?? 0);
    }

    public function getIsInStockAttribute(): bool
    {
        $inStock = (bool) ($this->attributes['in_stock'] ?? false);
        $quantity = (int) ($this->attributes['quantity'] ?? 0);
        return $inStock && $quantity > 0;
    }
}