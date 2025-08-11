<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShippingPrice;
use App\Models\ShippingOverride;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUserShippingTier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping:update-user-tier 
                            {user_id : ID của user cần cập nhật}
                            {from_tier=Wood : Tier hiện tại (mặc định: Wood)}
                            {to_tier=Special : Tier mới (mặc định: Special)}
                            {--dry-run : Chỉ hiển thị thay đổi mà không thực hiện}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật giá shipping cho user từ tier này sang tier khác';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $fromTier = $this->argument('from_tier');
        $toTier = $this->argument('to_tier');
        $dryRun = $this->option('dry-run');

        // Kiểm tra user có tồn tại không
        $user = User::find($userId);
        if (!$user) {
            $this->error("User với ID {$userId} không tồn tại!");
            return 1;
        }

        $this->info("Đang cập nhật giá shipping cho user: {$user->name} (ID: {$userId})");
        $this->info("Từ tier: {$fromTier} → Sang tier: {$toTier}");
        $this->info("Chế độ: " . ($dryRun ? 'DRY RUN (chỉ xem)' : 'THỰC THI'));

        // Lấy tất cả shipping prices
        $shippingPrices = ShippingPrice::with(['overrides' => function ($query) use ($fromTier, $toTier) {
            $query->whereIn('tier_name', [$fromTier, $toTier]);
        }])->get();

        $this->info("\nTìm thấy " . $shippingPrices->count() . " shipping prices");

        $updatedCount = 0;
        $errors = [];

        foreach ($shippingPrices as $shippingPrice) {
            try {
                // Tìm giá của tier hiện tại (from_tier)
                $fromTierOverride = $shippingPrice->overrides->where('tier_name', $fromTier)->first();

                // Tìm giá của tier mới (to_tier)
                $toTierOverride = $shippingPrice->overrides->where('tier_name', $toTier)->first();

                if (!$toTierOverride) {
                    $this->warn("Không tìm thấy giá cho tier '{$toTier}' trong shipping price ID: {$shippingPrice->id}");
                    continue;
                }

                // Nếu from_tier là Wood, sử dụng giá cơ bản từ shipping_prices
                $fromPrice = $fromTier === 'Wood' ? $shippingPrice->price : ($fromTierOverride ? $fromTierOverride->override_price : $shippingPrice->price);
                $toPrice = $toTierOverride->override_price;

                $this->info("Shipping Price ID: {$shippingPrice->id}");
                $this->info("  Variant ID: {$shippingPrice->variant_id}");
                $this->info("  Method: {$shippingPrice->method}");
                $this->info("  {$fromTier} tier: {$fromPrice} {$shippingPrice->currency}");
                $this->info("  {$toTier} tier: {$toPrice} {$toTierOverride->currency}");

                // Kiểm tra xem user đã có override riêng chưa
                $existingUserOverride = ShippingOverride::findForUser($shippingPrice->id, $userId);

                if ($existingUserOverride) {
                    $this->info("  User đã có override riêng: {$existingUserOverride->override_price} {$existingUserOverride->currency}");

                    if (!$dryRun) {
                        // Cập nhật giá override hiện tại thành giá của tier mới
                        $existingUserOverride->update([
                            'override_price' => $toPrice,
                            'currency' => $toTierOverride->currency
                        ]);
                        $this->info("  ✓ Đã cập nhật override hiện tại");
                    }
                } else {
                    $this->info("  Tạo override mới cho user");

                    if (!$dryRun) {
                        // Tạo override mới cho user với giá của tier mới
                        ShippingOverride::createOrUpdateForUser(
                            $shippingPrice->id,
                            $userId,
                            $toPrice,
                            $toTierOverride->currency
                        );
                        $this->info("  ✓ Đã tạo override mới");
                    }
                }

                $updatedCount++;
                $this->info("");
            } catch (\Exception $e) {
                $errorMsg = "Lỗi khi xử lý shipping price ID {$shippingPrice->id}: " . $e->getMessage();
                $errors[] = $errorMsg;
                $this->error($errorMsg);
                Log::error('UpdateUserShippingTier error', [
                    'shipping_price_id' => $shippingPrice->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Hiển thị kết quả
        $this->info("\n" . str_repeat("=", 50));
        $this->info("KẾT QUẢ:");
        $this->info("Tổng shipping prices đã xử lý: {$updatedCount}");

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
