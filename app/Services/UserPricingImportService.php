<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Models\ShippingOverride;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserPricingImportService
{
    /**
     * Import user pricing từ file Excel
     */
    public function importFromFile($file): array
    {
        $results = [
            'total_rows' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'details' => []
        ];

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rowIterator = $worksheet->getRowIterator(2); // Bỏ qua dòng tiêu đề

            DB::beginTransaction();

            foreach ($rowIterator as $row) {
                $results['total_rows']++;
                $rowIndex = $row->getRowIndex();

                try {
                    $cells = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $cells[] = $cell->getValue();
                    }

                    // Bỏ qua nếu dòng hoàn toàn trống
                    if (empty(array_filter($cells))) {
                        continue;
                    }

                    $result = $this->processRow($cells, $rowIndex);

                    if ($result['success']) {
                        $results['success_count']++;
                        $results['details'][] = $result['details'];
                    } else {
                        $results['error_count']++;
                        $results['errors'][] = $result['error'];
                    }
                } catch (\Exception $e) {
                    $results['error_count']++;
                    $results['errors'][] = [
                        'row' => $rowIndex,
                        'message' => $e->getMessage(),
                        'data' => $cells ?? []
                    ];
                    Log::error("Lỗi khi xử lý dòng $rowIndex: " . $e->getMessage());
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xử lý file Excel User Pricing: ' . $e->getMessage());
            throw $e;
        }

        return $results;
    }

    /**
     * Xử lý một dòng dữ liệu
     */
    private function processRow(array $cells, int $rowIndex): array
    {
        $userIdsRaw = $cells[0] ?? '';
        $productName = $cells[1] ?? '';
        $variantSku = $cells[2] ?? '';
        $tiktok1stPrice = (float)($cells[3] ?? 0);
        $tiktokNextPrice = (float)($cells[4] ?? 0);
        $seller1stPrice = (float)($cells[5] ?? 0);
        $sellerNextPrice = (float)($cells[6] ?? 0);
        $currency = $cells[7] ?? 'USD';

        // Validate dữ liệu
        if (empty($userIdsRaw) || !$productName || !$variantSku) {
            return [
                'success' => false,
                'error' => [
                    'row' => $rowIndex,
                    'message' => 'Dữ liệu không hợp lệ - thiếu thông tin bắt buộc',
                    'data' => $cells
                ]
            ];
        }

        // Kiểm tra ít nhất một giá phải > 0
        if ($tiktok1stPrice <= 0 && $tiktokNextPrice <= 0 && $seller1stPrice <= 0 && $sellerNextPrice <= 0) {
            return [
                'success' => false,
                'error' => [
                    'row' => $rowIndex,
                    'message' => 'Ít nhất một giá phải lớn hơn 0',
                    'data' => $cells
                ]
            ];
        }

        // Xử lý user IDs (hỗ trợ nhiều format: 123,456,789 hoặc 123;456;789)
        $userIds = [];
        $userIdsArray = preg_split('/[,;]/', $userIdsRaw);

        foreach ($userIdsArray as $userIdStr) {
            $userId = (int)trim($userIdStr);
            if ($userId > 0) {
                // Kiểm tra user có tồn tại không
                $user = User::find($userId);
                if (!$user) {
                    return [
                        'success' => false,
                        'error' => [
                            'row' => $rowIndex,
                            'message' => "User ID $userId không tồn tại",
                            'data' => $cells
                        ]
                    ];
                }
                $userIds[] = $userId;
            }
        }

        if (empty($userIds)) {
            return [
                'success' => false,
                'error' => [
                    'row' => $rowIndex,
                    'message' => 'Không có User ID hợp lệ',
                    'data' => $cells
                ]
            ];
        }

        // Tìm variant theo SKU
        $variant = ProductVariant::where('sku', $variantSku)
            ->orWhere('twofifteen_sku', $variantSku)
            ->orWhere('flashship_sku', $variantSku)
            ->first();

        if (!$variant) {
            return [
                'success' => false,
                'error' => [
                    'row' => $rowIndex,
                    'message' => "Variant SKU '$variantSku' không tồn tại",
                    'data' => $cells
                ]
            ];
        }

        $processedUsers = [];
        $methods = [
            'tiktok_1st' => $tiktok1stPrice,
            'tiktok_next' => $tiktokNextPrice,
            'seller_1st' => $seller1stPrice,
            'seller_next' => $sellerNextPrice
        ];

        // Xử lý từng method có giá > 0
        foreach ($methods as $method => $price) {
            if ($price > 0) {
                // Tìm hoặc tạo shipping price cơ bản
                $shippingPrice = ShippingPrice::firstOrCreate([
                    'variant_id' => $variant->id,
                    'method' => $method
                ], [
                    'price' => 0,
                    'currency' => $currency
                ]);

                // Tìm tất cả overrides hiện có cho shipping_price_id này
                $existingOverrides = ShippingOverride::where('shipping_price_id', $shippingPrice->id)->get();

                // Tạo map để theo dõi user đã được xử lý
                $processedUserIds = [];

                // Xử lý từng user_id
                foreach ($userIds as $userId) {
                    $found = false;

                    // Tìm trong existing overrides
                    foreach ($existingOverrides as $existingOverride) {
                        $existingUserIds = $existingOverride->user_ids;

                        // Xử lý user_ids nếu là string JSON
                        if (is_string($existingUserIds)) {
                            $existingUserIds = json_decode($existingUserIds, true) ?: [];
                        }

                        // Kiểm tra xem user_id có trong override này không
                        if (is_array($existingUserIds) && in_array($userId, $existingUserIds)) {
                            // Cập nhật override hiện tại
                            $existingOverride->update([
                                'override_price' => $price,
                                'currency' => $currency
                            ]);
                            $processedUsers[] = "User $userId - $method (cập nhật)";
                            $found = true;
                            break;
                        }
                    }

                    // Nếu không tìm thấy, tạo override mới
                    if (!$found) {
                        ShippingOverride::create([
                            'shipping_price_id' => $shippingPrice->id,
                            'user_ids' => json_encode([$userId]),
                            'override_price' => $price,
                            'currency' => $currency
                        ]);
                        $processedUsers[] = "User $userId - $method (tạo mới)";
                    }

                    $processedUserIds[] = $userId;
                }
            }
        }

        return [
            'success' => true,
            'details' => [
                'row' => $rowIndex,
                'variant_id' => $variant->id,
                'variant_sku' => $variantSku,
                'processed_users' => $processedUsers
            ]
        ];
    }

    /**
     * Export template Excel
     */
    public function exportTemplate(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tiêu đề
        $sheet->setCellValue('A1', 'User ID');
        $sheet->setCellValue('B1', 'Product Name');
        $sheet->setCellValue('C1', 'Variant SKU');
        $sheet->setCellValue('D1', 'TikTok 1st Price');
        $sheet->setCellValue('E1', 'TikTok Next Price');
        $sheet->setCellValue('F1', 'Seller 1st Price');
        $sheet->setCellValue('G1', 'Seller Next Price');
        $sheet->setCellValue('H1', 'Currency');

        // Dữ liệu mẫu
        $sheet->setCellValue('A2', '123,456,789');
        $sheet->setCellValue('B2', 'Product A');
        $sheet->setCellValue('C2', 'SKU001');
        $sheet->setCellValue('D2', '12.50');
        $sheet->setCellValue('E2', '15.00');
        $sheet->setCellValue('F2', '18.75');
        $sheet->setCellValue('G2', '20.00');
        $sheet->setCellValue('H2', 'USD');

        $sheet->setCellValue('A3', '123;456');
        $sheet->setCellValue('B3', 'Product B');
        $sheet->setCellValue('C3', 'SKU002');
        $sheet->setCellValue('D3', '10.00');
        $sheet->setCellValue('E3', '13.50');
        $sheet->setCellValue('F3', '16.25');
        $sheet->setCellValue('G3', '18.50');
        $sheet->setCellValue('H3', 'USD');

        $sheet->setCellValue('A4', '456');
        $sheet->setCellValue('B4', 'Product C');
        $sheet->setCellValue('C4', 'SKU003');
        $sheet->setCellValue('D4', '8.75');
        $sheet->setCellValue('E4', '0'); // Không có giá cho TikTok Next
        $sheet->setCellValue('F4', '12.00');
        $sheet->setCellValue('G4', '0'); // Không có giá cho Seller Next
        $sheet->setCellValue('H4', 'USD');

        // Định dạng
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(10);

        // Tạo file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'user_pricing_template.xlsx';
        $filepath = storage_path('app/public/' . $filename);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Lấy danh sách user để hiển thị trong form
     */
    public function getUsersList(): array
    {
        return User::select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'text' => "{$user->first_name} {$user->last_name} ({$user->email})"
                ];
            })
            ->toArray();
    }

    /**
     * Lấy danh sách variants để hiển thị trong form
     */
    public function getVariantsList(): array
    {
        return ProductVariant::with('product')
            ->select('id', 'sku', 'twofifteen_sku', 'flashship_sku', 'product_id')
            ->orderBy('sku')
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'text' => "{$variant->sku} - " . ($variant->product->name ?? 'N/A')
                ];
            })
            ->toArray();
    }
}
