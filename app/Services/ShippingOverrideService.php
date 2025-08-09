<?php

namespace App\Services;

use App\Models\ShippingPrice;
use App\Models\ShippingOverride;
use App\Models\UserTier;
use Illuminate\Support\Collection;

class ShippingOverrideService
{
    /**
     * Thiết lập giá riêng cho một user
     */
    public static function setUserPrice(int $variantId, string $method, int $userId, float $price, string $currency = 'USD'): ShippingOverride
    {
        return ShippingPrice::setUserSpecificPrice($variantId, $method, $userId, $price, $currency);
    }

    /**
     * Thiết lập giá riêng cho một tier
     */
    public static function setTierPrice(int $variantId, string $method, string $tierName, float $price, string $currency = 'USD'): ShippingOverride
    {
        $basePrice = ShippingPrice::where('variant_id', $variantId)
            ->where('method', $method)
            ->first();

        if (!$basePrice) {
            throw new \InvalidArgumentException("Shipping price not found for variant {$variantId} and method {$method}");
        }

        return ShippingOverride::createOrUpdateForTier($basePrice->id, $tierName, $price, $currency);
    }

    /**
     * Lấy giá cho user với logic ưu tiên
     */
    public static function getPriceForUser(int $variantId, string $method, int $userId, ?string $userTier = null): ?array
    {
        return ShippingPrice::findPriceByPriority($variantId, $method, $userId, $userTier);
    }

    /**
     * Lấy giá cho tier
     */
    public static function getPriceForTier(int $variantId, string $method, string $tierName): ?array
    {
        $basePrice = ShippingPrice::where('variant_id', $variantId)
            ->where('method', $method)
            ->first();

        if (!$basePrice) {
            return null;
        }

        $tierOverride = ShippingOverride::findForTier($basePrice->id, $tierName);

        if ($tierOverride) {
            return [
                'price' => $tierOverride->override_price,
                'currency' => $tierOverride->currency,
                'is_override' => true,
                'override_id' => $tierOverride->id
            ];
        }

        return [
            'price' => $basePrice->price,
            'currency' => $basePrice->currency,
            'is_override' => false,
            'override_id' => null
        ];
    }

    /**
     * Xóa giá riêng cho user
     */
    public static function removeUserPrice(int $variantId, string $method, int $userId): bool
    {
        return ShippingPrice::removeUserSpecificPrice($variantId, $method, $userId);
    }

    /**
     * Xóa giá riêng cho tier
     */
    public static function removeTierPrice(int $variantId, string $method, string $tierName): bool
    {
        $basePrice = ShippingPrice::where('variant_id', $variantId)
            ->where('method', $method)
            ->first();

        if (!$basePrice) {
            return false;
        }

        return ShippingOverride::removeForTier($basePrice->id, $tierName);
    }

    /**
     * Lấy tất cả overrides cho một variant
     */
    public static function getOverridesForVariant(int $variantId): Collection
    {
        $shippingPrices = ShippingPrice::where('variant_id', $variantId)
            ->with(['overrides'])
            ->get();

        $overrides = collect();

        foreach ($shippingPrices as $shippingPrice) {
            foreach ($shippingPrice->overrides as $override) {
                $overrides->push([
                    'shipping_price_id' => $shippingPrice->id,
                    'variant_id' => $variantId,
                    'method' => $shippingPrice->method,
                    'override_id' => $override->id,
                    'user_ids' => $override->user_ids,
                    'tier_name' => $override->tier_name,
                    'override_price' => $override->override_price,
                    'currency' => $override->currency,
                    'base_price' => $shippingPrice->price,
                    'base_currency' => $shippingPrice->currency
                ]);
            }
        }

        return $overrides;
    }

    /**
     * Lấy overrides cho một user cụ thể
     */
    public static function getOverridesForUser(int $userId): Collection
    {
        return ShippingOverride::where(function ($query) use ($userId) {
            $query->whereJsonContains('user_ids', $userId)
                ->orWhereJsonContains('user_ids', (string) $userId);
        })
            ->with(['shippingPrice.variant.product'])
            ->get()
            ->map(function ($override) {
                return [
                    'override_id' => $override->id,
                    'variant_id' => $override->shippingPrice->variant_id,
                    'method' => $override->shippingPrice->method,
                    'override_price' => $override->override_price,
                    'currency' => $override->currency,
                    'base_price' => $override->shippingPrice->price,
                    'base_currency' => $override->shippingPrice->currency,
                    'product_name' => $override->shippingPrice->variant->product->name ?? 'Unknown',
                    'variant_sku' => $override->shippingPrice->variant->sku ?? 'Unknown'
                ];
            });
    }

    /**
     * Lấy overrides cho một tier cụ thể
     */
    public static function getOverridesForTier(string $tierName): Collection
    {
        return ShippingOverride::where('tier_name', $tierName)
            ->with(['shippingPrice.variant.product'])
            ->get()
            ->map(function ($override) {
                return [
                    'override_id' => $override->id,
                    'variant_id' => $override->shippingPrice->variant_id,
                    'method' => $override->shippingPrice->method,
                    'override_price' => $override->override_price,
                    'currency' => $override->currency,
                    'base_price' => $override->shippingPrice->price,
                    'base_currency' => $override->shippingPrice->currency,
                    'product_name' => $override->shippingPrice->variant->product->name ?? 'Unknown',
                    'variant_sku' => $override->shippingPrice->variant->sku ?? 'Unknown'
                ];
            });
    }

    /**
     * Thêm user vào override hiện có
     */
    public static function addUserToOverride(int $overrideId, int $userId): bool
    {
        $override = ShippingOverride::find($overrideId);

        if (!$override) {
            return false;
        }

        $override->addUser($userId);
        return true;
    }

    /**
     * Xóa user khỏi override
     */
    public static function removeUserFromOverride(int $overrideId, int $userId): bool
    {
        $override = ShippingOverride::find($overrideId);

        if (!$override) {
            return false;
        }

        $override->removeUser($userId);
        return true;
    }

    /**
     * Lấy danh sách users trong một override
     */
    public static function getUsersInOverride(int $overrideId): array
    {
        $override = ShippingOverride::find($overrideId);

        if (!$override) {
            return [];
        }

        return $override->user_ids ?? [];
    }
}
