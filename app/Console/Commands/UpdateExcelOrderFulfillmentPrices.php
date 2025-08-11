<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExcelOrder;
use App\Models\ExcelOrderFulfillment;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Models\ShippingOverride;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateExcelOrderFulfillmentPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel-order:update-fulfillment-prices 
                            {user_id : ID của user cần cập nhật}
                            {from_tier=Wood : Tier hiện tại (mặc định: Wood)}
                            {to_tier=Special : Tier mới (mặc định: Special)}
                            {--dry-run : Chỉ hiển thị thay đổi mà không thực hiện}
                            {--order-id= : Chỉ cập nhật order cụ thể (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật giá trong ExcelOrderItem cho user từ tier này sang tier khác';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $fromTier = $this->argument('from_tier');
        $toTier = $this->argument('to_tier');
        $dryRun = $this->option('dry-run');
        $specificOrderId = $this->option('order-id');

        // Kiểm tra user có tồn tại không
        $user = User::find($userId);
        if (!$user) {
            $this->error("User với ID {$userId} không tồn tại!");
            return 1;
        }

        $this->info("Đang cập nhật giá fulfillment cho user: {$user->name} (ID: {$userId})");
        $this->info("Từ tier: {$fromTier} → Sang tier: {$toTier}");
        $this->info("Chế độ: " . ($dryRun ? 'DRY RUN (chỉ xem)' : 'THỰC THI'));

        // Lấy các orders của user
        $query = ExcelOrder::where('created_by', $userId)
            ->with(['items.product']);

        if ($specificOrderId) {
            $query->where('id', $specificOrderId);
            $this->info("Chỉ cập nhật order ID: {$specificOrderId}");
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->warn("Không tìm thấy orders nào cho user {$userId}");
            return 0;
        }

        $this->info("\nTìm thấy " . $orders->count() . " orders");

        $updatedCount = 0;
        $errors = [];

        foreach ($orders as $order) {
            try {
                $this->info("\nXử lý Order ID: {$order->id} (External ID: {$order->external_id})");

                $totalPrice = 0;
                $itemCount = 0;

                // Tìm item có giá cao nhất để làm first item
                $highestPriceItem = null;
                $highestPrice = 0;
                foreach ($order->items as $item) {
                    $variant = ProductVariant::where('sku', $item->part_number)
                        ->orWhere('twofifteen_sku', $item->part_number)
                        ->orWhere('flashship_sku', $item->part_number)
                        ->first();

                    if ($variant) {
                        $shippingMethod = $order->shipping_method;
                        $firstItemPrice = $variant->getFirstItemPrice($shippingMethod, $userId);
                        if ($firstItemPrice > $highestPrice) {
                            $highestPrice = $firstItemPrice;
                            $highestPriceItem = $item;
                        }
                    }
                }

                $firstItemProcessed = false;
                foreach ($order->items as $item) {
                    // Tìm variant dựa trên part_number
                    $variant = ProductVariant::where('sku', $item->part_number)
                        ->orWhere('twofifteen_sku', $item->part_number)
                        ->orWhere('flashship_sku', $item->part_number)
                        ->first();

                    if (!$variant) {
                        $this->warn("  Item {$item->id} không tìm thấy variant cho part_number: {$item->part_number}");
                        continue;
                    }

                    $shippingMethod = $order->shipping_method;
                    $quantity = $item->quantity;
                    $isFirstItem = (!$firstItemProcessed && $highestPriceItem && $highestPriceItem->id === $item->id);

                    if ($isFirstItem) {
                        $firstItemProcessed = true;
                    }

                    $oldPrice = $item->print_price;
                    $newPrice = 0;
                    $itemTotal = 0;

                    if ($isFirstItem && $quantity > 1) {
                        // Logic cho first item với quantity > 1
                        $priceInfo1 = $variant->getOrderPriceInfo($shippingMethod, 1, $userId);
                        $priceInfo2 = $variant->getOrderPriceInfo($shippingMethod, 2, $userId);

                        if ($priceInfo1['shipping_price_found'] && $priceInfo2['shipping_price_found']) {
                            $firstPrice = round($priceInfo1['print_price'], 2);
                            $secondPrice = round($priceInfo2['print_price'], 2);
                            $itemTotal = $firstPrice + ($secondPrice * ($quantity - 1));
                            $itemTotal = round($itemTotal, 2);
                            $newPrice = round($itemTotal / $quantity, 2);

                            $this->info("  Item {$item->id} (First Item, Qty: {$quantity}): {$oldPrice} USD → {$newPrice} USD (1x{$firstPrice} + " . ($quantity - 1) . "x{$secondPrice})");
                        }
                    } else {
                        // Logic cho các item khác
                        $position = $isFirstItem ? 1 : 2;
                        $priceInfo = $variant->getOrderPriceInfo($shippingMethod, $position, $userId);

                        if ($priceInfo['shipping_price_found']) {
                            $unitPrice = round($priceInfo['print_price'], 2);
                            $itemTotal = $unitPrice * $quantity;
                            $itemTotal = round($itemTotal, 2);
                            $newPrice = round($itemTotal / $quantity, 2);

                            $this->info("  Item {$item->id} (" . ($isFirstItem ? 'First' : 'Additional') . " Item, Qty: {$quantity}): {$oldPrice} USD → {$newPrice} USD ({$quantity}x{$unitPrice})");
                        }
                    }

                    if ($newPrice > 0) {
                        if (!$dryRun) {
                            $item->update(['print_price' => $newPrice]);
                        }
                        $totalPrice += $itemTotal;
                        $itemCount++;
                    } else {
                        $this->warn("  Không tìm thấy giá cho variant {$variant->id}, method {$shippingMethod}");
                    }
                }

                if ($itemCount > 0) {
                    $this->info("  Tổng giá mới: {$totalPrice} USD");
                    $updatedCount++;
                }
            } catch (\Exception $e) {
                $errorMsg = "Lỗi khi xử lý order ID {$order->id}: " . $e->getMessage();
                $errors[] = $errorMsg;
                $this->error($errorMsg);
                Log::error('UpdateExcelOrderFulfillmentPrices error', [
                    'order_id' => $order->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Hiển thị kết quả
        $this->info("\n" . str_repeat("=", 50));
        $this->info("KẾT QUẢ:");
        $this->info("Tổng orders đã xử lý: {$updatedCount}");

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
            $this->info("✓ Hoàn thành cập nhật giá fulfillment cho user {$userId}");
        }

        return 0;
    }
}
