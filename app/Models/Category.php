<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Для генерации slug, если нужно

class Category extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые разрешено массово присваивать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Атрибуты, которые должны быть скрыты при сериализации.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        //
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        //
    ];

    /**
     * Событие модели при создании для автоматической генерации slug (пример).
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (!$category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Здесь вы можете определить связи с другими моделями (например, с Product)
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}