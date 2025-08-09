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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\OrderRowValidator;

class ExcelOrderImportService
{
    private OrderRowValidator $validator;

    public function __construct(OrderRowValidator $validator)
    {
        $this->validator = $validator;
    }

    private function getPositionTitle(string $position): string
    {
        // Chỉ xử lý UK format, US format sẽ giữ nguyên
        return match ($position) {
            'Front' => 'Printing Front Side',
            'Back' => 'Printing Back Side',
            'Left sleeve' => 'Printing Left Sleeve Side',
            'Right sleeve' => 'Printing Right Sleeve Side',
            'Hem' => 'Printing Hem Side',
            default => $position // Giữ nguyên cho US format hoặc các trường hợp khác
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
                    'created_by' => $importFile->user_id,
                    'warehouse' => $importFile->warehouse,
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

            // Lấy thông tin sản phẩm từ part_number
            $variant = ProductVariant::where('sku', $row['Q'])
                ->orWhere('twofifteen_sku', $row['Q'])
                ->orWhere('flashship_sku', $row['Q'])
                ->first();

            if ($variant) {
                // Lấy tier hiện tại của user
                $userTier = \App\Models\UserTier::getCurrentTier($importFile->user_id);
                $currentTier = $userTier ? $userTier->tier : 'Wood'; // Mặc định là Wood nếu không có tier

                // Xác định shipping method dựa trên shipping_method và position
                $shippingMethod = $this->determineShippingMethod($row['W'] ?? '', $order->id);

                // 1. Lấy giá shipping theo tier cụ thể
                $shippingPrice = ShippingPrice::where('variant_id', $variant->id)
                    ->where('tier_name', $currentTier)
                    ->where('method', $shippingMethod)
                    ->first();

                if ($shippingPrice) {
                    // Cập nhật giá và product_id vào orderItem
                    $orderItem->update([
                        'print_price' => $shippingPrice->price_usd,
                        'product_id' => $variant->product_id
                    ]);
                } else {
                    // Fallback: Lấy giá Wood tier nếu không tìm thấy giá cho tier hiện tại
                    $fallbackPrice = ShippingPrice::where('variant_id', $variant->id)
                        ->where('tier_name', 'Wood')
                        ->where('method', $shippingMethod)
                        ->first();

                    if ($fallbackPrice) {
                        $orderItem->update([
                            'print_price' => $fallbackPrice->price_usd,
                            'product_id' => $variant->product_id
                        ]);

                        Log::warning("Used fallback Wood tier pricing", [
                            'user_id' => $importFile->user_id,
                            'requested_tier' => $currentTier,
                            'variant_id' => $variant->id,
                            'method' => $shippingMethod,
                            'price' => $fallbackPrice->price_usd
                        ]);
                    } else {
                        Log::warning("No shipping prices found for variant", [
                            'variant_id' => $variant->id,
                            'tier' => $currentTier,
                            'method' => $shippingMethod
                        ]);
                    }
                }
            } else {
                Log::warning("Product variant not found for part_number: {$row['Q']}");
            }

            // Reset các mảng cho mỗi sản phẩm
            $positions = [];
            $mockupUrls = [];
            $designUrls = [];

            // Xử lý các vị trí in và URL tương ứng (đã dời sang phải 1 cột)
            $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
            $mockupCols = ['Y', 'AB', 'AE', 'AH', 'AK'];
            $designCols = ['Z', 'AC', 'AF', 'AI', 'AL'];

            for ($i = 0; $i < 5; $i++) {
                $positionCol = $positionCols[$i];
                $mockupCol = $mockupCols[$i];
                $designCol = $designCols[$i];

                if (!empty($row[$positionCol])) {
                    $positions[] = trim($row[$positionCol]);
                    $mockupUrls[] = !empty($row[$mockupCol]) ?
                        (str_contains($row[$mockupCol], 'drive.google.com') ?
                            GoogleDriveHelper::convertToDirectDownloadLink(trim($row[$mockupCol])) :
                            trim($row[$mockupCol])) : '';
                    $designUrls[] = !empty($row[$designCol]) ?
                        (str_contains($row[$designCol], 'drive.google.com') ?
                            GoogleDriveHelper::convertToDirectDownloadLink(trim($row[$designCol])) :
                            trim($row[$designCol])) : '';
                }
            }

            foreach ($positions as $index => $position) {
                // Xử lý title dựa trên warehouse
                $title = '';
                if ($importFile->warehouse === 'UK') {
                    // UK: Chuyển đổi position thành title
                    $title = $this->getPositionTitle($position);
                } else {
                    // US: Giữ nguyên position
                    $title = $position;
                }

                if (!empty($mockupUrls[$index])) {
                    ExcelOrderMockup::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $title,
                        'url' => $mockupUrls[$index]
                    ]);
                }

                if (!empty($designUrls[$index])) {
                    ExcelOrderDesign::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $title,
                        'url' => $designUrls[$index]
                    ]);
                }
            }
        }

        return true; // Thêm return true khi xử lý thành công
    }

    public function processCustomer(ImportFile $importFile, array $rows, string $warehouse)
    {
        $errors = $this->validator->validateRows($rows, $warehouse);
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

        // Lấy tier hiện tại của user
        $userTier = \App\Models\UserTier::getCurrentTier($importFile->user_id);
        $currentTier = $userTier ? $userTier->tier : 'Wood';
        Log::info('[IMPORT] Tier hiện tại của user', [
            'user_id' => $importFile->user_id,
            'tier' => $currentTier,
            'tier_data' => $userTier
        ]);

        // Logic tính giá sẽ áp dụng thứ tự ưu tiên:
        // 1. Giá theo tier của user (tier_name = $currentTier)
        // 2. Giá mặc định (tier_name = null)
        // 3. Giá Wood tier (tier_name = 'Wood') làm fallback cuối cùng

        $rowsByExternalId = [];
        foreach ($rows as $row) {
            // Loại bỏ khoảng trắng từ tất cả giá trị trong mảng $row
            $filteredRow = array_filter(array_map('trim', $row));
            if (empty($filteredRow)) {
                continue; // Bỏ qua nếu dòng rỗng hoặc chỉ chứa khoảng trắng
            }
            $externalId = trim($row['A'] ?? '');
            if (!isset($rowsByExternalId[$externalId])) {
                $rowsByExternalId[$externalId] = [];
            }
            $rowsByExternalId[$externalId][] = $row;
        }

        $totalAmount = 0;
        $orderPriceBreakdowns = [];
        $itemPrices = [];
        $itemPriceBreakdowns = [];

        foreach ($rowsByExternalId as $externalId => $orderRows) {
            $allItems = [];
            foreach ($orderRows as $rowIndex => $row) {
                $partNumber = trim($row['Q'] ?? '');
                $variant = ProductVariant::where('sku', $partNumber)
                    ->orWhere('twofifteen_sku', $partNumber)
                    ->orWhere('flashship_sku', $partNumber)
                    ->first();
                if ($variant) {
                    $shippingMethod = !empty($row['W']) ? strtolower(trim($row['W'] ?? '')) : '';
                    $quantity = (int)($row['S'] ?? 0);
                    $allItems[] = [
                        'variant' => $variant,
                        'quantity' => $quantity,
                        'row' => $row,
                        'row_index' => $rowIndex,
                        'first_item_price' => $variant->getFirstItemPrice($shippingMethod, $importFile->user_id),
                        'part_number' => $variant->twofifteen_sku ?? $partNumber,
                        'shipping_method' => $shippingMethod
                    ];
                }
            }

            $highestPriceItem = null;
            $highestPrice = 0;
            foreach ($allItems as $item) {
                if ($item['first_item_price'] > $highestPrice) {
                    $highestPrice = $item['first_item_price'];
                    $highestPriceItem = $item;
                }
            }

            $productsByPartNumber = [];
            foreach ($allItems as $item) {
                $partNumber = $item['part_number'];
                if (!isset($productsByPartNumber[$partNumber])) {
                    $productsByPartNumber[$partNumber] = [];
                }
                $productsByPartNumber[$partNumber][] = $item;
            }

            $orderTotalAmount = 0;
            $firstItemProcessed = false;
            foreach ($productsByPartNumber as $partNumber => $items) {
                foreach ($items as $index => $item) {
                    $variant = $item['variant'];
                    $quantity = $item['quantity'];
                    $rowIndex = $item['row_index'];
                    $shippingMethod = $item['shipping_method'];
                    $row = $item['row'];

                    $isFirstItem = (!$firstItemProcessed && $highestPriceItem && $highestPriceItem['row_index'] === $rowIndex);
                    if ($isFirstItem) {
                        $firstItemProcessed = true;
                    }

                    $itemTotal = 0;
                    $averagePrice = 0;
                    $priceBreakdown = [];
                    $logType = '';
                    $specialPriceAdjustment = 0;

                    // Kiểm tra position Special (chỉ áp dụng cho warehouse US)
                    if ($warehouse === 'US') {
                        $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
                        foreach ($positionCols as $col) {
                            if (!empty($row[$col]) && str_contains(trim($row[$col]), '(Special)')) {
                                $specialPriceAdjustment = 2 * $quantity; // +$2 cho mỗi quantity
                                break;
                            }
                        }
                    }

                    if ($isFirstItem && $quantity > 1) {
                        $priceInfo1 = $variant->getOrderPriceInfo($shippingMethod, 1, $importFile->user_id);
                        $priceInfo2 = $variant->getOrderPriceInfo($shippingMethod, 2, $importFile->user_id);
                        if ($priceInfo1['shipping_price_found'] && $priceInfo2['shipping_price_found']) {
                            $firstPrice = round($priceInfo1['print_price'], 2);
                            $secondPrice = round($priceInfo2['print_price'], 2);
                            $itemTotal = $firstPrice + ($secondPrice * ($quantity - 1)) + $specialPriceAdjustment;
                            $itemTotal = round($itemTotal, 2);
                            $averagePrice = round($itemTotal / $quantity, 2);
                            $logType = 'first_item_mix';
                            $priceBreakdown = [
                                'first_item_price' => $firstPrice,
                                'additional_item_price' => $secondPrice,
                                'quantity' => $quantity,
                                'special_price_adjustment' => $specialPriceAdjustment,
                                'tier_price' => $priceInfo1['tier_price'] ?? false,
                                'tier' => $priceInfo1['tier'] ?? null,
                                'breakdown' => "1x{$firstPrice} + " . ($quantity - 1) . "x{$secondPrice}" . ($specialPriceAdjustment > 0 ? " + {$quantity}x2 (Special)" : "")
                            ];
                            Log::info('[IMPORT] Tính giá (first_item_mix)', [
                                'external_id' => $externalId,
                                'part_number' => $partNumber,
                                'quantity' => $quantity,
                                'first_item_price' => $firstPrice,
                                'second_item_price' => $secondPrice,
                                'special_price_adjustment' => $specialPriceAdjustment,
                                'item_total' => $itemTotal,
                                'average_price' => $averagePrice,
                                'tier_price' => $priceInfo1['tier_price'] ?? false,
                                'tier' => $priceInfo1['tier'] ?? null,
                                'price_source' => $priceInfo1['tier_price'] ? 'tier_specific' : 'default_or_fallback',
                                'breakdown' => $priceBreakdown['breakdown']
                            ]);
                        }
                    } else {
                        $position = $isFirstItem ? 1 : 2;
                        $priceInfo = $variant->getOrderPriceInfo($shippingMethod, $position, $importFile->user_id);
                        if ($priceInfo['shipping_price_found']) {
                            $unitPrice = round($priceInfo['print_price'], 2);
                            $itemTotal = ($unitPrice * $quantity) + $specialPriceAdjustment;
                            $itemTotal = round($itemTotal, 2);
                            $averagePrice = round($itemTotal / $quantity, 2);
                            $logType = $isFirstItem ? 'first_item' : 'second_item';
                            $priceBreakdown = [
                                'unit_price' => $unitPrice,
                                'quantity' => $quantity,
                                'is_first_item' => $isFirstItem,
                                'special_price_adjustment' => $specialPriceAdjustment,
                                'tier_price' => $priceInfo['tier_price'] ?? false,
                                'tier' => $priceInfo['tier'] ?? null,
                                'breakdown' => $quantity . "x" . $unitPrice . ($specialPriceAdjustment > 0 ? " + {$quantity}x2 (Special)" : "")
                            ];
                        }
                    }

                    if ($itemTotal > 0) {
                        $orderTotalAmount += $itemTotal;
                        $itemPrices[$externalId][$rowIndex] = $averagePrice;
                        $itemPriceBreakdowns[$externalId][$rowIndex] = $priceBreakdown;
                    }
                }
            }
            $totalAmount += $orderTotalAmount;
            $orderPriceBreakdowns[$externalId] = $orderTotalAmount;
        }

        $totalAmount = round($totalAmount, 2);

        $wallet = Wallet::where('user_id', $importFile->user_id)->first();
        if (!$wallet || !$wallet->hasEnoughBalance($totalAmount)) {
            $importFile->update([
                'status' => 'failed',
                'error_logs' => ['Số dư ví không đủ'],
            ]);
            return false;
        }

        $transaction = Transaction::create([
            'user_id' => $importFile->user_id,
            'transaction_code' => 'ORDER-' . \Illuminate\Support\Str::ulid(),
            'type' => Transaction::TYPE_DEDUCT,
            'method' => Transaction::METHOD_VND,
            'amount' => $totalAmount,
            'status' => Transaction::STATUS_APPROVED,
            'note' => 'Trừ tiền cho đơn hàng nhập: ' . $importFile->id,
            'approved_at' => now(),
        ]);

        if (!$wallet->withdraw($totalAmount)) {
            $transaction->reject('Không thể trừ tiền từ ví');
            $importFile->update([
                'status' => 'failed',
                'error_logs' => ['Không thể xử lý thanh toán'],
            ]);
            return false;
        }

        $ordersByExternalId = [];
        $orderTotalPrices = [];
        $orderTotalQuantities = [];

        foreach ($rowsByExternalId as $externalId => $orderRows) {
            $order = null;
            $orderTotalQuantity = 0;
            $orderTotalPrice = 0;

            foreach ($orderRows as $index => $row) {
                if (!$order) {
                    $order = ExcelOrder::create([
                        'external_id' => $externalId,
                        'brand' => trim($row['B'] ?? ''),
                        'channel' => trim($row['C'] ?? ''),
                        'buyer_email' => trim($row['D'] ?? ''),
                        'first_name' => trim($row['E'] ?? ''),
                        'last_name' => trim($row['F'] ?? ''),
                        'company' => trim($row['G'] ?? ''),
                        'address1' => trim($row['H'] ?? ''),
                        'address2' => trim($row['I'] ?? ''),
                        'city' => trim($row['J'] ?? ''),
                        'county' => trim($row['K'] ?? ''),
                        'post_code' => trim($row['L'] ?? ''),
                        'country' => trim($row['M'] ?? ''),
                        'phone1' => trim($row['N'] ?? ''),
                        'phone2' => trim($row['O'] ?? ''),
                        'comment' => trim($row['P'] ?? ''),
                        'shipping_method' => trim($row['W'] ?? ''),
                        'status' => 'on hold',
                        'import_file_id' => $importFile->id,
                        'created_by' => Auth::user()->id,
                        'warehouse' => $warehouse
                    ]);
                    $ordersByExternalId[$externalId] = $order;
                }

                $partNumber = trim($row['Q'] ?? '');
                $variant = ProductVariant::where('sku', $partNumber)
                    ->orWhere('twofifteen_sku', $partNumber)
                    ->orWhere('flashship_sku', $partNumber)
                    ->first();

                $productId = null;
                $finalPartNumber = $partNumber;
                if ($variant) {
                    $productId = $variant->product_id;
                    $sku = $variant->getSkuByWarehouse($warehouse);
                    Log::info("Chọn SKU cho warehouse {$warehouse}", ['selected_sku' => $sku]);

                    if (!empty($sku)) {
                        $finalPartNumber = $sku;
                    } else {
                        $errors[] = "Không tìm thấy SKU hợp lệ cho variant (SKU: {$partNumber}, Warehouse: {$warehouse})";
                        Log::error('Không tìm thấy SKU hợp lệ cho variant', [
                            'sku' => $partNumber,
                            'warehouse' => $warehouse,
                            'variant_id' => $variant->id
                        ]);
                        continue;
                    }
                }

                $orderItem = ExcelOrderItem::create([
                    'excel_order_id' => $order->id,
                    'part_number' => $finalPartNumber,
                    'title' => trim($row['R'] ?? ''),
                    'quantity' => (int)($row['S'] ?? 0),
                    'description' => trim($row['T'] ?? ''),
                    'label_name' => trim($row['U'] ?? ''),
                    'label_type' => trim($row['V'] ?? ''),
                    'product_id' => $productId,
                    'print_price' => $itemPrices[$externalId][$index] ?? 0
                ]);

                $orderTotalQuantity += (int)($row['S'] ?? 0);

                $positions = [];
                $mockupUrls = [];
                $designUrls = [];

                $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
                $mockupCols = ['Y', 'AB', 'AE', 'AH', 'AK'];
                $designCols = ['Z', 'AC', 'AF', 'AI', 'AL'];

                for ($i = 0; $i < 5; $i++) {
                    $positionCol = $positionCols[$i];
                    $mockupCol = $mockupCols[$i];
                    $designCol = $designCols[$i];

                    if (!empty($row[$positionCol])) {
                        $positions[] = trim($row[$positionCol]);
                        $mockupUrls[] = !empty($row[$mockupCol]) ?
                            (str_contains($row[$mockupCol], 'drive.google.com') ?
                                GoogleDriveHelper::convertToDirectDownloadLink(trim($row[$mockupCol])) :
                                trim($row[$mockupCol])) : '';
                        $designUrls[] = !empty($row[$designCol]) ?
                            (str_contains($row[$designCol], 'drive.google.com') ?
                                GoogleDriveHelper::convertToDirectDownloadLink(trim($row[$designCol])) :
                                trim($row[$designCol])) : '';
                    }
                }

                foreach ($positions as $idx => $position) {
                    if (!empty($mockupUrls[$idx])) {
                        ExcelOrderMockup::create([
                            'excel_order_item_id' => $orderItem->id,
                            'title' => $this->getPositionTitle($position),
                            'url' => $mockupUrls[$idx]
                        ]);
                    }

                    if (!empty($designUrls[$idx])) {
                        ExcelOrderDesign::create([
                            'excel_order_item_id' => $orderItem->id,
                            'title' => $this->getPositionTitle($position),
                            'url' => $designUrls[$idx]
                        ]);
                    }
                }
            }

            $orderTotalQuantities[$externalId] = $orderTotalQuantity;
            $orderTotalPrices[$externalId] = $orderPriceBreakdowns[$externalId] ?? 0;
        }

        return true;
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
            // Bỏ qua dòng trống
            if (empty(array_filter($row))) continue;

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
                        // Kiểm tra mimetype thay vì extension
                        $isValidImageMime = function ($url) {
                            $headers = @get_headers($url, 1);
                            if (!$headers) return false;
                            $mime = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : '';
                            return in_array($mime, ['image/jpeg', 'image/png', 'image/jpg']);
                        };
                        if (!$isValidImageMime($url)) {
                            $rowErrors[] = "Row $excelRow: File at column $col is not a valid image (JPG, JPEG, PNG).";
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
                        // Kiểm tra mimetype thay vì extension
                        $isValidImageMime = function ($url) {
                            $headers = @get_headers($url, 1);
                            if (!$headers) return false;
                            $mime = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : '';
                            return in_array($mime, ['image/jpeg', 'image/png', 'image/jpg']);
                        };
                        if (!$isValidImageMime($url)) {
                            $rowErrors[] = "Row $excelRow: File at $col is not a valid image (JPG, JPEG, PNG).";
                        }
                    }
                }
            }

            // Kiểm tra phải có ít nhất một design URL
            $designCols = ['Z', 'AC', 'AF', 'AI', 'AL'];
            $hasDesign = false;
            foreach ($designCols as $col) {
                if (!empty($row[$col])) {
                    $hasDesign = true;
                    break;
                }
            }

            if (!$hasDesign) {
                $rowErrors[] = "Row $excelRow: At least one design URL is required.";
            }

            // Ghi nhận lỗi nếu có
            if (!empty($rowErrors)) {
                $errors[$excelRow] = $rowErrors;
            }
        }

        return $errors;
    }

    /**
     * Xác định shipping method dựa trên shipping_method và position trong order
     *
     * @param string $shippingMethod Shipping method từ Excel (cột W)
     * @param int $orderId ID của order để đếm số item
     * @return string Shipping method constant
     */
    private function determineShippingMethod(string $shippingMethod, int $orderId): string
    {
        // Đếm số item đã có trong order này
        $itemCount = \App\Models\ExcelOrderItem::where('excel_order_id', $orderId)->count();

        // Xác định đây là item đầu tiên hay tiếp theo
        $isFirstItem = ($itemCount === 0);

        // Xác định loại shipping (TikTok hay Seller)
        // Chỉ coi là TikTok nếu shipping_method chứa 'tiktok_label' và không rỗng
        $isTikTokShipping = !empty($shippingMethod) && stripos($shippingMethod, 'tiktok_label') !== false;

        Log::info('[IMPORT] Xác định shipping method trong determineShippingMethod', [
            'shipping_method_raw' => $shippingMethod,
            'is_tiktok_shipping' => $isTikTokShipping,
            'is_first_item' => $isFirstItem,
            'item_count' => $itemCount
        ]);

        // Trả về method phù hợp
        if ($isTikTokShipping) {
            return $isFirstItem ?
                \App\Models\ShippingPrice::METHOD_TIKTOK_1ST :
                \App\Models\ShippingPrice::METHOD_TIKTOK_NEXT;
        } else {
            return $isFirstItem ?
                \App\Models\ShippingPrice::METHOD_SELLER_1ST :
                \App\Models\ShippingPrice::METHOD_SELLER_NEXT;
        }
    }
}
