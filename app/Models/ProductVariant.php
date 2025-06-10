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
    public function getFirstItemPrice(?string $shippingMethod = null): float
    {
        $method = null;

        if (!empty($shippingMethod)) {
            $shippingMethodLower = strtolower($shippingMethod);
            $isTikTokLabel = str_contains($shippingMethodLower, 'tiktok_label');
            $method = $isTikTokLabel ? ShippingPrice::METHOD_TIKTOK_1ST : ShippingPrice::METHOD_SELLER_1ST;
        } else {
            $method = ShippingPrice::METHOD_SELLER_1ST;
        }

        $shippingPrice = $this->shippingPrices()->where('method', $method)->first();
        return $shippingPrice ? $shippingPrice->price_usd : 0;
    }

    /**
     * Get shipping price and product info for order with position logic
     */
    public function getOrderPriceInfo(?string $shippingMethod = null, int $position = 1): array
    {
        $printPrice = 0;
        $productId = $this->product_id;
        $method = null;

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

        // Lấy giá shipping dựa trên variant và method
        $shippingPrice = $this->shippingPrices()->where('method', $method)->first();

        if ($shippingPrice) {
            $printPrice = $shippingPrice->price_usd; // Sử dụng accessor price_usd để luôn quy đổi về USD

            Log::info("Found shipping price for variant", [
                'variant_id' => $this->id,
                'method' => $method,
                'price_usd' => $printPrice,
                'position' => $position
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
            'shipping_price_found' => !is_null($shippingPrice)
        ];
    }
}
