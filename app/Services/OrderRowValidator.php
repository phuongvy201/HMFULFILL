<?php

namespace App\Services;

use App\Models\ExcelOrder;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

class OrderRowValidator
{
    private array $validPositionsUK = ['Front', 'Back', 'Left sleeve', 'Right sleeve', 'Hem'];
    private array $validPositionsUS = ['Front', 'Back', 'Right Sleeve', 'Left Sleeve'];
    private array $validSizes = ['S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];
    private array $validImageMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];

    // Định nghĩa size và position theo loại sản phẩm
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

    public function validateRows(array $rows, string $warehouse): array
    {
        $errors = [];

        foreach ($rows as $index => $row) {
            if (!$this->hasRowData($row)) {
                continue;
            }

            $rowErrors = [];
            $excelRow = $index + 2;

            // Kiểm tra các trường bắt buộc
            $this->validateRequiredFields($row, $excelRow, $rowErrors);

            // Kiểm tra shipping method và comment
            $this->validateShippingMethodAndComment($row, $excelRow, $rowErrors);

            // Kiểm tra SKU
            $sku = trim($row['Q'] ?? '');
            $this->validateSku($sku, $warehouse, $excelRow, $rowErrors);

            // Kiểm tra position, mockup, design
            $this->validatePrintData($row, $sku, $warehouse, $excelRow, $rowErrors);

            if (!empty($rowErrors)) {
                $errors[$excelRow] = $rowErrors;
            }
        }

        return $errors;
    }

    private function hasRowData(array $row): bool
    {
        $requiredColumns = ['A', 'E', 'H', 'J', 'K', 'L', 'M', 'Q', 'S', 'X', 'Y', 'Z'];
        foreach ($requiredColumns as $col) {
            if (!empty(trim($row[$col] ?? ''))) {
                return true;
            }
        }
        return false;
    }

    private function validateRequiredFields(array $row, int $excelRow, array &$rowErrors): void
    {
        $externalId = trim($row['A'] ?? '');
        if (empty($externalId)) {
            $rowErrors[] = "Row $excelRow: Missing order code (External_ID).";
        } else {
            // Kiểm tra xem external_id đã tồn tại với status khác "cancelled"
            $existingOrder = ExcelOrder::where('external_id', $externalId)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($existingOrder) {
                $rowErrors[] = "Row $excelRow: External_ID '$externalId' already exists in the database with status '{$existingOrder->status}'.";
            }
        }

        if (empty(trim($row['E'] ?? ''))) {
            $rowErrors[] = "Row $excelRow: Missing recipient name (First Name).";
        }
        if (empty(trim($row['H'] ?? ''))) {
            $rowErrors[] = "Row $excelRow: Missing delivery address (Address 1).";
        }
        if (empty(trim($row['J'] ?? ''))) {
            $rowErrors[] = "Row $excelRow: Missing city (City).";
        }
        if (empty(trim($row['K'] ?? ''))) {
            $rowErrors[] = "Row $excelRow: Missing district/county (County).";
        }
        if (empty(trim($row['L'] ?? ''))) {
            $rowErrors[] = "Row $excelRow: Missing postal code (Postcode).";
        }
        if (empty(trim($row['M'] ?? ''))) {
            $rowErrors[] = "Row $excelRow: Missing country (Country).";
        }

        if (empty($row['S']) || !is_numeric($row['S']) || (int)$row['S'] <= 0) {
            $rowErrors[] = "Row $excelRow: Product quantity is invalid or empty.";
        }
    }

    private function validateShippingMethodAndComment(array $row, int $excelRow, array &$rowErrors): void
    {
        $comment = trim($row['P'] ?? '');
        $shippingMethod = trim($row['W'] ?? '');
        $shippingMethodLower = strtolower($shippingMethod);

        // Kiểm tra shipping method chỉ được phép là 'tiktok_label' hoặc 'Tiktok_label'
        if (!empty($shippingMethod) && !in_array($shippingMethod, ['tiktok_label', 'Tiktok_label'])) {
            $rowErrors[] = "Row $excelRow: Shipping method must be 'tiktok_label' or 'Tiktok_label', but got '$shippingMethod'.";
            return;
        }

        if ($shippingMethodLower === 'tiktok_label') {
            if (empty($comment)) {
                $rowErrors[] = "Row $excelRow: Shipping method is '$shippingMethod' but no label link found in comment.";
            } else {
                // Kiểm tra xem comment có phải là link hợp lệ hay không
                if (!filter_var($comment, FILTER_VALIDATE_URL)) {
                    $rowErrors[] = "Row $excelRow: Comment at column P must contain a valid URL for '$shippingMethod' shipping method.";
                } else {
                    // Kiểm tra xem link có chứa các domain không được phép
                    if (
                        str_contains(strtolower($comment), 'seller-uk.tiktok.com') ||
                        str_contains(strtolower($comment), 'seller-us.tiktok.com') ||
                        str_contains(strtolower($comment), 'seller.tiktok.com')
                    ) {
                        $rowErrors[] = "Row $excelRow: TikTok Seller links are not allowed in comment.";
                    }
                }
            }
        } else {
            // Nếu shipping method không phải 'tiktok_label', cột comment phải để trống
            if (!empty($comment)) {
                $rowErrors[] = "Row $excelRow: Comment at column P must be empty unless shipping method is 'tiktok_label' or 'Tiktok_label'.";
            }
        }
    }

