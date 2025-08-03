<?php

namespace App\Services;

use App\Models\ShippingPrice;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserSpecificPricingImportService
{
    /**
     * Import giá riêng cho user từ CSV/JSON
     */
    public static function importFromData(array $data): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'summary' => []
        ];

        $processedUsers = [];

        foreach ($data as $index => $row) {
            $rowNumber = $index + 1;

            try {
                // Validate dữ liệu
                $validator = Validator::make($row, [
                    'user_email' => 'required|email|exists:users,email',
                    'product_id' => 'required|integer|exists:products,id',
                    'product_name' => 'required|string',
                    'variant_sku' => 'required|string',
                    'tiktok_1st' => 'nullable|numeric|min:0',
                    'tiktok_next' => 'nullable|numeric|min:0',
                    'seller_1st' => 'nullable|numeric|min:0',
                    'seller_next' => 'nullable|numeric|min:0',
                    'currency' => 'required|in:USD,VND,GBP',
                    'attr_name' => 'nullable|string',
                    'attr_value' => 'nullable|string'
                ]);

                if ($validator->fails()) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => $validator->errors()->toArray(),
                        'data' => $row
                    ];
                    continue;
                }

                // Tìm user
                $user = User::where('email', $row['user_email'])->first();
                if (!$user) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => ['user_email' => ['User not found']],
                        'data' => $row
                    ];
                    continue;
                }

                // Tìm product
                $product = Product::find($row['product_id']);
                if (!$product) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => ['product_id' => ['Product not found']],
                        'data' => $row
                    ];
                    continue;
                }

                // Tìm variant dựa trên SKU hoặc attributes
                $variant = null;

                // Thử tìm theo SKU trước
                if (!empty($row['variant_sku'])) {
                    $variant = ProductVariant::where('sku', $row['variant_sku'])
                        ->where('product_id', $row['product_id'])
                        ->first();
                }

                // Nếu không tìm thấy theo SKU, thử tìm theo attributes
                if (!$variant) {
                    $selectedAttributes = [];

                    // Tìm tất cả các cột attr_name và attr_value
                    $attrNames = [];
                    $attrValues = [];

                    foreach ($row as $key => $value) {
                        if (strpos($key, 'attr_name_') === 0) {
                            $index = substr($key, 10); // Lấy số sau 'attr_name_'
                            $attrNames[$index] = $value;
                        } elseif (strpos($key, 'attr_value_') === 0) {
                            $index = substr($key, 11); // Lấy số sau 'attr_value_'
                            $attrValues[$index] = $value;
                        }
                    }

                    // Kết hợp name và value
                    foreach ($attrNames as $index => $name) {
                        if (!empty($name) && isset($attrValues[$index]) && !empty($attrValues[$index])) {
                            $selectedAttributes[$name] = $attrValues[$index];
                        }
                    }

                    if (!empty($selectedAttributes)) {
                        $variant = ProductVariant::findVariantByAttributes($row['product_id'], $selectedAttributes);
                    }
                }

                if (!$variant) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => ['variant_sku' => ['Product variant not found for this product or attributes']],
                        'data' => $row
                    ];
                    continue;
                }

                // Thiết lập giá riêng cho từng method
                $methods = ['tiktok_1st', 'tiktok_next', 'seller_1st', 'seller_next'];
                $pricesSet = 0;

                foreach ($methods as $method) {
                    if (!empty($row[$method]) && is_numeric($row[$method])) {
                        UserSpecificPricingService::setUserPrice(
                            $user->id,
                            $variant->id,
                            $method,
                            $row[$method],
                            $row['currency']
                        );
                        $pricesSet++;
                    }
                }

                if ($pricesSet > 0) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => ['general' => ['No valid prices provided']],
                        'data' => $row
                    ];
                    continue;
                }

                // Thống kê theo user
                if (!isset($processedUsers[$user->id])) {
                    $processedUsers[$user->id] = [
                        'user_email' => $user->email,
                        'user_name' => $user->first_name . ' ' . $user->last_name,
                        'count' => 0
                    ];
                }
                $processedUsers[$user->id]['count']++;

                Log::info("User-specific prices imported", [
                    'row' => $rowNumber,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'variant_id' => $variant->id,
                    'variant_sku' => $variant->sku,
                    'method' => $row['method'],
                    'price' => $row['price'],
                    'currency' => $row['currency']
                ]);
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'errors' => ['general' => [$e->getMessage()]],
                    'data' => $row
                ];

                Log::error("Failed to import user-specific price", [
                    'row' => $rowNumber,
                    'data' => $row,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $results['summary'] = [
            'total_rows' => count($data),
            'processed_users' => $processedUsers
        ];

        return $results;
    }

    /**
     * Tạo template Excel để download
     */
    public static function generateTemplate(): string
    {
        $filename = 'user_specific_pricing_template_' . date('Y-m-d_H-i-s') . '.xlsx';
        $path = storage_path('app/public/' . $filename);

        try {
            // Tạo spreadsheet mới
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();

            // Header
            $headers = ['user_email', 'product_id', 'product_name', 'variant_sku', 'tiktok_1st', 'tiktok_next', 'seller_1st', 'seller_next', 'currency', 'attr_name_1', 'attr_value_1', 'attr_name_2', 'attr_value_2', 'attr_name_3', 'attr_value_3'];
            $worksheet->fromArray([$headers], null, 'A1');

            // Sample data
            $sampleData = [
                ['john.doe@example.com', '123', 'Product Name 1', 'PROD-001', '15.99', '12.50', '18.99', '14.50', 'USD', 'color', 'Black', 'size', 'M', 'material', 'Cotton'],
                ['jane.smith@example.com', '456', 'Product Name 2', 'PROD-002', '12.50', '10.00', '16.50', '13.00', 'USD', 'color', 'White', 'size', 'L', 'style', 'Sport']
            ];

            $worksheet->fromArray($sampleData, null, 'A2');

            // Auto-size columns
            foreach (range('A', 'O') as $column) {
                $worksheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Tạo Excel file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($path);

            return $filename;
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate Excel template: ' . $e->getMessage());
        }
    }

    /**
     * Export giá riêng của user ra CSV
     */
    public static function exportUserPrices(int $userId): string
    {
        $user = User::findOrFail($userId);
        $prices = UserSpecificPricingService::getAllUserPrices($userId);

        $filename = 'user_specific_pricing_' . $user->email . '_' . date('Y-m-d_H-i-s') . '.csv';
        $path = storage_path('app/public/' . $filename);

        $handle = fopen($path, 'w');

        // Header
        fputcsv($handle, ['user_email', 'product_id', 'product_name', 'variant_sku', 'tiktok_1st', 'tiktok_next', 'seller_1st', 'seller_next', 'currency', 'attr_name_1', 'attr_value_1', 'attr_name_2', 'attr_value_2', 'attr_name_3', 'attr_value_3']);

        // Group prices by variant
        $groupedPrices = [];
        foreach ($prices as $price) {
            $key = $price->variant->sku;
            if (!isset($groupedPrices[$key])) {
                $groupedPrices[$key] = [
                    'user_email' => $price->user->email,
                    'product_id' => $price->variant->product->id,
                    'product_name' => $price->variant->product->name,
                    'variant_sku' => $price->variant->sku,
                    'tiktok_1st' => '',
                    'tiktok_next' => '',
                    'seller_1st' => '',
                    'seller_next' => '',
                    'currency' => $price->currency,
                    'attr_name_1' => $price->variant->attributes->first()->name ?? '',
                    'attr_value_1' => $price->variant->attributes->first()->value ?? '',
                    'attr_name_2' => $price->variant->attributes->skip(1)->first()->name ?? '',
                    'attr_value_2' => $price->variant->attributes->skip(1)->first()->value ?? '',
                    'attr_name_3' => $price->variant->attributes->skip(2)->first()->name ?? '',
                    'attr_value_3' => $price->variant->attributes->skip(2)->first()->value ?? ''
                ];
            }
            $groupedPrices[$key][$price->method] = $price->price;
        }

        // Data
        foreach ($groupedPrices as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $filename;
    }

    /**
     * Export tất cả giá riêng ra CSV
     */
    public static function exportAllPrices(): string
    {
        $prices = ShippingPrice::whereNotNull('user_id')
            ->with(['user', 'variant'])
            ->get();

        $filename = 'all_user_specific_pricing_' . date('Y-m-d_H-i-s') . '.csv';
        $path = storage_path('app/public/' . $filename);

        $handle = fopen($path, 'w');

        // Header
        fputcsv($handle, ['user_email', 'user_name', 'product_id', 'product_name', 'variant_sku', 'tiktok_1st', 'tiktok_next', 'seller_1st', 'seller_next', 'currency', 'attr_name_1', 'attr_value_1', 'attr_name_2', 'attr_value_2', 'attr_name_3', 'attr_value_3']);

        // Group prices by user and variant
        $groupedPrices = [];
        foreach ($prices as $price) {
            $key = $price->user->email . '_' . $price->variant->sku;
            if (!isset($groupedPrices[$key])) {
                $groupedPrices[$key] = [
                    'user_email' => $price->user->email,
                    'user_name' => $price->user->first_name . ' ' . $price->user->last_name,
                    'product_id' => $price->variant->product->id,
                    'product_name' => $price->variant->product->name ?? '',
                    'variant_sku' => $price->variant->sku,
                    'tiktok_1st' => '',
                    'tiktok_next' => '',
                    'seller_1st' => '',
                    'seller_next' => '',
                    'currency' => $price->currency,
                    'attr_name_1' => $price->variant->attributes->first()->name ?? '',
                    'attr_value_1' => $price->variant->attributes->first()->value ?? '',
                    'attr_name_2' => $price->variant->attributes->skip(1)->first()->name ?? '',
                    'attr_value_2' => $price->variant->attributes->skip(1)->first()->value ?? '',
                    'attr_name_3' => $price->variant->attributes->skip(2)->first()->name ?? '',
                    'attr_value_3' => $price->variant->attributes->skip(2)->first()->value ?? ''
                ];
            }
            $groupedPrices[$key][$price->method] = $price->price;
        }

        // Data
        foreach ($groupedPrices as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $filename;
    }

    /**
     * Validate dữ liệu import
     */
    public static function validateImportData(array $data): array
    {
        $errors = [];

        if (empty($data)) {
            $errors[] = 'Không có dữ liệu để import';
            return $errors;
        }

        $requiredColumns = ['user_email', 'product_id', 'product_name', 'variant_sku', 'currency'];
        $optionalColumns = ['tiktok_1st', 'tiktok_next', 'seller_1st', 'seller_next'];
        $attributeColumns = ['attr_name_1', 'attr_value_1', 'attr_name_2', 'attr_value_2', 'attr_name_3', 'attr_value_3'];

        // Kiểm tra header
        $firstRow = $data[0] ?? [];
        foreach ($requiredColumns as $column) {
            if (!array_key_exists($column, $firstRow)) {
                $errors[] = "Thiếu cột bắt buộc: {$column}";
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        // Kiểm tra dữ liệu
        foreach ($data as $index => $row) {
            $rowNumber = $index + 1;

            // Kiểm tra email
            if (!empty($row['user_email'])) {
                $user = User::where('email', $row['user_email'])->first();
                if (!$user) {
                    $errors[] = "Dòng {$rowNumber}: Email '{$row['user_email']}' không tồn tại";
                }
            }

            // Kiểm tra product
            if (!empty($row['product_id'])) {
                $product = Product::find($row['product_id']);
                if (!$product) {
                    $errors[] = "Dòng {$rowNumber}: Product ID '{$row['product_id']}' không tồn tại";
                }
            }

            // Kiểm tra SKU
            if (!empty($row['variant_sku']) && !empty($row['product_id'])) {
                $variant = ProductVariant::where('sku', $row['variant_sku'])
                    ->where('product_id', $row['product_id'])
                    ->first();
                if (!$variant) {
                    $errors[] = "Dòng {$rowNumber}: SKU '{$row['variant_sku']}' không tồn tại cho product ID '{$row['product_id']}'";
                }
            }

            // Kiểm tra các cột giá
            foreach ($optionalColumns as $column) {
                if (!empty($row[$column]) && (!is_numeric($row[$column]) || $row[$column] < 0)) {
                    $errors[] = "Dòng {$rowNumber}: {$column} phải là số dương";
                }
            }

            // Kiểm tra currency
            if (!empty($row['currency']) && !in_array($row['currency'], ['USD', 'VND', 'GBP'])) {
                $errors[] = "Dòng {$rowNumber}: Currency '{$row['currency']}' không hợp lệ";
            }

            // Kiểm tra ít nhất một giá được cung cấp
            $hasPrice = false;
            foreach ($optionalColumns as $column) {
                if (!empty($row[$column]) && is_numeric($row[$column])) {
                    $hasPrice = true;
                    break;
                }
            }
            if (!$hasPrice) {
                $errors[] = "Dòng {$rowNumber}: Phải có ít nhất một giá được cung cấp";
            }
        }

        return $errors;
    }

    /**
     * Parse CSV file
     */
    public static function parseCsvFile($file): array
    {
        $data = [];
        $handle = fopen($file->getPathname(), 'r');

        // Đọc header
        $headers = fgetcsv($handle);

        // Đọc data
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= count($headers)) {
                $dataRow = array_combine($headers, $row);
                $data[] = $dataRow;
            }
        }

        fclose($handle);

        return $data;
    }

    /**
     * Parse Excel file (.xlsx, .xls)
     */
    public static function parseExcelFile($file): array
    {
        $data = [];

        try {
            // Sử dụng PhpSpreadsheet để đọc Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();

            // Lấy tất cả dữ liệu
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                return $data;
            }

            // Lấy header từ dòng đầu tiên
            $headers = array_map('trim', $rows[0]);

            // Xử lý các dòng dữ liệu (bỏ qua dòng header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Bỏ qua dòng trống
                if (empty(array_filter($row))) {
                    continue;
                }

                // Đảm bảo số cột bằng với header
                if (count($row) >= count($headers)) {
                    $dataRow = array_combine($headers, $row);
                    $data[] = $dataRow;
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to parse Excel file: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Preview dữ liệu (tối đa 10 dòng đầu)
     */
    public static function previewData(array $data, int $limit = 10): array
    {
        return array_slice($data, 0, $limit);
    }
}
