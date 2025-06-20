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

class ExcelOrderImportService
{
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
                // Lấy giá shipping dựa trên variant
                $shippingPrices = ShippingPrice::where('variant_id', $variant->id)->get();

                // Giả định bạn muốn lấy giá shipping đầu tiên (hoặc có thể thêm logic để chọn giá phù hợp)
                if ($shippingPrices->isNotEmpty()) {
                    $shippingPrice = $shippingPrices->first(); // Lấy giá shipping đầu tiên

                    // Cập nhật giá và product_id vào orderItem
                    $orderItem->update([
                        'print_price' => $shippingPrice->price_usd, // Hoặc bạn có thể lưu giá theo currency khác
                        'product_id' => $variant->product_id // Lưu product_id từ variant
                    ]);
                } else {
                    Log::warning("No shipping prices found for variant ID: {$variant->id}");
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

        $rowsByExternalId = [];
        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            $externalId = trim($row['A'] ?? '');
            if (!isset($rowsByExternalId[$externalId])) {
                $rowsByExternalId[$externalId] = [];
            }
            $rowsByExternalId[$externalId][] = $row;
        }

        $totalAmount = 0;
        $orderPriceBreakdowns = [];
        $itemPrices = []; // Lưu giá trung bình cho mỗi item
        $itemPriceBreakdowns = []; // Lưu chi tiết giá

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
                        'first_item_price' => $variant->getFirstItemPrice($shippingMethod),
                        'part_number' => $variant->twofifteen_sku ?? $partNumber, // Sử dụng twofifteen_sku nếu có
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

                    $isFirstItem = (!$firstItemProcessed && $highestPriceItem && $highestPriceItem['row_index'] === $rowIndex);
                    if ($isFirstItem) {
                        $firstItemProcessed = true;
                    }

                    $itemTotal = 0;
                    $averagePrice = 0;
                    $priceBreakdown = [];
                    $logType = '';