    private function validateSku(string $sku, string $warehouse, int $excelRow, array &$rowErrors): void
    {
        if (empty($sku)) {
            $rowErrors[] = "Row $excelRow: Missing product code (SKU).";
            return;
        }

        $skuError = $this->validateSkuAndWarehouse($sku, $warehouse, $excelRow);
        if ($skuError) {
            $rowErrors[] = $skuError;
        }

        // Kiểm tra xem SKU được nhập có phải là twofifteen_sku hay flashship_sku không
        $variantWithTwofifteen = ProductVariant::where('twofifteen_sku', $sku)->first();
        $variantWithFlashship = ProductVariant::where('flashship_sku', $sku)->first();

        if ($variantWithTwofifteen || $variantWithFlashship) {
            $rowErrors[] = "Row $excelRow: Product code (SKU) does not exist in the system: '$sku'.";
            return;
        }

        // Chỉ cho phép SKU chính
        $variant = ProductVariant::where('sku', $sku)->first();
        if (!$variant) {
            $rowErrors[] = "Row $excelRow: Product code (SKU) does not exist in the system: '$sku'.";
        }
    }

    private function validatePrintData(array $row, string $sku, string $warehouse, int $excelRow, array &$rowErrors): void
    {
        $productType = $this->getProductTypeFromSku($sku);
        $requiredPrintCount = $this->getRequiredPrintCount($sku);

        $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
        $mockupCols = ['Y', 'AB', 'AE', 'AH', 'AK'];
        $designCols = ['Z', 'AC', 'AF', 'AI', 'AL'];

        $hasPosition = false;
        $hasMockup = false;
        $hasDesign = false;

        $positionCount = 0;
        $mockupCount = 0;
        $designCount = 0;

        for ($i = 0; $i < 5; $i++) {
            $positionCol = $positionCols[$i];
            $mockupCol = $mockupCols[$i];
            $designCol = $designCols[$i];

            if (!empty($row[$positionCol])) {
                $hasPosition = true;
                $positionCount++;
                $position = trim($row[$positionCol]);

                $positionError = $this->validatePosition($position, $warehouse, $excelRow, $positionCol, $productType);
                if ($positionError) {
                    $rowErrors[] = $positionError;
                }
            }

            if (!empty($row[$mockupCol])) {
                $hasMockup = true;
                $mockupCount++;
                $url = trim($row[$mockupCol]);

                $this->validateImageUrl($url, $excelRow, $mockupCol, 'mockup', $rowErrors);
            }

            if (!empty($row[$designCol])) {
                $hasDesign = true;
                $designCount++;
                $url = trim($row[$designCol]);

                $this->validateImageUrl($url, $excelRow, $designCol, 'design', $rowErrors);
            }
        }

        // Kiểm tra số lượng position, mockup, design
        if ($requiredPrintCount !== null) {
            // SKU có 1S hoặc 2S
            if ($positionCount !== $requiredPrintCount) {
                $rowErrors[] = "Row $excelRow: SKU '$sku' requires exactly $requiredPrintCount print position(s), but $positionCount provided.";
            }
            if ($mockupCount !== $requiredPrintCount) {
                $rowErrors[] = "Row $excelRow: SKU '$sku' requires exactly $requiredPrintCount mockup(s), but $mockupCount provided.";
            }
            if ($designCount !== $requiredPrintCount) {
                $rowErrors[] = "Row $excelRow: SKU '$sku' requires exactly $requiredPrintCount design(s), but $designCount provided.";
            }
        } else {
            // Logic mặc định cho các sản phẩm khác
            if ($positionCount > 0 && $mockupCount > 0 && $designCount > 0) {
                if ($positionCount !== $mockupCount || $positionCount !== $designCount || $mockupCount !== $designCount) {
                    $rowErrors[] = "Row $excelRow: Số lượng position ($positionCount), mockup ($mockupCount) và design ($designCount) phải bằng nhau.";
                }
            }

            if (!$hasPosition) {
                $rowErrors[] = "Row $excelRow: At least one print position is required.";
            }
            if (!$hasMockup) {
                $rowErrors[] = "Row $excelRow: At least one mockup URL is required.";
            }
            if (!$hasDesign) {
                $rowErrors[] = "Row $excelRow: At least one design URL is required.";
            }
        }
    }

