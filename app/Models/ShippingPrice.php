<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingPrice extends Model
{
    protected $fillable = ['variant_id', 'method', 'price', 'currency', 'tier_name'];
    protected $appends = ['price_usd', 'price_vnd', 'price_gbp'];

    // Định nghĩa các hằng số cho shipping methods
    const METHOD_TIKTOK_1ST = 'tiktok_1st';
    const METHOD_TIKTOK_NEXT = 'tiktok_next';
    const METHOD_SELLER_1ST = 'seller_1st';
    const METHOD_SELLER_NEXT = 'seller_next';

    // Danh sách các method hợp lệ
    public static $validMethods = [
        self::METHOD_TIKTOK_1ST,
        self::METHOD_TIKTOK_NEXT,
        self::METHOD_SELLER_1ST,
        self::METHOD_SELLER_NEXT
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Relationship với UserTier
     */
    public function tier()
    {
        return $this->belongsTo(UserTier::class, 'tier_id');
    }

    // Format giá shipping theo currency
    /**
     * Format giá shipping theo currency
     */
    public function getFormattedPriceAttribute()
    {
        $symbol = Product::$validCurrencies[$this->currency] ?? '';
        switch ($this->currency) {
            case Product::CURRENCY_USD:
            case Product::CURRENCY_GBP:
                return $symbol . number_format($this->price, 2);
            case Product::CURRENCY_VND:
                return number_format($this->price, 0) . $symbol;
            default:
                throw new \InvalidArgumentException("Currency not supported: {$this->currency}");
        }
    }

    /**
     * Chuyển đổi giá shipping sang VND
     */
    public function getPriceVndAttribute(): float
    {
        switch ($this->currency) {
            case Product::CURRENCY_USD:
                return $this->price * config('currency.usd_to_vnd', 24500);
            case Product::CURRENCY_GBP:
                return $this->price * config('currency.gbp_to_vnd', 31000);
            case Product::CURRENCY_VND:
                return $this->price;
            default:
                throw new \InvalidArgumentException("Currency not supported: {$this->currency}");
        }
    }

    /**
     * Chuyển đổi giá shipping sang USD
     */
    public function getPriceUsdAttribute(): float
    {
        switch ($this->currency) {
            case Product::CURRENCY_GBP:
                return $this->price * config('currency.gbp_to_usd', 1.27);
            case Product::CURRENCY_VND:
                return $this->price / config('currency.usd_to_vnd', 24500);
            case Product::CURRENCY_USD:
                return $this->price;
            default:
                throw new \InvalidArgumentException("Currency not supported: {$this->currency}");
        }
    }

    /**
     * Chuyển đổi giá shipping sang GBP
     */
    public function getPriceGbpAttribute(): float
    {
        switch ($this->currency) {
            case Product::CURRENCY_USD:
                return $this->price / config('currency.gbp_to_usd', 1.27);
            case Product::CURRENCY_VND:
                return $this->price / config('currency.usd_to_vnd', 24500) * (1 / config('currency.gbp_to_usd', 1.27));
            case Product::CURRENCY_GBP:
                return $this->price;
            default:
                throw new \InvalidArgumentException("Currency not supported: {$this->currency}");
        }
    }

    /**
     * Lấy shipping price theo variant, method và tier
     */
    public static function getPriceByVariantAndTier(int $variantId, string $method, ?int $tierId = null)
    {
        $query = self::where('variant_id', $variantId)
            ->where('method', $method);

        if ($tierId) {
            $query->where('tier_id', $tierId);
        } else {
            $query->whereNull('tier_id'); // Giá mặc định khi không có tier
        }

        return $query->first();
    }

    /**
     * Lấy tất cả shipping prices cho một variant
     */
    public static function getPricesByVariant(int $variantId)
    {
        return self::where('variant_id', $variantId)
            ->with('tier')
            ->get()
            ->groupBy('method');
    }

    /**
     * Lấy shipping prices theo tier
     */
    public static function getPricesByTier(int $tierId)
    {
        return self::where('tier_id', $tierId)
            ->with(['variant', 'variant.product'])
            ->get();
    }
}
