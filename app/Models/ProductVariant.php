<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'twofifteen_sku',
        'flashship_sku'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributes()
    {
        return $this->hasMany(VariantAttribute::class, 'variant_id');
    }

    public function shippingPrices()
    {
        return $this->hasMany(ShippingPrice::class, 'variant_id');
    }

    /**
     * Relationship với ShippingPrice theo tier
     */
    public function tierPrices()
    {
        return $this->hasMany(ShippingPrice::class, 'variant_id');
    }

    /**
     * Lấy SKU tương ứng dựa trên warehouse
     *
     * @param string|null $warehouse Giá trị warehouse (US, UK, hoặc null)
     * @return string|null SKU tương ứng với warehouse
     */
    public function getSkuByWarehouse(?string $warehouse): ?string
    {
        $wh = strtoupper(trim((string)$warehouse));
        if ($wh === 'US') {
            return $this->flashship_sku;
        } elseif ($wh === 'UK') {
            return $this->twofifteen_sku;
        }
        return $this->sku;
    }

    /**
     * Get first item price for comparison to find highest price item
     */
    public function getFirstItemPrice(?string $shippingMethod = null, ?int $userId = null): float
    {
        $method = null;

        if (!empty($shippingMethod)) {
            $shippingMethodLower = strtolower($shippingMethod);
            $isTikTokLabel = str_contains($shippingMethodLower, 'tiktok_label');
            $method = $isTikTokLabel ? ShippingPrice::METHOD_TIKTOK_1ST : ShippingPrice::METHOD_SELLER_1ST;
        } else {
            $method = ShippingPrice::METHOD_SELLER_1ST;
        }

        // Sử dụng logic mới với user-specific pricing
        $userTier = null;
        if ($userId) {
            $userTier = \App\Models\UserTier::getCurrentTier($userId);
            $tierName = $userTier ? $userTier->tier : 'Wood';
        }

        $shippingPrice = ShippingPrice::findPriceByPriority($this->id, $method, $userId, $userTier ? $userTier->tier : null);

        return $shippingPrice ? $shippingPrice->price_usd : 0;
    }

    /**
     * Get shipping price and product info for order with position logic
     */
    public function getOrderPriceInfo(?string $shippingMethod = null, int $position = 1, ?int $userId = null): array
    {
        $printPrice = 0;
        $productId = $this->product_id;
        $method = null;
        $tierName = 'Wood';
        $tierPrice = false;
        $userSpecificPrice = false;

        if (!empty($shippingMethod)) {
            $shippingMethodLower = strtolower($shippingMethod);
            $isTikTokLabel = str_contains($shippingMethodLower, 'tiktok_label');

            // Xác định method dựa trên shipping method và position
            $method = $isTikTokLabel ?
                ($position === 1 ? ShippingPrice::METHOD_TIKTOK_1ST : ShippingPrice::METHOD_TIKTOK_NEXT) : ($position === 1 ? ShippingPrice::METHOD_SELLER_1ST : ShippingPrice::METHOD_SELLER_NEXT);
        } else {
            // Mặc định sử dụng seller method
            $method = $position === 1 ? ShippingPrice::METHOD_SELLER_1ST : ShippingPrice::METHOD_SELLER_NEXT;
        }

        $shippingPriceFound = false;

        // Sử dụng logic mới với user-specific pricing
        $userTier = null;
        if ($userId) {
            $userTier = \App\Models\UserTier::getCurrentTier($userId);
            $tierName = $userTier ? $userTier->tier : 'Wood';
        }

        $shippingPrice = ShippingPrice::findPriceByPriority($this->id, $method, $userId, $userTier ? $userTier->tier : null);

        if ($shippingPrice) {
            $printPrice = $shippingPrice->price_usd;
            $shippingPriceFound = true;

            // Xác định loại giá
            if ($shippingPrice->user_id) {
                $userSpecificPrice = true;
                $tierPrice = false;
                Log::info("Found user-specific price for variant", [
                    'variant_id' => $this->id,
                    'user_id' => $userId,
                    'method' => $method,
                    'price_usd' => $printPrice,
                    'position' => $position,
                    'price_type' => 'user_specific'
                ]);
            } elseif ($shippingPrice->tier_name) {
                $tierPrice = true;
                $userSpecificPrice = false;
                Log::info("Found tier price for variant", [
                    'variant_id' => $this->id,
                    'user_id' => $userId,
                    'method' => $method,
                    'price_usd' => $printPrice,
                    'position' => $position,
                    'tier' => $shippingPrice->tier_name
                ]);
            } else {
                $tierPrice = false;
                $userSpecificPrice = false;
                Log::info("Found default price for variant", [
                    'variant_id' => $this->id,
                    'user_id' => $userId,
                    'method' => $method,
                    'price_usd' => $printPrice,
                    'position' => $position,
                    'price_type' => 'default'
                ]);
            }
        } else {
            Log::warning("No shipping price found for variant", [
                'variant_id' => $this->id,
                'method' => $method,
                'shipping_method' => $shippingMethod,
                'position' => $position
            ]);
        }

        return [
            'print_price' => $printPrice,
            'product_id' => $productId,
            'method' => $method,
            'shipping_price_found' => $shippingPriceFound,
            'tier_price' => $tierPrice,
            'user_specific_price' => $userSpecificPrice,
            'tier' => $tierName
        ];
    }

    /**
     * Lấy tất cả giá theo tier cho variant này
     */
    public function getAllTierPrices()
    {
        return $this->shippingPrices()->get();
    }

    /**
     * Lấy giá theo tier cụ thể
     */
    public function getTierPrice($tier, $method)
    {
        return $this->shippingPrices()
            ->where('tier_name', $tier)
            ->where('method', $method)
            ->first();
    }

    /**
     * Tạo hoặc cập nhật giá theo tier
     */
    public function setTierPrice($tier, $method, $price, $currency = 'USD')
    {
        return ShippingPrice::updateOrCreate(
            [
                'variant_id' => $this->id,
                'tier_name' => $tier,
                'method' => $method
            ],
            [
                'price' => $price,
                'currency' => $currency
            ]
        );
    }

    /**
     * Tạo hoặc cập nhật giá riêng cho user
     */
    public function setUserSpecificPrice(int $userId, string $method, float $price, string $currency = 'USD')
    {
        return ShippingPrice::setUserSpecificPrice($this->id, $method, $userId, $price, $currency);
    }

    /**
     * Lấy giá riêng cho user
     */
    public function getUserSpecificPrice(int $userId, string $method)
    {
        return ShippingPrice::getUserSpecificPrice($this->id, $method, $userId);
    }

    /**
     * Xóa giá riêng cho user
     */
    public function removeUserSpecificPrice(int $userId, string $method)
    {
        return ShippingPrice::removeUserSpecificPrice($this->id, $method, $userId);
    }

    /**
     * Lấy tất cả giá riêng cho user
     */
    public function getAllUserSpecificPrices(int $userId)
    {
        return $this->shippingPrices()
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Tìm variant dựa trên attributes
     *
     * @param int $productId
     * @param array $selectedAttributes ['color' => 'Black', 'size' => 'M']
     * @return ProductVariant|null
     */
    public static function findVariantByAttributes(int $productId, array $selectedAttributes): ?ProductVariant
    {
        if (empty($selectedAttributes)) {
            return null;
        }

        $variant = self::where('product_id', $productId)
            ->whereHas('attributes', function ($query) use ($selectedAttributes) {
                // Đếm số attributes match
                $query->selectRaw('variant_id, COUNT(*) as match_count')
                    ->whereIn('name', array_keys($selectedAttributes))
                    ->whereIn('value', array_values($selectedAttributes))
                    ->groupBy('variant_id')
                    ->having('match_count', '=', count($selectedAttributes));
            })
            ->first();

        return $variant;
    }

    /**
     * Tìm variant dựa trên attributes với fallback
     *
     * @param int $productId
     * @param array $selectedAttributes
     * @return ProductVariant|null
     */
    public static function findVariantByAttributesWithFallback(int $productId, array $selectedAttributes): ?ProductVariant
    {
        // Thử tìm exact match trước
        $variant = self::findVariantByAttributes($productId, $selectedAttributes);

        if ($variant) {
            return $variant;
        }

        // Nếu không tìm thấy, thử tìm partial match
        $variant = self::where('product_id', $productId)
            ->whereHas('attributes', function ($query) use ($selectedAttributes) {
                foreach ($selectedAttributes as $name => $value) {
                    $query->whereHas('attributes', function ($subQuery) use ($name, $value) {
                        $subQuery->where('name', $name)
                            ->where('value', $value);
                    });
                }
            })
            ->first();

        return $variant;
    }

    /**
     * Lấy tất cả attributes của variant dưới dạng array
     *
     * @return array
     */
    public function getAttributesArray(): array
    {
        return $this->attributes->pluck('value', 'name')->toArray();
    }

    /**
     * Kiểm tra xem variant có match với attributes không
     *
     * @param array $selectedAttributes
     * @return bool
     */
    public function matchesAttributes(array $selectedAttributes): bool
    {
        $variantAttributes = $this->getAttributesArray();

        foreach ($selectedAttributes as $name => $value) {
            if (!isset($variantAttributes[$name]) || $variantAttributes[$name] !== $value) {
                return false;
            }
        }

        return true;
    }
}