    private function validateSkuAndWarehouse(string $sku, string $warehouse, int $excelRow): ?string
    {
        $skuParts = explode('-', $sku);
        $skuSuffix = end($skuParts);

        if ($skuSuffix === 'UK' && $warehouse !== 'UK') {
            return "Row $excelRow: SKU '$sku' is for UK warehouse but selected warehouse is $warehouse";
        }
        if ($skuSuffix === 'US' && $warehouse !== 'US') {
            return "Row $excelRow: SKU '$sku' is for US warehouse but selected warehouse is $warehouse";
        }
        return null;
    }

    private function validatePosition(string $position, string $warehouse, int $excelRow, string $positionCol, string $productType): ?string
    {
        $validSizes = $this->validSizesByProductType[$productType] ?? $this->validSizesByProductType['Default'];
        $validPositions = $this->validPositionsByProductType[$productType] ?? $this->validPositionsByProductType['Default'];

        if ($warehouse === 'UK') {
            if (!in_array($position, $validPositions)) {
                return "Row $excelRow: Invalid print position at column $positionCol: '$position'. Valid values for $productType in UK warehouse are: " . implode(', ', $validPositions);
            }
        } elseif ($warehouse === 'US') {
            if (str_contains($position, '(Special)')) {
                if ($productType !== 'Default') {
                    return "Row $excelRow: Special position format is not allowed for $productType at column $positionCol.";
                }
                $parts = explode('-', str_replace(' (Special)', '', $position));
                if (count($parts) !== 2 || trim($parts[1]) !== 'Front') {
                    return "Row $excelRow: Invalid print position format at column $positionCol: '$position'. For $productType in US warehouse, Special position must be in format 'size-Front (Special)'.";
                }
                $size = trim($parts[0]);
                if (!in_array($size, $validSizes)) {
                    return "Row $excelRow: Invalid size '$size' in position at column $positionCol. Valid sizes for $productType are: " . implode(', ', $validSizes);
                }
            } else {
                $parts = explode('-', $position);
                if (count($parts) !== 2) {
                    return "Row $excelRow: Invalid print position format at column $positionCol: '$position'. For $productType in US warehouse, position must be in format 'size-side'.";
                }

                $size = trim($parts[0]);
                $side = trim($parts[1]);

                if (!in_array($size, $validSizes)) {
                    return "Row $excelRow: Invalid size '$size' in position at column $positionCol. Valid sizes for $productType are: " . implode(', ', $validSizes);
                }

                if (!in_array($side, $validPositions)) {
                    return "Row $excelRow: Invalid side '$side' in position at column $positionCol. Valid sides for $productType in US warehouse are: " . implode(', ', $validPositions);
                }
            }
        }
        return null;
    }

    private function validateImageUrl(string $url, int $excelRow, string $col, string $type, array &$rowErrors): void
    {
        if (str_contains($url, 'drive.google.com')) {
            if (!str_contains($url, '/file/d/')) {
                $rowErrors[] = "Row $excelRow: Google Drive link for $type at column $col must be a sharing link.";
            }
        } else {
            if (!$this->isValidImageMime($url)) {
                $rowErrors[] = "Row $excelRow: File at column $col is not a valid image (JPG, JPEG, PNG).";
            }
        }
    }

    private function isValidImageMime(string $url): bool
    {
        $headers = @get_headers($url, 1);
        if (!$headers) return false;
        $mime = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : '';
        return in_array(strtolower($mime), $this->validImageMimeTypes);
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
        } elseif (str_starts_with($sku, 'TOTEBAG') || str_starts_with($sku, 'MUG')) {
            return 'Tote Bag';
        } elseif (str_starts_with($sku, 'MUG')) {
            return 'Mug';
        }
        return 'Default';
    }

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
