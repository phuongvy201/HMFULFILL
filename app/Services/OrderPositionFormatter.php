<?php

namespace App\Services;

use App\Models\ProductVariant;

class OrderPositionFormatter
{
    private array $validSizesByProductType = [
        'BabyOnesie' => ['0-6', '6-12', '12-18', '18-24'],
        'Magnet' => ['5x5', '7.5x4.5', '10x3'],
        'Diecut-Magnet' => ['2x2', '3x3', '4x4', '5x5', '6x6'],
        'UV Sticker' => ['2x2', '3x3', '4x4', '5x5', '6x6', '7x7', '8x8', '9x9', '10x10', '12x12', '15x15', '18x18', '20x20'],
        'Vinyl Sticker' => ['3x4', '6x8', '8x10'],
        'Phone Case' => ['15', '15 Pro', '15 Pro Max', '15 Plus', '16', '16 Plus', '16 Pro', '16 Pro Max'],
        'Tote Bag' => ['Tote Bag'],
        'Mug' => ['Mug'],
        'Default' => ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL']
    ];

    public function formatPositions(array $orderData): array
    {
        // No formatting needed - positions are entered manually
        // Validation will be handled in the controller
        return $orderData;
    }

    private function formatPosition(string $position, array $validSizes, string $selectedSize = ''): string
    {
        // If position is already in size-position format, return as is
        if (str_contains($position, '-')) {
            return $position;
        }

        // For US warehouse, format simple position to size-position
        // Use selected size if provided, otherwise use first valid size as default
        $size = !empty($selectedSize) ? $selectedSize : ($validSizes[0] ?? 'S');
        return $size . '-' . $position;
    }

    private function getProductTypeFromSku(string $sku): string
    {
        if (str_starts_with($sku, 'OS01')) {
            return 'BabyOnesie';
        } elseif (str_starts_with($sku, 'DIECUT-MAGNET')) {
            return 'Diecut-Magnet';
        } elseif (str_starts_with($sku, 'MAGNET')) {
            return 'Magnet';
        } elseif (str_starts_with($sku, 'UV-STICKER')) {
            return 'UV Sticker';
        } elseif (str_starts_with($sku, 'VINYL-STICKER')) {
            return 'Vinyl Sticker';
        } elseif (str_starts_with($sku, 'CASE-IPHONE')) {
            return 'Phone Case';
        } elseif (str_starts_with($sku, 'TOTEBAG')) {
            return 'Tote Bag';
        } elseif (str_starts_with($sku, 'MUG')) {
            return 'Mug';
        }
        return 'Default';
    }
}
