<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'description',
        'quantity', 
        'slug',
        'feature',
    ];

    
    protected $appends = ['quantity_available', 'is_in_stock', 'main_image_url']; 

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function getMainImageUrlAttribute(): string
    {
        if ($this->mainImage) {
            return asset($this->mainImage->path);
        }
        if ($this->images->isNotEmpty()) {
            return asset($this->images->first()->path);
        }
        return asset('images/default_product.png');
    }

    public function getQuantityAvailableAttribute(): int
    {
        return (int) ($this->attributes['quantity'] ?? 0); 
    }

    public function getIsInStockAttribute(): bool
    {
        return (int) ($this->attributes['quantity'] ?? 0) > 0;
    }
}