                    if ($isFirstItem && $quantity > 1) {
                        $priceInfo1 = $variant->getOrderPriceInfo($shippingMethod, 1);
                        $priceInfo2 = $variant->getOrderPriceInfo($shippingMethod, 2);
                        if ($priceInfo1['shipping_price_found'] && $priceInfo2['shipping_price_found']) {
                            $firstPrice = round($priceInfo1['print_price'], 2);
                            $secondPrice = round($priceInfo2['print_price'], 2);
                            $itemTotal = $firstPrice + ($secondPrice * ($quantity - 1));
                            $itemTotal = round($itemTotal, 2);
                            $averagePrice = round($itemTotal / $quantity, 2);
                            $logType = 'first_item_mix';
                            $priceBreakdown = [
                                'first_item_price' => $firstPrice,
                                'additional_item_price' => $secondPrice,
                                'quantity' => $quantity,
                                'breakdown' => "1x{$firstPrice} + " . ($quantity - 1) . "x{$secondPrice}"
                            ];
                            Log::info('[IMPORT] Tính giá (first_item_mix)', [
                                'external_id' => $externalId,
                                'part_number' => $partNumber,
                                'quantity' => $quantity,
                                'first_item_price' => $firstPrice,
                                'second_item_price' => $secondPrice,
                                'item_total' => $itemTotal,
                                'average_price' => $averagePrice,
                                'breakdown' => $priceBreakdown['breakdown']
                            ]);
                        }
                    } else {
                        $position = $isFirstItem ? 1 : 2;
                        $priceInfo = $variant->getOrderPriceInfo($shippingMethod, $position);
                        if ($priceInfo['shipping_price_found']) {
                            $unitPrice = round($priceInfo['print_price'], 2);
                            $itemTotal = $unitPrice * $quantity;
                            $itemTotal = round($itemTotal, 2);
                            $averagePrice = $unitPrice;
                            $logType = $isFirstItem ? 'first_item' : 'second_item';
                            $priceBreakdown = [
                                'unit_price' => $unitPrice,
                                'quantity' => $quantity,
                                'is_first_item' => $isFirstItem,
                                'breakdown' => $quantity . "x" . $unitPrice
                            ];
                            Log::info('[IMPORT] Tính giá (' . $logType . ')', [
                                'external_id' => $externalId,
                                'part_number' => $partNumber,
                                'quantity' => $quantity,
                                'unit_price' => $unitPrice,
                                'item_total' => $itemTotal,
                                'average_price' => $averagePrice,
                                'breakdown' => $priceBreakdown['breakdown']
                            ]);
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
            'transaction_code' => 'ORDER-' . time(),
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
                        'status' => 'pending',
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
                    'print_price' => $itemPrices[$externalId][$index] ?? 0 // Sử dụng giá trung bình
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

    public function validateRows(array $rows, ImportFile $importFile): array
    {
        $errors = [];
        $validPositionsUK = ['Front', 'Back', 'Left sleeve', 'Right sleeve', 'Hem'];
        $validPositionsUS = ['Front', 'Back', 'Right Sleeve', 'Left Sleeve', 'Special'];
        $validSizes = ['S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];
        $validImageMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $externalIds = [];

        // Hàm kiểm tra mimetype từ URL
        $isValidImageMime = function ($url) use ($validImageMimeTypes) {
            $headers = @get_headers($url, 1);
            if (!$headers) return false;
            $mime = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : '';
            return in_array(strtolower($mime), $validImageMimeTypes);
        };

        // Hàm kiểm tra hàng có dữ liệu hay không
        $hasRowData = function ($row) {
            $requiredColumns = ['A', 'E', 'H', 'J', 'K', 'L', 'M', 'Q', 'S', 'X', 'Y', 'Z'];
            foreach ($requiredColumns as $col) {
                if (!empty(trim($row[$col] ?? ''))) {
                    return true;
                }
            }
            return false;
        };

        // Hàm kiểm tra SKU và warehouse
        $validateSkuAndWarehouse = function ($sku, $warehouse, $excelRow) {
            $skuParts = explode('-', $sku);
            $skuSuffix = end($skuParts);

            if ($skuSuffix === 'UK' && $warehouse !== 'UK') {
                return "Row $excelRow: SKU '$sku' is for UK warehouse but selected warehouse is $warehouse";
            }
            if ($skuSuffix === 'US' && $warehouse !== 'US') {
                return "Row $excelRow: SKU '$sku' is for US warehouse but selected warehouse is $warehouse";
            }
            return null;
        };

        // Hàm kiểm tra position dựa trên warehouse
        $validatePosition = function ($position, $warehouse, $excelRow, $positionCol) use ($validPositionsUK, $validPositionsUS, $validSizes) {
            if ($warehouse === 'UK') {
                if (!in_array($position, $validPositionsUK)) {
                    return "Row $excelRow: Invalid print position at column $positionCol: '$position'. Valid values for UK warehouse are: " . implode(', ', $validPositionsUK);
                }
            } elseif ($warehouse === 'US') {
                // Kiểm tra format size-side cho US
                $parts = explode('-', $position);
                if (count($parts) !== 2) {
                    return "Row $excelRow: Invalid print position format at column $positionCol: '$position'. For US warehouse, position must be in format 'size-side' (e.g., S-Front, L-Left Sleeve).";
                }

                $size = $parts[0];
                $side = $parts[1];

                if (!in_array($size, $validSizes)) {
                    return "Row $excelRow: Invalid size '$size' in position at column $positionCol. Valid sizes are: " . implode(', ', $validSizes);
                }

                if (!in_array($side, $validPositionsUS)) {
                    return "Row $excelRow: Invalid side '$side' in position at column $positionCol. Valid sides for US warehouse are: " . implode(', ', $validPositionsUS);
                }
            }
            return null;
        };

        foreach ($rows as $index => $row) {
            // Bỏ qua hàng không có dữ liệu
            if (!$hasRowData($row)) {
                continue;
            }

            $rowErrors = [];
            $excelRow = $index + 2;

            // Kiểm tra các trường bắt buộc
            $externalId = trim($row['A'] ?? '');
            if (empty($externalId)) {
                $rowErrors[] = "Row $excelRow: Missing order code (External_ID).";
            } else {
                // Chỉ kiểm tra xem đã tồn tại trong database chưa
                if (ExcelOrder::where('external_id', $externalId)->exists()) {
                    $rowErrors[] = "Row $excelRow: External_ID '$externalId' already exists in the database.";
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

            // Kiểm tra số lượng
            if (empty($row['S']) || !is_numeric($row['S']) || (int)$row['S'] <= 0) {
                $rowErrors[] = "Row $excelRow: Product quantity is invalid or empty.";
            }

            // Kiểm tra shipping method với Google Drive link
            $comment = trim($row['P'] ?? '');
            $hasGoogleDriveLink = str_contains(strtolower($comment), 'drive.google.com');
            if ($hasGoogleDriveLink) {
                $shippingMethod = strtolower(trim($row['W'] ?? ''));
                if ($shippingMethod !== 'tiktok_label') {
                    $rowErrors[] = "Row $excelRow: Shipping method must be 'tiktok_label' when Google Drive link is present in comment. Current value: '{$row['W']}'.";
                }
            }

            // Kiểm tra SKU
            $sku = trim($row['Q'] ?? '');
            if (empty($sku)) {
                $rowErrors[] = "Row $excelRow: Missing product code (SKU).";
            } else {
                // Kiểm tra SKU và warehouse
                $skuError = $validateSkuAndWarehouse($sku, $importFile->warehouse, $excelRow);
                if ($skuError) {
                    $rowErrors[] = $skuError;
                }

                $variant = ProductVariant::where('sku', $sku)
                    ->orWhere('twofifteen_sku', $sku)
                    ->orWhere('flashship_sku', $sku)
                    ->first();
                if (!$variant) {
                    $rowErrors[] = "Row $excelRow: Product code (SKU) does not exist in the system: '$sku'.";
                }
            }

            // Kiểm tra vị trí in, mockup và design
            $positionCols = ['X', 'AA', 'AD', 'AG', 'AJ'];
            $mockupCols = ['Y', 'AB', 'AE', 'AH', 'AK'];
            $designCols = ['Z', 'AC', 'AF', 'AI', 'AL'];
            $hasPosition = false;
            $hasMockup = false;
            $hasDesign = false;

            for ($i = 0; $i < 5; $i++) {
                $positionCol = $positionCols[$i];
                $mockupCol = $mockupCols[$i];
                $designCol = $designCols[$i];

                // Kiểm tra vị trí in
                if (!empty($row[$positionCol])) {
                    $hasPosition = true;
                    $position = trim($row[$positionCol]);

                    // Kiểm tra position dựa trên warehouse
                    $positionError = $validatePosition($position, $importFile->warehouse, $excelRow, $positionCol);
                    if ($positionError) {
                        $rowErrors[] = $positionError;
                    }
                }

                // Kiểm tra mockup và design
                foreach (['mockup' => $mockupCol, 'design' => $designCol] as $type => $col) {
                    if (!empty($row[$col])) {
                        $url = trim($row[$col]);
                        if ($type === 'mockup') {
                            $hasMockup = true;
                        } else {
                            $hasDesign = true;
                        }

                        if (str_contains($url, 'drive.google.com')) {
                            if (!str_contains($url, '/file/d/')) {
                                $rowErrors[] = "Row $excelRow: Google Drive link for $type at column $col must be a sharing link.";
                            }
                        } else {
                            if (!$isValidImageMime($url)) {
                                $rowErrors[] = "Row $excelRow: File at column $col is not a valid image (JPG, JPEG, PNG).";
                            }
                        }
                    }
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
                            $rowErrors[] = "Row $excelRow: File at $col is not a valid image (JPG, JPEG, PNG).";
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
}
