<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExcelOrder;
use App\Models\ShippingPrice;
use App\Models\ShippingOverride;
use App\Models\User;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

class UpdateOrderShippingPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:update-shipping-prices 
                                {user_id : ID của user cần cập nhật giá order}
                                {--dry-run : Chỉ hiển thị thay đổi mà không thực hiện}
                                {--order-id= : Chỉ cập nhật order cụ thể (nếu không có sẽ cập nhật tất cả)}
                                {--method= : Chỉ cập nhật shipping method cụ thể}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật giá shipping của tất cả order dựa trên ShippingOverride với user_ids và logic tính giá theo quantity (tìm theo part_number)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $dryRun = $this->option('dry-run');
        $specificOrderId = $this->option('order-id');
        $specificMethod = $this->option('method');

        // Kiểm tra user có tồn tại không
        $user = User::find($userId);
        if (!$user) {
            $this->error("User với ID {$userId} không tồn tại!");
            return 1;
        }

        $this->info("=== CẬP NHẬT GIÁ SHIPPING CHO ORDER ===");
        $this->info("User: {$user->name} (ID: {$userId})");
        $this->info("Email: {$user->email}");
        $this->info("Chế độ: " . ($dryRun ? 'DRY RUN (chỉ xem)' : 'THỰC THI'));

        if ($specificOrderId) {
            $this->info("Chỉ cập nhật order ID: {$specificOrderId}");
        }

        if ($specificMethod) {
            $this->info("Chỉ cập nhật shipping method: {$specificMethod}");
        }

        // Lấy tất cả order của user (dựa trên created_by)
        $ordersQuery = ExcelOrder::where('created_by', $userId);

        if ($specificOrderId) {
            $ordersQuery->where('id', $specificOrderId);
        }

        // Load items (không cần load product.variants nữa)
        $orders = $ordersQuery->with(['items'])->get();

        if ($orders->isEmpty()) {
            $this->warn("Không tìm thấy order nào cho user {$userId} (created_by: {$userId})!");
            return 0;
        }

        $this->info("\nTìm thấy " . $orders->count() . " order(s)");

        $updatedCount = 0;
        $errors = [];
        $totalSavings = 0;

        foreach ($orders as $order) {
            try {
                $this->info("\n--- Order ID: {$order->id} ---");
                $this->info("External ID: {$order->external_id}");
                $this->info("Status: {$order->status}");

                // Xử lý shipping method và map về method chuẩn
                $originalMethod = $order->shipping_method;
                $mappedMethod = $this->mapShippingMethod($originalMethod);

                $this->info("Shipping Method gốc: {$originalMethod}");
                $this->info("Shipping Method được map: {$mappedMethod}");

                // Kiểm tra shipping method cụ thể nếu được chỉ định
                if ($specificMethod && $mappedMethod !== $specificMethod) {
                    $this->info("Bỏ qua - không phải method cần cập nhật");
                    continue;
                }

                $orderUpdated = false;
                $orderSavings = 0;

                // Xử lý từng item trong order
                $itemIndex = 0; // Đếm thứ tự item trong order
                foreach ($order->items as $item) {
                    $partNumber = $item->part_number;
                    $quantity = $item->quantity;
                    $this->info("  Part Number: {$partNumber}");
                    $this->info("  Quantity: {$quantity}");

                    // Tìm product trực tiếp theo part_number (không phụ thuộc vào product_id)
                    $product = \App\Models\Product::whereHas('variants', function ($query) use ($partNumber) {
                        $query->where('sku', $partNumber)
                            ->orWhere('twofifteen_sku', $partNumber)
                            ->orWhere('flashship_sku', $partNumber);
                    })->first();

                    if (!$product) {
                        $this->warn("    Không tìm thấy product cho part_number: {$partNumber}");
                        $this->warn("    Item ID: {$item->id}");
                        $this->warn("    Title: {$item->title}");
                        $this->warn("    Bỏ qua item này");
                        $itemIndex++;
                        continue;
                    }

                    $this->info("    Tìm thấy product: {$product->name} (ID: {$product->id})");

                    // Xác định đây là item đầu tiên hay thứ 2
                    $isFirstItem = ($itemIndex === 0);
                    $this->info("    Item thứ: " . ($isFirstItem ? "1 (đầu tiên)" : ($itemIndex + 1) . " (tiếp theo)"));

                    // Tìm variant của product theo SKU cụ thể (KHÔNG phải variant đầu tiên!)
                    $variant = $this->findVariantBySku($product, $partNumber);
                    if (!$variant) {
                        $this->warn("    Không tìm thấy variant cho product {$product->id} với SKU: {$partNumber}");
                        $itemIndex++;
                        continue;
                    }

                    $this->info("    Tìm thấy variant: ID {$variant->id}, SKU: {$variant->sku}");

                    // Tính giá shipping theo quantity (với fallback)
                    $shippingPriceTotal = 0;
                    $shippingPriceBreakdown = [];
                    $averageShippingPrice = 0; // Khởi tạo giá trị mặc định

                    if ($quantity > 1) {
                        // Nếu quantity > 1, tính giá cho từng item
                        $this->info("    Tính giá cho {$quantity} items:");

                        try {
                            // Thử sử dụng getOrderPriceInfo trước
                            $priceInfo1 = $variant->getOrderPriceInfo($mappedMethod, 1, $userId);
                            $priceInfo2 = $variant->getOrderPriceInfo($mappedMethod, 2, $userId);

                            if (
                                isset($priceInfo1['shipping_price']) && isset($priceInfo2['shipping_price']) &&
                                $priceInfo1['shipping_price_found'] && $priceInfo2['shipping_price_found']
                            ) {

                                $firstItemPrice = round($priceInfo1['shipping_price'], 2);
                                $secondItemPrice = round($priceInfo2['shipping_price'], 2);

                                // Công thức: Giá item 1 + (Giá item 2 × (quantity - 1))
                                $shippingPriceTotal = $firstItemPrice + ($secondItemPrice * ($quantity - 1));
                                $averageShippingPrice = round($shippingPriceTotal / $quantity, 2);

                                $this->info("      Item 1 (position 1): {$firstItemPrice} USD (getOrderPriceInfo)");
                                $this->info("      Item 2+ (position 2): {$secondItemPrice} USD × " . ($quantity - 1));
                                $this->info("      Tổng giá shipping: {$shippingPriceTotal} USD");
                                $this->info("      Giá trung bình: {$averageShippingPrice} USD");

                                // Log thông tin tier nếu có
                                if (isset($priceInfo1['tier_price']) && $priceInfo1['tier_price']) {
                                    $this->info("      Sử dụng giá tier: {$priceInfo1['tier']}");
                                }
                            } else {
                                // Fallback về logic cũ nếu getOrderPriceInfo không hoạt động
                                $this->info("      Fallback về logic cũ (ShippingPrice + ShippingOverride)");

                                for ($i = 0; $i < $quantity; $i++) {
                                    $isFirstItemInQuantity = ($i === 0);
                                    $shippingMethod = $this->determineShippingMethod($mappedMethod, $isFirstItemInQuantity);

                                    $this->info("        Item " . ($i + 1) . " (quantity): {$shippingMethod}");

                                    // Tìm ShippingPrice cho variant và method
                                    $shippingPrice = ShippingPrice::where('variant_id', $variant->id)
                                        ->where('method', $shippingMethod)
                                        ->first();

                                    if (!$shippingPrice) {
                                        $this->warn("          Không tìm thấy shipping price cho method {$shippingMethod}");
                                        continue;
                                    }

                                    // Tìm ShippingOverride có user_ids chứa user hiện tại
                                    $userOverride = ShippingOverride::where('shipping_price_id', $shippingPrice->id)
                                        ->whereJsonContains('user_ids', $userId)
                                        ->first();

                                    if ($userOverride) {
                                        $itemPrice = $userOverride->override_price;
                                        $this->info("          Giá: {$itemPrice} {$userOverride->currency} (user override)");
                                    } else {
                                        $itemPrice = $shippingPrice->price;
                                        $this->info("          Giá: {$itemPrice} {$shippingPrice->currency} (giá cơ bản)");
                                    }

                                    $shippingPriceTotal += $itemPrice;
                                    $shippingPriceBreakdown[] = $itemPrice;
                                }

                                // Tính giá trung bình
                                $averageShippingPrice = round($shippingPriceTotal / $quantity, 2);
                                $this->info("      Tổng giá shipping: {$shippingPriceTotal} USD");
                                $this->info("      Giá trung bình: {$averageShippingPrice} USD");
                            }
                        } catch (\Exception $e) {
                            $this->warn("        Lỗi khi sử dụng getOrderPriceInfo: " . $e->getMessage());
                            $this->info("        Fallback về logic cũ");

                            // Fallback về logic cũ
                            for ($i = 0; $i < $quantity; $i++) {
                                $isFirstItemInQuantity = ($i === 0);
                                $shippingMethod = $this->determineShippingMethod($mappedMethod, $isFirstItemInQuantity);

                                $this->info("          Item " . ($i + 1) . " (quantity): {$shippingMethod}");

                                // Tìm ShippingPrice cho variant và method
                                $shippingPrice = ShippingPrice::where('variant_id', $variant->id)
                                    ->where('method', $shippingMethod)
                                    ->first();

                                if (!$shippingPrice) {
                                    $this->warn("            Không tìm thấy shipping price cho method {$shippingMethod}");
                                    continue;
                                }

                                // Tìm ShippingOverride có user_ids chứa user hiện tại
                                $userOverride = ShippingOverride::where('shipping_price_id', $shippingPrice->id)
                                    ->whereJsonContains('user_ids', $userId)
                                    ->first();

                                if ($userOverride) {
                                    $itemPrice = $userOverride->override_price;
                                    $this->info("            Giá: {$itemPrice} {$userOverride->currency} (user override)");
                                } else {
                                    $itemPrice = $shippingPrice->price;
                                    $this->info("            Giá: {$itemPrice} {$shippingPrice->currency} (giá cơ bản)");
                                }

                                $shippingPriceTotal += $itemPrice;
                                $shippingPriceBreakdown[] = $itemPrice;
                            }

                            // Tính giá trung bình
                            $averageShippingPrice = round($shippingPriceTotal / $quantity, 2);
                            $this->info("          Tổng giá shipping: {$shippingPriceTotal} USD");
                            $this->info("          Giá trung bình: {$averageShippingPrice} USD");
                        }
                    } else {
                        // Nếu quantity = 1, chỉ tính giá cho item đầu tiên
                        $position = $isFirstItem ? 1 : 2;

                        try {
                            // Thử sử dụng getOrderPriceInfo trước
                            $priceInfo = $variant->getOrderPriceInfo($mappedMethod, $position, $userId);

                            if (isset($priceInfo['shipping_price']) && $priceInfo['shipping_price_found']) {
                                $averageShippingPrice = round($priceInfo['shipping_price'], 2);
                                $this->info("    Shipping method được sử dụng: {$mappedMethod} (position {$position})");
                                $this->info("    Giá shipping: {$averageShippingPrice} USD (getOrderPriceInfo)");

                                // Log thông tin tier nếu có
                                if (isset($priceInfo['tier_price']) && $priceInfo['tier_price']) {
                                    $this->info("    Sử dụng giá tier: {$priceInfo['tier']}");
                                }
                            } else {
                                // Fallback về logic cũ
                                $this->info("    Fallback về logic cũ (ShippingPrice + ShippingOverride)");

                                $shippingMethod = $this->determineShippingMethod($mappedMethod, $isFirstItem);
                                $this->info("    Shipping method được sử dụng: {$shippingMethod}");

                                // Tìm ShippingPrice cho variant và method
                                $shippingPrice = ShippingPrice::where('variant_id', $variant->id)
                                    ->where('method', $shippingMethod)
                                    ->first();

                                if (!$shippingPrice) {
                                    $this->warn("    Không tìm thấy shipping price cho variant {$variant->id} và method {$shippingMethod}");
                                    $itemIndex++;
                                    continue;
                                }

                                // Tìm ShippingOverride có user_ids chứa user hiện tại
                                $userOverride = ShippingOverride::where('shipping_price_id', $shippingPrice->id)
                                    ->whereJsonContains('user_ids', $userId)
                                    ->first();

                                if ($userOverride) {
                                    $averageShippingPrice = $userOverride->override_price;
                                    $this->info("    Giá shipping: {$averageShippingPrice} USD (user override)");
                                } else {
                                    $averageShippingPrice = $shippingPrice->price;
                                    $this->info("    Giá shipping: {$averageShippingPrice} USD (giá cơ bản)");
                                }
                            }
                        } catch (\Exception $e) {
                            $this->warn("    Lỗi khi sử dụng getOrderPriceInfo: " . $e->getMessage());
                            $this->info("    Fallback về logic cũ");

                            // Fallback về logic cũ
                            $shippingMethod = $this->determineShippingMethod($mappedMethod, $isFirstItem);
                            $this->info("    Shipping method được sử dụng: {$shippingMethod}");

                            // Tìm ShippingPrice cho variant và method
                            $shippingPrice = ShippingPrice::where('variant_id', $variant->id)
                                ->where('method', $shippingMethod)
                                ->first();

                            if (!$shippingPrice) {
                                $this->warn("    Không tìm thấy shipping price cho variant {$variant->id} và method {$shippingMethod}");
                                $itemIndex++;
                                continue;
                            }

                            // Tìm ShippingOverride có user_ids chứa user hiện tại
                            $userOverride = ShippingOverride::where('shipping_price_id', $shippingPrice->id)
                                ->whereJsonContains('user_ids', $userId)
                                ->first();

                            if ($userOverride) {
                                $averageShippingPrice = $userOverride->override_price;
                                $this->info("    Giá shipping: {$averageShippingPrice} USD (user override)");
                            } else {
                                $averageShippingPrice = $shippingPrice->price;
                                $this->info("    Giá shipping: {$averageShippingPrice} USD (giá cơ bản)");
                            }
                        }
                    }

                    // Kiểm tra xem có giá shipping hợp lệ không
                    if ($averageShippingPrice <= 0) {
                        $this->warn("    Giá shipping không hợp lệ: {$averageShippingPrice} USD");
                        $itemIndex++;
                        continue;
                    }

                    $oldPrice = $item->print_price ?? 0;
                    $newPrice = $averageShippingPrice;

                    $this->info("    Giá cũ: {$oldPrice} USD");
                    $this->info("    Giá mới: {$newPrice} USD");

                    if (!$dryRun) {
                        // Cập nhật print_price của item (cột thực tế trong database)
                        $item->update([
                            'print_price' => $newPrice
                        ]);

                        $this->info("    ✓ Đã cập nhật print_price");
                    } else {
                        $this->info("    [DRY RUN] Sẽ cập nhật print_price");
                    }

                    $orderUpdated = true;
                    $orderSavings += ($oldPrice - $newPrice);
                    $itemIndex++;
                }

                if ($orderUpdated) {
                    $updatedCount++;
                    $totalSavings += $orderSavings;
                }
            } catch (\Exception $e) {
                $errorMsg = "Lỗi khi xử lý order ID {$order->id}: " . $e->getMessage();
                $errors[] = $errorMsg;
                $this->error($errorMsg);
                Log::error('UpdateOrderShippingPrices error', [
                    'order_id' => $order->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Hiển thị kết quả
        $this->info("\n" . str_repeat("=", 50));
        $this->info("KẾT QUẢ:");
        $this->info("Tổng order đã xử lý: " . $orders->count());
        $this->info("Số order được cập nhật: {$updatedCount}");
        $this->info("Tổng tiết kiệm: " . number_format($totalSavings, 2) . " USD");

        if (!empty($errors)) {
            $this->error("Số lỗi: " . count($errors));
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        if ($dryRun) {
            $this->warn("Đây là DRY RUN - không có thay đổi nào được thực hiện");
            $this->info("Để thực hiện thực sự, chạy lại command không có --dry-run");
        } else {
            $this->info("✓ Hoàn thành cập nhật giá shipping cho order của user {$userId}");
        }

        return 0;
    }

    /**
     * Tìm variant theo SKU cụ thể (giống ExcelOrderImportService)
     */
    private function findVariantBySku($product, string $partNumber): ?ProductVariant
    {
        // Tìm variant theo SKU chính xác
        $variant = ProductVariant::where('product_id', $product->id)
            ->where(function ($query) use ($partNumber) {
                $query->where('sku', $partNumber)
                    ->orWhere('twofifteen_sku', $partNumber)
                    ->orWhere('flashship_sku', $partNumber);
            })
            ->first();

        return $variant;
    }

    /**
     * Xác định shipping method dựa trên item thứ tự (giống ExcelOrderImportService)
     */
    private function determineShippingMethod(string $baseMethod, bool $isFirstItem): string
    {
        // Xác định loại shipping (TikTok hay Seller)
        $isTikTokShipping = strpos($baseMethod, 'tiktok') !== false;

        // Trả về method phù hợp
        if ($isTikTokShipping) {
            return $isFirstItem ? 'tiktok_1st' : 'tiktok_next';
        } else {
            return $isFirstItem ? 'seller_1st' : 'seller_next';
        }
    }

    /**
     * Map shipping method về method chuẩn
     */
    private function mapShippingMethod(?string $originalMethod): string
    {
        if (empty($originalMethod)) {
            return 'seller_1st'; // Mặc định cho null hoặc ""
        }

        $method = strtolower(trim($originalMethod));

        // Map các biến thể của tiktok_label
        if (in_array($method, ['tiktok_label', 'tiktok_label', 'tiktok_label'])) {
            return 'tiktok_1st'; // Mặc định về tiktok_1st
        }

        // Map các biến thể của seller
        if (in_array($method, ['', 'null', 'none'])) {
            return 'seller_1st'; // Mặc định cho empty
        }

        // Nếu method đã chuẩn, giữ nguyên
        if (in_array($method, ['tiktok_1st', 'tiktok_next', 'seller_1st', 'seller_next'])) {
            return $method;
        }

        // Mặc định về seller_1st cho các method không xác định
        return 'seller_1st';
    }
}
