<?php

namespace App\Services;

use App\Helpers\GoogleDriveHelper;
use App\Models\ExcelOrder;
use App\Models\ExcelOrderItem;
use App\Models\ExcelOrderMockup;
use App\Models\ExcelOrderDesign;
use App\Models\ImportFile;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Models\ExcelOrderFulfillment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Wallet;
use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ExcelOrderImportService
{
    private function getPositionTitle(string $position): string
    {
        return match ($position) {
            'Front' => 'Printing Front Side',
            'Back' => 'Printing Back Side',
            'Left sleeve' => 'Printing Left Sleeve Side',
            'Right sleeve' => 'Printing Right Sleeve Side',
            'Hem' => 'Printing Hem Side'
        };
    }

    private function processUrls(array $urls, string $position): array
    {
        if (empty($urls)) {
            return [];
        }

        $title = $this->getPositionTitle($position);
        return array_map(fn($url) => [
            'title' => $title,
            'url' => $url
        ], array_filter($urls));
    }

    public function process(ImportFile $importFile, array $rows)
    {
        // Validate dữ liệu trước
        $errors = $this->validateRowsWithoutSkuCheck($rows, $importFile);
        if (!empty($errors)) {
            $importFile->update([
                'status' => 'failed',
                'error_logs' => $errors,
                'total_rows' => count($rows),
                'processed_rows' => 0
            ]);
            return false;
        }
        // Tiếp tục xử lý nếu không có lỗi
        $ordersByExternalId = [];

        foreach ($rows as $row) {
            // Bỏ qua dòng nếu không có external_id hoặc part_number
            if (empty($row['A']) && empty($row['Q'])) {
                continue;
            }

            $externalId = $row['A'] ?? '';

            // Tìm hoặc tạo order dựa vào external_id
            if (!isset($ordersByExternalId[$externalId])) {
                $ordersByExternalId[$externalId] = ExcelOrder::create([
                    'external_id' => $externalId,
                    'brand' => $row['B'] ?? '',
                    'channel' => $row['C'] ?? '',
                    'buyer_email' => $row['D'] ?? '',
                    'first_name' => $row['E'] ?? '',
                    'last_name' => $row['F'] ?? '',
                    'company' => $row['G'] ?? '',
                    'address1' => $row['H'] ?? '',
                    'address2' => $row['I'] ?? '',
                    'city' => $row['J'] ?? '',
                    'county' => $row['K'] ?? '',
                    'post_code' => $row['L'] ?? '',
                    'country' => $row['M'] ?? '',
                    'phone1' => $row['N'] ?? '',
                    'phone2' => $row['O'] ?? '',
                    'comment' => $row['P'] ?? '',
                    'shipping_method' => $row['W'] ?? '',
                    'status' => 'pending',
                    'import_file_id' => $importFile->id
                ]);
            }

            $order = $ordersByExternalId[$externalId];

            // Tạo order item cho sản phẩm mới
            $orderItem = ExcelOrderItem::create([
                'excel_order_id' => $order->id,
                'part_number' => $row['Q'] ?? '',
                'title' => $row['R'] ?? '',
                'quantity' => (int)($row['S'] ?? 0),
                'description' => $row['T'] ?? '',
                'label_name' => $row['U'] ?? '',
                'label_type' => $row['V'] ?? '',
            ]);

            // Reset các mảng cho mỗi sản phẩm
            $positions = [];
            $mockupUrls = [];
            $designUrls = [];

            // Xử lý các vị trí in và URL tương ứng (đã dời sang phải 1 cột)
            $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
            $mockupCols   = ['Y', 'AB', 'AE', 'AH', 'AK'];
            $designCols   = ['Z', 'AC', 'AF', 'AI', 'AL'];

            for ($i = 0; $i < 5; $i++) {
                $positionCol = $positionCols[$i];
                $mockupCol   = $mockupCols[$i];
                $designCol   = $designCols[$i];

                if (!empty($row[$positionCol])) {
                    $positions[] = $row[$positionCol];
                    $mockupUrls[] = !empty($row[$mockupCol]) ?
                        (str_contains($row[$mockupCol], 'drive.google.com') ?
                            GoogleDriveHelper::convertToDirectDownloadLink($row[$mockupCol]) :
                            $row[$mockupCol]) : '';
                    $designUrls[] = !empty($row[$designCol]) ?
                        (str_contains($row[$designCol], 'drive.google.com') ?
                            GoogleDriveHelper::convertToDirectDownloadLink($row[$designCol]) :
                            $row[$designCol]) : '';
                }
            }

            // Tạo mockup và design tương ứng
            foreach ($positions as $index => $position) {
                if (!empty($mockupUrls[$index])) {
                    ExcelOrderMockup::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $this->getPositionTitle($position),
                        'url' => $mockupUrls[$index]
                    ]);
                }

                if (!empty($designUrls[$index])) {
                    ExcelOrderDesign::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $this->getPositionTitle($position),
                        'url' => $designUrls[$index]
                    ]);
                }
            }
        }
    }

    public function processCustomer(ImportFile $importFile, array $rows, string $warehouse)
    {
        // Validate dữ liệu trước
        $errors = $this->validateRows($rows, $importFile);
        if (!empty($errors)) {
            $importFile->update([
                'status' => 'failed',
                'error_logs' => $errors,
                'total_rows' => count($rows),
                'processed_rows' => 0
            ]);
            return false;
        }
        Log::info('Warehouse: ' . $warehouse);
        // Đầu tiên, tính tổng quantity cho mỗi đơn hàng
        $orderTotalQuantities = [];
        foreach ($rows as $row) {
            if (empty($row['A'])) continue;
            $externalId = $row['A'];
            if (!isset($orderTotalQuantities[$externalId])) {
                $orderTotalQuantities[$externalId] = 0;
            }
            $orderTotalQuantities[$externalId] += (int)($row['S'] ?? 0);
        }

        // Tính tổng số tiền cần trừ
        $totalAmount = 0;
        foreach ($rows as $row) {
            if (empty($row['A'])) continue;

            $externalId = $row['A'];
            $quantity = (int)($row['S'] ?? 0);
            $variant = ProductVariant::where('sku', $row['Q'])
                ->orWhere('twofifteen_sku', $row['Q'])
                ->orWhere('flashship_sku', $row['Q'])
                ->first();

            if ($variant) {
                $shippingMethod = !empty($row['W']) ? strtolower($row['W']) : '';
                $isTikTokLabel = str_contains($shippingMethod, 'tiktok_label');

                // Xác định method dựa trên tổng quantity của đơn hàng
                $method = $isTikTokLabel ?
                    ($orderTotalQuantities[$externalId] <= 1 ? ShippingPrice::METHOD_TIKTOK_1ST : ShippingPrice::METHOD_TIKTOK_NEXT) : ($orderTotalQuantities[$externalId] <= 1 ? ShippingPrice::METHOD_SELLER_1ST : ShippingPrice::METHOD_SELLER_NEXT);

                $shippingPrice = $variant->shippingPrices()->where('method', $method)->first();
                if ($shippingPrice) {
                    $totalAmount += $shippingPrice->price * $quantity;
                }
            }
        }

        // Kiểm tra và trừ tiền từ ví
        $wallet = Wallet::where('user_id', $importFile->user_id)->first();
        if (!$wallet || !$wallet->hasEnoughBalance($totalAmount)) {
            $importFile->update([
                'status' => 'failed',
                'error_logs' => ['Insufficient balance in wallet'],
            ]);
            return false;
        }

        // Tạo giao dịch trừ tiền
        $transaction = Transaction::create([
            'user_id' => $importFile->user_id,
            'transaction_code' => 'ORDER-' . time(),
            'type' => Transaction::TYPE_DEDUCT,
            'method' => Transaction::METHOD_VND,
            'amount' => $totalAmount,
            'status' => Transaction::STATUS_APPROVED,
            'note' => 'Deduct for order import: ' . $importFile->id,
            'approved_at' => now(),
        ]);

        // Trừ tiền từ ví
        if (!$wallet->withdraw($totalAmount)) {
            $transaction->reject('Failed to withdraw from wallet');
            $importFile->update([
                'status' => 'failed',
                'error_logs' => ['Failed to process payment'],
            ]);
            return false;
        }

        // Tiếp tục xử lý đơn hàng như bình thường
        $ordersByExternalId = [];
        $orderTotalPrices = [];
        $orderTotalQuantities = [];

        // Đầu tiên, tính tổng quantity cho mỗi đơn hàng
        foreach ($rows as $row) {
            if (empty($row['A'])) continue;
            $externalId = $row['A'];
            if (!isset($orderTotalQuantities[$externalId])) {
                $orderTotalQuantities[$externalId] = 0;
                $orderTotalPrices[$externalId] = 0;
            }
            $orderTotalQuantities[$externalId] += (int)($row['S'] ?? 0);
        }

        // Sau đó xử lý từng dòng và tính giá vận chuyển
        foreach ($rows as $row) {
            if (empty($row['A']) && empty($row['Q'])) {
                continue;
            }

            $externalId = $row['A'] ?? '';
            $order = $ordersByExternalId[$externalId] ?? null;

            if (!$order) {
                $order = ExcelOrder::create([
                    'external_id' => $externalId,
                    'brand' => $row['B'] ?? '',
                    'channel' => $row['C'] ?? '',
                    'buyer_email' => $row['D'] ?? '',
                    'first_name' => $row['E'] ?? '',
                    'last_name' => $row['F'] ?? '',
                    'company' => $row['G'] ?? '',
                    'address1' => $row['H'] ?? '',
                    'address2' => $row['I'] ?? '',
                    'city' => $row['J'] ?? '',
                    'county' => $row['K'] ?? '',
                    'post_code' => $row['L'] ?? '',
                    'country' => $row['M'] ?? '',
                    'phone1' => $row['N'] ?? '',
                    'phone2' => $row['O'] ?? '',
                    'comment' => $row['P'] ?? '',
                    'shipping_method' => $row['W'] ?? '',
                    'status' => 'pending',
                    'import_file_id' => $importFile->id
                ]);
                $ordersByExternalId[$externalId] = $order;
            }

            // Tạo order item
            $orderItem = ExcelOrderItem::create([
                'excel_order_id' => $order->id,
                'part_number' => $row['Q'] ?? '',
                'title' => $row['R'] ?? '',
                'quantity' => (int)($row['S'] ?? 0),
                'description' => $row['T'] ?? '',
                'label_name' => $row['U'] ?? '',
                'label_type' => $row['V'] ?? '',
            ]);

            // Tìm variant và lấy giá vận chuyển
            $variant = ProductVariant::where('sku', $row['Q'])
                ->orWhere('twofifteen_sku', $row['Q'])
                ->orWhere('flashship_sku', $row['Q'])
                ->first();

            if ($variant) {
                // Xác định phương thức vận chuyển
                $shippingMethod = !empty($row['W']) ? strtolower($row['W']) : '';
                $isTikTokLabel = str_contains($shippingMethod, 'tiktok_label');

                // Xác định method dựa trên tổng quantity của đơn hàng
                $method = $isTikTokLabel ?
                    ($orderTotalQuantities[$externalId] <= 1 ? ShippingPrice::METHOD_TIKTOK_1ST : ShippingPrice::METHOD_TIKTOK_NEXT) : ($orderTotalQuantities[$externalId] <= 1 ? ShippingPrice::METHOD_SELLER_1ST : ShippingPrice::METHOD_SELLER_NEXT);

                // Lấy giá vận chuyển
                $shippingPrice = $variant->shippingPrices()
                    ->where('method', $method)
                    ->first();

                if ($shippingPrice) {
                    // Cập nhật giá vận chuyển cho order item
                    $orderItem->update([
                        'shipping_price' => $shippingPrice->price,
                        'shipping_method' => $isTikTokLabel ? 'tiktok' : 'seller'
                    ]);

                    // Cộng dồn giá vào tổng giá của đơn hàng
                    $orderTotalPrices[$externalId] += $shippingPrice->price * (int)($row['S'] ?? 0);
                }

                // Sử dụng warehouse từ ImportFile để xác định SKU
                $sku = $variant->getSkuByWarehouse($warehouse);
                Log::info("Chọn SKU cho warehouse {$warehouse}", ['selected_sku' => $sku]);

                // Cập nhật part_number cho order item với SKU tương ứng
                if (!empty($sku)) {
                    $orderItem->update(['part_number' => $sku]);
                } else {
                    // Ghi log lỗi và bỏ qua dòng nếu không tìm thấy SKU hợp lệ
                    $errors[] = "Không tìm thấy SKU hợp lệ cho variant (SKU: {$row['Q']}, Warehouse: {$warehouse})";
                    Log::error('Không tìm thấy SKU hợp lệ cho variant', [
                        'sku' => $row['Q'],
                        'warehouse' => $warehouse,
                        'variant_id' => $variant->id
                    ]);
                    continue; // Bỏ qua dòng này để tránh xử lý dữ liệu không hợp lệ
                }
            }

            // Reset các mảng cho mỗi sản phẩm
            $positions = [];
            $mockupUrls = [];
            $designUrls = [];

            // Xử lý các vị trí in và URL tương ứng (đã dời sang phải 1 cột)
            $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
            $mockupCols   = ['Y', 'AB', 'AE', 'AH', 'AK'];
            $designCols   = ['Z', 'AC', 'AF', 'AI', 'AL'];

            for ($i = 0; $i < 5; $i++) {
                $positionCol = $positionCols[$i];
                $mockupCol   = $mockupCols[$i];
                $designCol   = $designCols[$i];

                if (!empty($row[$positionCol])) {
                    $positions[] = $row[$positionCol];
                    $mockupUrls[] = !empty($row[$mockupCol]) ?
                        (str_contains($row[$mockupCol], 'drive.google.com') ?
                            GoogleDriveHelper::convertToDirectDownloadLink($row[$mockupCol]) :
                            $row[$mockupCol]) : '';
                    $designUrls[] = !empty($row[$designCol]) ?
                        (str_contains($row[$designCol], 'drive.google.com') ?
                            GoogleDriveHelper::convertToDirectDownloadLink($row[$designCol]) :
                            $row[$designCol]) : '';
                }
            }

            // Tạo mockup và design tương ứng
            foreach ($positions as $index => $position) {
                if (!empty($mockupUrls[$index])) {
                    ExcelOrderMockup::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $this->getPositionTitle($position),
                        'url' => $mockupUrls[$index]
                    ]);
                }

                if (!empty($designUrls[$index])) {
                    ExcelOrderDesign::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $this->getPositionTitle($position),
                        'url' => $designUrls[$index]
                    ]);
                }
            }
        }

        // Tạo fulfillment cho mỗi đơn hàng
        foreach ($ordersByExternalId as $externalId => $order) {
            ExcelOrderFulfillment::create([
                'excel_order_id' => $order->id,
                'total_quantity' => $orderTotalQuantities[$externalId],
                'total_price' => $orderTotalPrices[$externalId],
                'status' => 'pending',
                'factory_response' => null,
                'error_message' => null
            ]);
        }

        return true; // Thêm return true khi xử lý thành công
    }

    public function validateRows(array $rows, ImportFile $importFile): array
    {
        $errors = [];
        $validPositions = ['Front', 'Back', 'Left sleeve', 'Right sleeve', 'Hem'];
        $validImageExtensions = ['jpg', 'jpeg', 'png'];

        foreach ($rows as $index => $row) {
            $rowErrors = [];

            // Dòng thực tế trong Excel (cộng 2 vì index bắt đầu từ 0 và dòng tiêu đề là dòng 1)
            $excelRow = $index + 2;

            // Kiểm tra các trường bắt buộc
            if (empty($row['A'])) {
                $rowErrors[] = "Row $excelRow: Missing order code (External_ID).";
            }
            if (empty($row['E'])) {
                $rowErrors[] = "Row $excelRow: Missing recipient name (First Name).";
            }
            if (empty($row['H'])) {
                $rowErrors[] = "Row $excelRow: Missing delivery address (Address 1).";
            }
            if (empty($row['J'])) {
                $rowErrors[] = "Row $excelRow: Missing city (City).";
            }
            if (empty($row['K'])) {
                $rowErrors[] = "Row $excelRow: Missing district/county (County).";
            }
            if (empty($row['L'])) {
                $rowErrors[] = "Row $excelRow: Missing postal code (Postcode).";
            }
            if (empty($row['M'])) {
                $rowErrors[] = "Row $excelRow: Missing country (Country).";
            }

            // Kiểm tra số lượng
            if (empty($row['S']) || !is_numeric($row['S']) || (int)$row['S'] <= 0) {
                $rowErrors[] = "Row $excelRow: Product quantity is invalid or empty.";
            }

            // Kiểm tra vị trí in
            $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
            foreach ($positionCols as $col) {
                if (!empty($row[$col]) && !in_array($row[$col], $validPositions)) {
                    $rowErrors[] = "Row $excelRow: Invalid print position at column $col: '{$row[$col]}'. Valid values are: " . implode(', ', $validPositions);
                }
            }

            // Kiểm tra hình ảnh mockup và design
            $mockupCols = ['Y', 'AB', 'AE', 'AH', 'AK'];
            $designCols = ['Z', 'AC', 'AF', 'AI', 'AL'];

            foreach ($mockupCols as $col) {
                if (!empty($row[$col])) {
                    $url = $row[$col];
                    // Kiểm tra nếu là link Google Drive
                    if (str_contains($url, 'drive.google.com')) {
                        if (!str_contains($url, '/file/d/')) {
                            $rowErrors[] = "Row $excelRow: Google Drive link for mockup at column $col must be a sharing link.";
                        }
                    } else {
                        // Kiểm tra extension của URL thông thường
                        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                        if (!in_array($extension, $validImageExtensions)) {
                            $rowErrors[] = "Row $excelRow: Format image is not valid for mockup at column $col. Only JPG, JPEG, PNG is accepted.";
                        }
                    }
                }
            }

            foreach ($designCols as $col) {
                if (!empty($row[$col])) {
                    $url = $row[$col];
                    // Kiểm tra nếu là link Google Drive
                    if (str_contains($url, 'drive.google.com')) {
                        if (!str_contains($url, '/file/d/')) {
                            $rowErrors[] = "Row $excelRow: Google Drive link for design at column $col must be a sharing link.";
                        }
                    } else {
                        // Kiểm tra extension của URL thông thường
                        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                        if (!in_array($extension, $validImageExtensions)) {
                            $rowErrors[] = "Row $excelRow: Format image is not valid for design at column $col. Only JPG, JPEG, PNG is accepted.";
                        }
                    }
                }
            }

            // Kiểm tra SKU
            if (!empty($row['Q'])) {
                $sku = $row['Q'];
                $variant = ProductVariant::where('sku', $sku)
                    ->orWhere('twofifteen_sku', $sku)
                    ->orWhere('flashship_sku', $sku)
                    ->first();
                if (!$variant) {
                    $rowErrors[] = "Row $excelRow: Product code (SKU) does not exist in the system: '$sku'.";
                }
            } else {
                $rowErrors[] = "Row $excelRow: Missing product code (SKU).";
            }

            // Ghi nhận lỗi nếu có
            if (!empty($rowErrors)) {
                $errors[$excelRow] = $rowErrors;
            }
        }

        return $errors;
    }

    /**
     * Validate rows without checking SKU
     *
     * @param array $rows Mảng các dòng dữ liệu từ file Excel
     * @param ImportFile $importFile Đối tượng ImportFile
     * @return array Mảng lỗi nếu có
     */
    public function validateRowsWithoutSkuCheck(array $rows, ImportFile $importFile): array
    {
        $errors = [];
        $validPositions = ['Front', 'Back', 'Left sleeve', 'Right sleeve', 'Hem'];
        $validImageExtensions = ['jpg', 'jpeg', 'png'];

        foreach ($rows as $index => $row) {
            $rowErrors = [];

            // Dòng thực tế trong Excel (cộng 2 vì index bắt đầu từ 0 và dòng tiêu đề là dòng 1)
            $excelRow = $index + 2;

            // Kiểm tra các trường bắt buộc
            if (empty($row['A'])) {
                $rowErrors[] = "Row $excelRow: Missing order code (External_ID).";
            }
            if (empty($row['E'])) {
                $rowErrors[] = "Row $excelRow: Missing recipient name (First Name).";
            }
            if (empty($row['H'])) {
                $rowErrors[] = "Row $excelRow: Missing delivery address (Address 1).";
            }
            if (empty($row['J'])) {
                $rowErrors[] = "Row $excelRow: Missing city (City).";
            }
            if (empty($row['K'])) {
                $rowErrors[] = "Row $excelRow: Missing district/county (County).";
            }
            if (empty($row['L'])) {
                $rowErrors[] = "Row $excelRow: Missing postal code (Postcode).";
            }
            if (empty($row['M'])) {
                $rowErrors[] = "Row $excelRow: Missing country (Country).";
            }

            // Kiểm tra số lượng
            if (empty($row['S']) || !is_numeric($row['S']) || (int)$row['S'] <= 0) {
                $rowErrors[] = "Row $excelRow: Product quantity is invalid or empty.";
            }

            // Kiểm tra vị trí in
            $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
            foreach ($positionCols as $col) {
                if (!empty($row[$col]) && !in_array($row[$col], $validPositions)) {
                    $rowErrors[] = "Row $excelRow: Invalid print position at column $col: '{$row[$col]}'. Valid values are: " . implode(', ', $validPositions);
                }
            }

            // Kiểm tra hình ảnh mockup và design
            $mockupCols = ['Y', 'AB', 'AE', 'AH', 'AK'];
            $designCols = ['Z', 'AC', 'AF', 'AI', 'AL'];

            foreach ($mockupCols as $col) {
                if (!empty($row[$col])) {
                    $url = $row[$col];
                    // Kiểm tra nếu là link Google Drive
                    if (str_contains($url, 'drive.google.com')) {
                        if (!str_contains($url, '/file/d/')) {
                            $rowErrors[] = "Row $excelRow: Google Drive link for mockup at column $col must be a sharing link.";
                        }
                    } else {
                        // Kiểm tra extension của URL thông thường
                        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                        if (!in_array($extension, $validImageExtensions)) {
                            $rowErrors[] = "Row $excelRow: Format image is not valid for mockup at column $col. Only JPG, JPEG, PNG is accepted.";
                        }
                    }
                }
            }

            foreach ($designCols as $col) {
                if (!empty($row[$col])) {
                    $url = $row[$col];
                    // Kiểm tra nếu là link Google Drive
                    if (str_contains($url, 'drive.google.com')) {
                        if (!str_contains($url, '/file/d/')) {
                            $rowErrors[] = "Row $excelRow: Google Drive link for design at column $col must be a sharing link.";
                        }
                    } else {
                        // Kiểm tra extension của URL thông thường
                        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                        if (!in_array($extension, $validImageExtensions)) {
                            $rowErrors[] = "Row $excelRow: Format image is not valid for design at column $col. Only JPG, JPEG, PNG is accepted.";
                        }
                    }
                }
            }

            // Ghi nhận lỗi nếu có
            if (!empty($rowErrors)) {
                $errors[$excelRow] = $rowErrors;
            }
        }

        return $errors;
    }
}
