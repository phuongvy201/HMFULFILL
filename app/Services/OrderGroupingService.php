<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class OrderGroupingService
{
    /**
     * Gom nhóm sản phẩm theo shipping method và category
     *
     * @param Collection $variants Collection các ProductVariant
     * @param string|null $shippingMethod Shipping method (tiktok_label hoặc seller)
     * @return Collection Các nhóm sản phẩm đã được gom
     */
    public function groupVariantsByShippingAndCategory(Collection $variants, ?string $shippingMethod = null): Collection
    {
        // Gom nhóm theo category và shipping method
        $groupedVariants = $variants->groupBy(function ($variant) {
            return $variant->product->category_id;
        });

        $result = collect();

        foreach ($groupedVariants as $categoryId => $categoryVariants) {
            $firstItem = $categoryVariants->first();
            $remainingItems = $categoryVariants->slice(1);

            // Lấy thông tin giá shipping cho item đầu tiên
            $firstItemInfo = $firstItem->getOrderPriceInfo($shippingMethod, 1);

            // Lấy thông tin giá shipping cho các item còn lại
            $remainingItemsInfo = $remainingItems->map(function ($variant) use ($shippingMethod) {
                return $variant->getOrderPriceInfo($shippingMethod, 2);
            });

            $result->push([
                'category_id' => $categoryId,
                'first_item' => [
                    'variant' => $firstItem,
                    'shipping_info' => $firstItemInfo
                ],
                'remaining_items' => $remainingItems->map(function ($variant, $index) use ($remainingItemsInfo) {
                    return [
                        'variant' => $variant,
                        'shipping_info' => $remainingItemsInfo[$index]
                    ];
                })->values()
            ]);
        }

        return $result;
    }
}
