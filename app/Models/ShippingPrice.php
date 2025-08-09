<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ShippingOverride;

class ShippingPrice extends Model
{
    protected $fillable = ['variant_id', 'method', 'price', 'currency'];
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
     * Relationship với ShippingOverride
     */
    public function overrides()
    {
        return $this->hasMany(ShippingOverride::class);
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
     * Lấy shipping price theo variant và method
     */
    public static function getPriceByVariantAndMethod(int $variantId, string $method)
    {
        return self::where('variant_id', $variantId)
            ->where('method', $method)
            ->first();
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
     * Lấy shipping prices với overrides theo tier
     */
    public static function getPricesWithOverridesByTier(string $tierName)
    {
        return self::with(['overrides' => function ($query) use ($tierName) {
            $query->where('tier_name', $tierName);
        }, 'variant', 'variant.product'])
            ->get();
    }

    /**
     * Tìm giá theo thứ tự ưu tiên: user-specific -> user tier -> default
     *
     * @param int $variantId ID của variant
     * @param string $method Shipping method
     * @param int|null $userId ID của user
     * @param string|null $userTier Tier của user
     * @return array|null ['price' => float, 'currency' => string, 'is_override' => bool]
     */
    public static function findPriceByPriority(int $variantId, string $method, ?int $userId = null, ?string $userTier = null): ?array
    {
        // Lấy shipping price cơ bản
        $basePrice = self::where('variant_id', $variantId)
            ->where('method', $method)
            ->first();

        if (!$basePrice) {
            return null;
        }

        // 1. Ưu tiên cao nhất: giá riêng cho user cụ thể
        if ($userId) {
            $userOverride = ShippingOverride::findForUser($basePrice->id, $userId);
            if ($userOverride) {
                return [
                    'price' => $userOverride->override_price,
                    'currency' => $userOverride->currency,
                    'is_override' => true,
                    'override_id' => $userOverride->id
                ];
            }
        }

        // 2. Ưu tiên lấy giá theo tier của user
        if ($userTier) {
            $tierOverride = ShippingOverride::findForTier($basePrice->id, $userTier);
            if ($tierOverride) {
                return [
                    'price' => $tierOverride->override_price,
                    'currency' => $tierOverride->currency,
                    'is_override' => true,
                    'override_id' => $tierOverride->id
                ];
            }
        }

        // 3. Trả về giá mặc định
        return [
            'price' => $basePrice->price,
            'currency' => $basePrice->currency,
            'is_override' => false,
            'override_id' => null
        ];
    }

    /**
     * Lấy giá riêng cho user cụ thể
     */
    public static function getUserSpecificPrice(int $variantId, string $method, int $userId): ?array
    {
        $basePrice = self::where('variant_id', $variantId)
            ->where('method', $method)
            ->first();

        if (!$basePrice) {
            return null;
        }

        $userOverride = ShippingOverride::findForUser($basePrice->id, $userId);

        if ($userOverride) {
            return [
                'price' => $userOverride->override_price,
                'currency' => $userOverride->currency,
                'is_override' => true,
                'override_id' => $userOverride->id
            ];
        }

        return null;
    }

    /**
     * Tạo hoặc cập nhật giá riêng cho user
     */
    public static function setUserSpecificPrice(int $variantId, string $method, int $userId, float $price, string $currency = 'USD'): ShippingOverride
    {
        $basePrice = self::where('variant_id', $variantId)
            ->where('method', $method)
            ->first();

        if (!$basePrice) {
            throw new \InvalidArgumentException("Shipping price not found for variant {$variantId} and method {$method}");
        }

        return ShippingOverride::createOrUpdateForUser($basePrice->id, $userId, $price, $currency);
    }

    /**
     * Xóa giá riêng cho user
     */
    public static function removeUserSpecificPrice(int $variantId, string $method, int $userId): bool
    {
        $basePrice = self::where('variant_id', $variantId)
            ->where('method', $method)
            ->first();

        if (!$basePrice) {
            return false;
        }

        return ShippingOverride::removeForUser($basePrice->id, $userId);
    }
}
