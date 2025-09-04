<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShippingOverride;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UpdateUserSpecificPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping:update-user-specific-price 
                            {user_id : ID của user cần cập nhật giá}
                            {--price= : Giá mới (nếu không có sẽ dùng giá cơ bản của variant)}
                            {--multiplier=1.0 : Hệ số nhân giá (mặc định: 1.0)}
                            {--currency=USD : Đơn vị tiền tệ (mặc định: USD)}
                            {--dry-run : Chỉ hiển thị thay đổi mà không thực hiện}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật giá shipping cho user không có tier nhưng có ID trong user_ids của ShippingOverride';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $newPrice = $this->option('price');
        $multiplier = (float) $this->option('multiplier');
        $currency = $this->option('currency');
        $dryRun = $this->option('dry-run');

        // Kiểm tra user có tồn tại không
        $user = User::find($userId);
        if (!$user) {
            $this->error("User với ID {$userId} không tồn tại!");
            return 1;
        }

        $this->info("=== CẬP NHẬT GIÁ SHIPPING CHO USER KHÔNG CÓ TIER ===");
        $this->info("User: {$user->name} (ID: {$userId})");
        $this->info("Email: {$user->email}");
        $this->info("Chế độ: " . ($dryRun ? 'DRY RUN (chỉ xem)' : 'THỰC THI'));

        if ($newPrice) {
            $this->info("Giá mới: {$newPrice} {$currency}");
        } else {
            $this->info("Hệ số giá: {$multiplier}x (dựa trên giá cơ bản của variant)");
        }

        // Tìm tất cả shipping overrides có chứa user_id này
        $userOverrides = ShippingOverride::where(function ($query) use ($userId) {
            $query->whereJsonContains('user_ids', $userId)
                ->orWhereJsonContains('user_ids', (string) $userId);
        })->with('shippingPrice.variant.product')->get();

        if ($userOverrides->isEmpty()) {
            $this->warn("Không tìm thấy shipping overrides nào cho user {$userId}!");
            $this->info("User này chưa có giá riêng trong bảng shipping_overrides.");
            return 0;
        }

        $this->info("\nTìm thấy " . $userOverrides->count() . " shipping overrides cho user");

        $updatedCount = 0;
        $errors = [];

        foreach ($userOverrides as $override) {
            try {
                $shippingPrice = $override->shippingPrice;
                if (!$shippingPrice) {
                    $this->warn("Không tìm thấy shipping price cho override ID: {$override->id}");
                    continue;
                }

                $oldPrice = $override->override_price;
                $oldCurrency = $override->currency;

                // Tính toán giá mới
                if ($newPrice) {
                    $finalPrice = (float) $newPrice;
                } else {
                    $basePrice = $shippingPrice->price;
                    $finalPrice = round($basePrice * $multiplier, 2);
                }

                $this->info("Shipping Override ID: {$override->id}");
                $this->info("  Variant ID: {$shippingPrice->variant_id}");
                $this->info("  Method: {$shippingPrice->method}");

                if (!$newPrice) {
                    $this->info("  Giá cơ bản: {$shippingPrice->price} {$shippingPrice->currency}");
                }

                $this->info("  Giá cũ: {$oldPrice} {$oldCurrency}");
                $this->info("  Giá mới: {$finalPrice} {$currency}");

                if (!$dryRun) {
                    // Cập nhật giá
                    $override->update([
                        'override_price' => $finalPrice,
                        'currency' => $currency
                    ]);
                    $this->info("  ✓ Đã cập nhật giá");
                } else {
                    $this->info("  [DRY RUN] Sẽ cập nhật giá");
                }

                $updatedCount++;
            } catch (\Exception $e) {
                $errorMsg = "Lỗi khi cập nhật override ID {$override->id}: " . $e->getMessage();
                $errors[] = $errorMsg;
                $this->error($errorMsg);
                Log::error('UpdateUserSpecificPrice error', [
                    'override_id' => $override->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Hiển thị kết quả
        $this->info("\n" . str_repeat("=", 50));
        $this->info("KẾT QUẢ:");
        $this->info("Tổng shipping overrides đã xử lý: {$updatedCount}");

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
            $this->info("✓ Hoàn thành cập nhật giá shipping cho user {$userId}");
        }

        return 0;
    }
}
