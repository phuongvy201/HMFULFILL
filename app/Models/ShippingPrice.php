<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingPrice extends Model
{
    protected $fillable = ['variant_id', 'method', 'price', 'currency', 'tier_name', 'user_id'];
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

    /**
     * Relationship với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

    /**
     * Tìm giá theo thứ tự ưu tiên: user-specific -> user tier -> default (null) -> Wood tier
     *
     * @param int $variantId ID của variant
     * @param string $method Shipping method
     * @param int|null $userId ID của user
     * @param string|null $userTier Tier của user
     * @return ShippingPrice|null
     */
    public static function findPriceByPriority(int $variantId, string $method, ?int $userId = null, ?string $userTier = null): ?ShippingPrice
    {
        // 1. Ưu tiên cao nhất: giá riêng cho user cụ thể
        if ($userId) {
            $userSpecificPrice = self::where('variant_id', $variantId)
                ->where('method', $method)
                ->where('user_id', $userId)
                ->first();

            if ($userSpecificPrice) {
                return $userSpecificPrice;
            }
        }

        // 2. Ưu tiên lấy giá theo tier của user
        if ($userTier) {
            $tierPrice = self::where('variant_id', $variantId)
                ->where('method', $method)
                ->where('tier_name', $userTier)
                ->first();

            if ($tierPrice) {
                return $tierPrice;
            }
        }

        // 3. Tìm giá mặc định (tier_name = null, user_id = null)
        $defaultPrice = self::where('variant_id', $variantId)
            ->where('method', $method)
            ->whereNull('tier_name')
            ->whereNull('user_id')
            ->first();

        if ($defaultPrice) {
            return $defaultPrice;
        }

        // 4. Fallback về Wood tier
        return self::where('variant_id', $variantId)
            ->where('method', $method)
            ->where('tier_name', 'Wood')
            ->whereNull('user_id')
            ->first();
    }

    /**
     * Lấy giá riêng cho user cụ thể
     */
    public static function getUserSpecificPrice(int $variantId, string $method, int $userId): ?ShippingPrice
    {
        return self::where('variant_id', $variantId)
            ->where('method', $method)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Tạo hoặc cập nhật giá riêng cho user
     */
    public static function setUserSpecificPrice(int $variantId, string $method, int $userId, float $price, string $currency = 'USD'): ShippingPrice
    {
        return self::updateOrCreate(
            [
                'variant_id' => $variantId,
                'method' => $method,
                'user_id' => $userId
            ],
            [
                'price' => $price,
                'currency' => $currency
            ]
        );
    }

    /**
     * Xóa giá riêng cho user
     */
    public static function removeUserSpecificPrice(int $variantId, string $method, int $userId): bool
    {
        return self::where('variant_id', $variantId)
            ->where('method', $method)
            ->where('user_id', $userId)
            ->delete() > 0;
    }
}
