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
        'currency',
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
     * Định nghĩa các loại tiền tệ được hỗ trợ
     */
    const CURRENCY_USD = 'USD';
    const CURRENCY_GBP = 'GBP';
    const CURRENCY_VND = 'VND';

    public static $validCurrencies = [
        self::CURRENCY_USD => '$',
        self::CURRENCY_GBP => '£',
        self::CURRENCY_VND => 'đ'
    ];

    /**
     * Accessor để format giá sản phẩm
     */
    public function getFormattedPriceAttribute()
    {
        $symbol = self::$validCurrencies[$this->currency] ?? '';

        switch ($this->currency) {
            case self::CURRENCY_USD:
            case self::CURRENCY_GBP:
                return $symbol . number_format($this->base_price, 2);
            case self::CURRENCY_VND:
                return number_format($this->base_price, 0) . $symbol;
            default:
                return number_format($this->base_price, 2);
        }
    }

    /**
     * Method để chuyển đổi giá sang VND
     */
    public function getPriceInVND()
    {
        switch ($this->currency) {
            case self::CURRENCY_USD:
                return $this->base_price * config('currency.usd_to_vnd', 24500);
            case self::CURRENCY_GBP:
                return $this->base_price * config('currency.gbp_to_vnd', 31000);
            case self::CURRENCY_VND:
                return $this->base_price;
            default:
                throw new \InvalidArgumentException("Currency not supported: {$this->currency}");
        }
    }

    /**
     * Method để chuyển đổi giá sang USD
     */
    public function getPriceInUSD()
    {
        switch ($this->currency) {
            case self::CURRENCY_GBP:
                return $this->base_price * config('currency.gbp_to_usd', 1.27);
            case self::CURRENCY_VND:
                return $this->base_price / config('currency.usd_to_vnd', 24500);
            case self::CURRENCY_USD:
                return $this->base_price;
            default:
                throw new \InvalidArgumentException("Currency not supported: {$this->currency}");
        }
    }

    /**
     * Method để chuyển đổi giá sang GBP
     */
    public function getPriceInGBP()
    {
        switch ($this->currency) {
            case self::CURRENCY_USD:
                return $this->base_price / config('currency.gbp_to_usd', 1.27);
            case self::CURRENCY_VND:
                return $this->base_price / config('currency.usd_to_vnd', 24500) * config('currency.gbp_to_usd', 1.27);
            case self::CURRENCY_GBP:
                return $this->base_price;
            default:
                throw new \InvalidArgumentException("Currency not supported: {$this->currency}");
        }
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

    public function getAllProductsWithGBPVariants()
    {
        // Lấy tất cả sản phẩm có currency là GBP, kèm theo variant và sku
        $products = Product::with(['variants', 'variants.attributes'])
            ->where('currency', Product::CURRENCY_GBP)
            ->get();

        $result = [];
        foreach ($products as $product) {
            $variants = [];
            foreach ($product->variants as $variant) {
                $variants[] = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'twofifteen_sku' => $variant->twofifteen_sku,
                    'flashship_sku' => $variant->flashship_sku,
                    'attributes' => $variant->attributes->map(function ($attr) {
                        return [
                            'name' => $attr->name,
                            'value' => $attr->value
                        ];
                    })
                ];
            }
            $result[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'currency' => $product->currency,
                'variants' => $variants
            ];
        }
        return $result;
    }
}
