<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        'description',
        'base_price',
        'category_id',
        'template_link'
    ];

    /**
     * Quan hệ với bảng categories
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Quan hệ với bảng images
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function attributes()
    {
        return $this->hasManyThrough(
            VariantAttribute::class,
            ProductVariant::class,
            'product_id',
            'variant_id'
        );
    }

    public function fulfillmentLocations()
    {
        return $this->hasMany(FulfillmentLocation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope để lọc sản phẩm theo danh mục
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope để tìm kiếm sản phẩm theo tên
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%");
    }

    /**
     * Accessor để format giá sản phẩm
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->base_price, 2) . ' đ';
    }

    /**
     * Boot method để tự động tạo slug khi tạo mới sản phẩm
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function getGroupedAttributes()
    {
        return $this->attributes()
            ->get()
            ->groupBy('name')
            ->map(function ($items) {
                return $items->pluck('value')->unique()->values();
            });
    }
}
