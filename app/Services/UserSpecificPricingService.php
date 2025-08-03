<?php

namespace App\Services;

use App\Models\ShippingPrice;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserSpecificPricingService
{
    /**
     * Tạo hoặc cập nhật giá riêng cho user
     */
    public static function setUserPrice(int $userId, int $variantId, string $method, float $price, string $currency = 'USD'): ShippingPrice
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \InvalidArgumentException("User not found with ID: {$userId}");
        }

        $variant = ProductVariant::find($variantId);
        if (!$variant) {
            throw new \InvalidArgumentException("Product variant not found with ID: {$variantId}");
        }

        if (!in_array($method, ShippingPrice::$validMethods)) {
            throw new \InvalidArgumentException("Invalid shipping method: {$method}");
        }

        $shippingPrice = ShippingPrice::setUserSpecificPrice($variantId, $method, $userId, $price, $currency);

        Log::info("User-specific price set", [
            'user_id' => $userId,
            'user_email' => $user->email,
            'variant_id' => $variantId,
            'method' => $method,
            'price' => $price,
            'currency' => $currency
        ]);

        return $shippingPrice;
    }

    /**
     * Lấy giá riêng cho user
     */
    public static function getUserPrice(int $userId, int $variantId, string $method): ?ShippingPrice
    {
        return ShippingPrice::getUserSpecificPrice($variantId, $method, $userId);
    }

    /**
     * Xóa giá riêng cho user
     */
    public static function removeUserPrice(int $userId, int $variantId, string $method): bool
    {
        $user = User::find($userId);
        $variant = ProductVariant::find($variantId);

        $removed = ShippingPrice::removeUserSpecificPrice($variantId, $method, $userId);

        if ($removed) {
            Log::info("User-specific price removed", [
                'user_id' => $userId,
                'user_email' => $user ? $user->email : 'unknown',
                'variant_id' => $variantId,
                'method' => $method
            ]);
        }

        return $removed;
    }

    /**
     * Lấy tất cả giá riêng cho một user
     */
    public static function getAllUserPrices(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return ShippingPrice::where('user_id', $userId)
            ->with(['variant', 'variant.product'])
            ->get();
    }

    /**
     * Lấy tất cả user có giá riêng
     */
    public static function getUsersWithSpecificPrices(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('shippingPrices')
            ->with(['shippingPrices.variant', 'shippingPrices.variant.product'])
            ->get();
    }

    /**
     * Kiểm tra xem user có giá riêng cho variant và method không
     */
    public static function hasUserSpecificPrice(int $userId, int $variantId, string $method): bool
    {
        return ShippingPrice::where('user_id', $userId)
            ->where('variant_id', $variantId)
            ->where('method', $method)
            ->exists();
    }

    /**
     * Lấy danh sách tất cả giá riêng cho user (có phân trang)
     */
    public static function getUserPricesPaginated(int $userId, int $perPage = 15)
    {
        return ShippingPrice::where('user_id', $userId)
            ->with(['variant', 'variant.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Lấy thống kê giá riêng cho user
     */
    public static function getUserPriceStats(int $userId): array
    {
        $prices = ShippingPrice::where('user_id', $userId)->get();

        return [
            'total_prices' => $prices->count(),
            'methods' => $prices->groupBy('method')->map->count(),
            'currencies' => $prices->groupBy('currency')->map->count(),
            'price_range' => [
                'min' => $prices->min('price'),
                'max' => $prices->max('price'),
                'avg' => $prices->avg('price')
            ]
        ];
    }

    /**
     * Copy giá từ một user sang user khác
     */
    public static function copyUserPrices(int $fromUserId, int $toUserId): int
    {
        $sourcePrices = ShippingPrice::where('user_id', $fromUserId)->get();
        $copiedCount = 0;

        foreach ($sourcePrices as $price) {
            try {
                ShippingPrice::setUserSpecificPrice(
                    $price->variant_id,
                    $price->method,
                    $toUserId,
                    $price->price,
                    $price->currency
                );
                $copiedCount++;
            } catch (\Exception $e) {
                Log::error("Failed to copy user price", [
                    'from_user_id' => $fromUserId,
                    'to_user_id' => $toUserId,
                    'price_id' => $price->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("User prices copied", [
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'copied_count' => $copiedCount
        ]);

        return $copiedCount;
    }
}
