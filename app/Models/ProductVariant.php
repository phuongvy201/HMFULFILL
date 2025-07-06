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

        // Nếu có userId, ưu tiên lấy giá theo tier
        if ($userId) {
            $userTier = \App\Models\UserTier::getCurrentTier($userId);
            $tierName = $userTier ? $userTier->tier : 'Wood';

            $tierPrice = $this->shippingPrices()
                ->where('method', $method)
                ->where('tier_name', $tierName)
                ->first();

            if ($tierPrice) {
                return $tierPrice->price_usd;
            }
        }

        // Fallback về giá Wood tier
        $shippingPrice = $this->shippingPrices()
            ->where('method', $method)
            ->where('tier_name', 'Wood')
            ->first();

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

        // Nếu có userId, ưu tiên lấy giá theo tier
        if ($userId) {
            $userTier = \App\Models\UserTier::getCurrentTier($userId);
            $tierName = $userTier ? $userTier->tier : 'Wood';

            $tierPrice = $this->shippingPrices()
                ->where('method', $method)
                ->where('tier_name', $tierName)
                ->first();

            if ($tierPrice) {
                $printPrice = $tierPrice->price_usd;

                Log::info("Found tier price for variant", [
                    'variant_id' => $this->id,
                    'user_id' => $userId,
                    'method' => $method,
                    'price_usd' => $printPrice,
                    'position' => $position,
                    'tier' => $tierName
                ]);

                return [
                    'print_price' => $printPrice,
                    'product_id' => $productId,
                    'method' => $method,
                    'shipping_price_found' => true,
                    'tier_price' => true,
                    'tier' => $tierName
                ];
            }
        }

        // Fallback về giá Wood tier
        $shippingPrice = $this->shippingPrices()
            ->where('method', $method)
            ->where('tier_name', 'Wood')
            ->first();

        if ($shippingPrice) {
            $printPrice = $shippingPrice->price_usd; // Sử dụng accessor price_usd để luôn quy đổi về USD

            Log::info("Found shipping price for variant", [
                'variant_id' => $this->id,
                'method' => $method,
                'price_usd' => $printPrice,
                'position' => $position,
                'tier' => 'Wood'
            ]);
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
            'shipping_price_found' => !is_null($shippingPrice),
            'tier_price' => false
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
}
