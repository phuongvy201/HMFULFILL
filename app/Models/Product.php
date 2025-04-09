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
     * Quan hệ với bảng product_images
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Lấy ảnh chính của sản phẩm
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Quan hệ với bảng attributes thông qua product_attributes
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('value', 'image_url')
            ->withTimestamps();
    }

    /**
     * Quan hệ trực tiếp với bảng product_attributes
     */
    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Quan hệ với bảng variants
     */
    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    /**
     * Quan hệ với bảng fulfillment_regions thông qua product_fulfillments
     */
    public function fulfillmentRegions(): BelongsToMany
    {
        return $this->belongsToMany(FulfillmentRegion::class, 'product_fulfillments')
            ->withTimestamps();
    }

    /**
     * Scope để lọc sản phẩm theo trạng thái
     */
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
}
