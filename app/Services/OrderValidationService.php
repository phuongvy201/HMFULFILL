<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

class OrderValidationService
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

    private array $validPositionsByProductType = [
        'BabyOnesie' => ['Front', 'Back'],
        'Magnet' => ['Front'],
        'Diecut-Magnet' => ['Front'],
        'UV Sticker' => ['Front'],
        'Vinyl Sticker' => ['Front'],
        'Phone Case' => ['Front'],
        'Tote Bag' => ['Front', 'Back'],
        'Mug' => ['Front'],
        'Default' => ['Front', 'Back', 'Right Sleeve', 'Left Sleeve']
    ];

    private array $validImageMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];

    /**
     * Validate customer manual order
     */
    public function validateCustomerManualOrder(array $validated): array
    {
        $errors = [];

        // Validate shipping method and comment
        $this->validateShippingMethodAndComment(
            $validated['shipping_method'] ?? '',
            $validated['order_note'] ?? '',
            $errors
        );

        // Validate each product
        foreach ($validated['products'] as $productIndex => $product) {
            $variant = ProductVariant::find($product['variant_id']);
            if (!$variant) {
                $errors[] = "Product #" . ($productIndex + 1) . ": Invalid variant ID.";
                continue;
            }

            $sku = $variant->sku;
            $warehouse = $validated['warehouse'];

            // Validate SKU and warehouse compatibility
            $this->validateSkuAndWarehouse($sku, $warehouse, $productIndex, $errors);

            // Validate print data (designs and mockups)
            $this->validatePrintData($product, $sku, $warehouse, $productIndex, $errors);
        }

        return $errors;
    }

    /**
     * Validate shipping method and comment
     */
    private function validateShippingMethodAndComment(string $shippingMethod, string $orderNote, array &$errors): void
    {
        $shippingMethodLower = strtolower($shippingMethod);

        // Kiểm tra shipping method chỉ được phép là 'tiktok_label' hoặc 'Tiktok_label'
        if (!empty($shippingMethod) && !in_array($shippingMethod, ['tiktok_label', 'Tiktok_label'])) {
            $errors[] = "Shipping method must be 'tiktok_label' or 'Tiktok_label', but got '$shippingMethod'.";
            return;
        }

        if ($shippingMethodLower === 'tiktok_label') {
            if (empty($orderNote)) {
                $errors[] = "Shipping method is '$shippingMethod' but no label link found in comment.";
            } else {
                // Kiểm tra xem comment có phải là link hợp lệ hay không
                if (!filter_var($orderNote, FILTER_VALIDATE_URL)) {
                    $errors[] = "Comment must contain a valid URL for '$shippingMethod' shipping method.";
                } else {
                    // Kiểm tra xem link có chứa các domain không được phép
                    if (
                        str_contains(strtolower($orderNote), 'seller-uk.tiktok.com') ||
                        str_contains(strtolower($orderNote), 'seller-us.tiktok.com') ||
                        str_contains(strtolower($orderNote), 'seller.tiktok.com')
                    ) {
                        $errors[] = "TikTok Seller links are not allowed in comment.";
                    }
                }
            }
        } else {
            // Nếu shipping method không phải 'tiktok_label', cột comment phải để trống
            if (!empty($orderNote)) {
                $errors[] = "Comment must be empty unless shipping method is 'tiktok_label' or 'Tiktok_label'.";
            }
        }
    }

    /**
     * Validate SKU and warehouse compatibility
     */
    private function validateSkuAndWarehouse(string $sku, string $warehouse, int $productIndex, array &$errors): void
    {
        $skuParts = explode('-', $sku);
        $skuSuffix = end($skuParts);

        if ($skuSuffix === 'UK' && $warehouse !== 'UK') {
            $errors[] = "Product #" . ($productIndex + 1) . ": SKU '$sku' is for UK warehouse but selected warehouse is $warehouse";
        }
        if ($skuSuffix === 'US' && $warehouse !== 'US') {
            $errors[] = "Product #" . ($productIndex + 1) . ": SKU '$sku' is for US warehouse but selected warehouse is $warehouse";
        }
    }

    /**
     * Validate print data (designs and mockups)
     */
    private function validatePrintData(array $product, string $sku, string $warehouse, int $productIndex, array &$errors): void
    {
        $productType = $this->getProductTypeFromSku($sku);
        $requiredPrintCount = $this->getRequiredPrintCount($sku);

        $designs = $product['designs'] ?? [];
        $mockups = $product['mockups'] ?? [];

        // Validate số lượng designs và mockups
        if ($requiredPrintCount !== null) {
            // SKU có 1S, 2S, 3S, 4S, 5S
            if (count($designs) !== $requiredPrintCount) {
                $errors[] = "Product #" . ($productIndex + 1) . ": SKU '$sku' requires exactly $requiredPrintCount design(s), but provided " . count($designs) . ".";
            }
            if (count($mockups) !== $requiredPrintCount) {
                $errors[] = "Product #" . ($productIndex + 1) . ": SKU '$sku' requires exactly $requiredPrintCount mockup(s), but provided " . count($mockups) . ".";
            }
        } else {
            // Logic mặc định cho các sản phẩm khác
            if (count($designs) > 0 && count($mockups) > 0) {
                if (count($designs) !== count($mockups)) {
                    $errors[] = "Product #" . ($productIndex + 1) . ": Number of designs (" . count($designs) . ") and mockups (" . count($mockups) . ") must be equal.";
                }
            }

            if (empty($designs)) {
                $errors[] = "Product #" . ($productIndex + 1) . ": At least one design URL is required.";
            }
            if (empty($mockups)) {
                $errors[] = "Product #" . ($productIndex + 1) . ": At least one mockup URL is required.";
            }
        }

        // Validate từng design và mockup
        foreach ($designs as $designIndex => $design) {
            $this->validateImageUrl($design['file_url'], $productIndex, $designIndex, 'design', $errors);
            $this->validatePrintSpace($design['print_space'], $warehouse, $productType, $productIndex, $designIndex, $errors);
        }

        foreach ($mockups as $mockupIndex => $mockup) {
            $this->validateImageUrl($mockup['file_url'], $productIndex, $mockupIndex, 'mockup', $errors);
            $this->validatePrintSpace($mockup['print_space'], $warehouse, $productType, $productIndex, $mockupIndex, $errors);
        }
    }

    /**
     * Validate image URL
     */
    private function validateImageUrl(string $url, int $productIndex, int $itemIndex, string $type, array &$errors): void
    {
        if (str_contains($url, 'drive.google.com')) {
            if (!str_contains($url, '/file/d/')) {
                $errors[] = "Product #" . ($productIndex + 1) . " $type #" . ($itemIndex + 1) . ": Google Drive link must be a sharing link.";
            }
        } else {
            if (!$this->isValidImageMime($url)) {
                $errors[] = "Product #" . ($productIndex + 1) . " $type #" . ($itemIndex + 1) . ": File is not a valid image (JPG, JPEG, PNG).";
            }
        }
    }

    /**
     * Validate print space
     */
    private function validatePrintSpace(string $printSpace, string $warehouse, string $productType, int $productIndex, int $itemIndex, array &$errors): void
    {
        $validSizes = $this->validSizesByProductType[$productType] ?? $this->validSizesByProductType['Default'];
        $validPositions = $this->validPositionsByProductType[$productType] ?? $this->validPositionsByProductType['Default'];

        if ($warehouse === 'UK') {
            // UK warehouse: chỉ cho phép position đơn giản (Front, Back, Left Sleeve, Right Sleeve)
            if (!in_array($printSpace, $validPositions)) {
                $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid print position '$printSpace'. For UK warehouse, position must be simple format. Valid values for $productType are: " . implode(', ', $validPositions);
            }
        } elseif ($warehouse === 'US') {
            // US warehouse: chỉ cho phép format size-side (S-Front, Tote Bag-Front, v.v.)
            if (str_contains($printSpace, '(Special)')) {
                // Special format (e.g. S-Front (Special))
                if ($productType !== 'Default') {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Special position format is not allowed for $productType.";
                }
                $parts = explode('-', str_replace(' (Special)', '', $printSpace));
                if (count($parts) !== 2 || trim($parts[1]) !== 'Front') {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid print position format '$printSpace'. For $productType in US warehouse, Special position must be in format 'size-Front (Special)'.";
                }
                $size = trim($parts[0]);
                if (!in_array($size, $validSizes)) {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid size '$size' in position. Valid sizes for $productType are: " . implode(', ', $validSizes);
                }
            } elseif (str_contains($printSpace, '-')) {
                // Size-position format (e.g. S-Front, Tote Bag-Front)
                $parts = explode('-', $printSpace);
                if (count($parts) !== 2) {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid print position format '$printSpace'. For US warehouse, position must be in format 'size-side' (e.g. S-Front, Tote Bag-Front).";
                }

                $size = trim($parts[0]);
                $side = trim($parts[1]);

                if (!in_array($size, $validSizes)) {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid size '$size' in position. Valid sizes for $productType are: " . implode(', ', $validSizes);
                }

                if (!in_array($side, $validPositions)) {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid side '$side' in position. Valid sides for $productType in US warehouse are: " . implode(', ', $validPositions);
                }
            } else {
                // Không cho phép position đơn giản cho US warehouse
                $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid print position '$printSpace'. For US warehouse, position must be in format 'size-side' (e.g. S-Front, Tote Bag-Front). Valid sizes for $productType are: " . implode(', ', $validSizes) . " and valid sides are: " . implode(', ', $validPositions);
            }
        }
    }

    /**
     * Validate image MIME type
     */
    private function isValidImageMime(string $url): bool
    {
        $headers = @get_headers($url, 1);
        if (!$headers) return false;
        $mime = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : '';
        return in_array(strtolower($mime), $this->validImageMimeTypes);
    }

    /**
     * Get product type from SKU
     */
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

    /**
     * Get required print count from SKU
     */
    private function getRequiredPrintCount(string $sku): ?int
    {
        $skuParts = explode('-', $sku);
        $printCountIndicator = $skuParts[count($skuParts) - 2] ?? '';
        if ($printCountIndicator === '1S') {
            return 1;
        } elseif ($printCountIndicator === '2S') {
            return 2;
        }
        if ($printCountIndicator === '3S') {
            return 3;
        }
        if ($printCountIndicator === '4S') {
            return 4;
        }
        if ($printCountIndicator === '5S') {
            return 5;
        }
        return null; // Không có yêu cầu cụ thể
    }
}
