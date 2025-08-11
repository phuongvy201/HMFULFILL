<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserTier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUserToSpecialTier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-to-special-tier 
                            {user_id : ID của user cần cập nhật}
                            {--dry-run : Chỉ hiển thị thay đổi mà không thực hiện}
                            {--update-existing-orders : Cập nhật cả orders hiện có}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật user lên Special tier và cập nhật tất cả giá shipping liên quan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $dryRun = $this->option('dry-run');
        $updateExistingOrders = $this->option('update-existing-orders');

        // Kiểm tra user có tồn tại không
        $user = User::find($userId);
        if (!$user) {
            $this->error("User với ID {$userId} không tồn tại!");
            return 1;
        }

        $this->info("=== CẬP NHẬT USER LÊN SPECIAL TIER ===");
        $this->info("User: {$user->name} (ID: {$userId})");
        $this->info("Email: {$user->email}");
        $this->info("Chế độ: " . ($dryRun ? 'DRY RUN (chỉ xem)' : 'THỰC THI'));

        // Kiểm tra tier hiện tại
        $currentTier = UserTier::getCurrentTier($userId);
        $currentTierName = $currentTier ? $currentTier->tier : 'Wood';

        $this->info("Tier hiện tại: {$currentTierName}");

        if ($currentTierName === 'Special') {
            $this->warn("User đã ở Special tier rồi!");
            return 0;
        }

        DB::beginTransaction();

        try {
            // Bước 1: Cập nhật tier của user
            $this->info("\n1. Cập nhật tier của user...");

            if (!$dryRun) {
                // Tạo hoặc cập nhật tier record
                UserTier::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'month' => now()->startOfMonth()
                    ],
                    [
                        'tier' => 'Special',
                        'order_count' => $currentTier ? $currentTier->order_count : 0,
                        'revenue' => $currentTier ? $currentTier->revenue : 0
                    ]
                );
            }

            $this->info("✓ Đã cập nhật tier thành Special");

            // Bước 2: Cập nhật giá shipping cho user
            $this->info("\n2. Cập nhật giá shipping...");

            if (!$dryRun) {
                $this->call('shipping:update-user-tier', [
                    'user_id' => $userId,
                    'from_tier' => $currentTierName,
                    'to_tier' => 'Special'
                ]);
            } else {
                $this->info("Chạy command: shipping:update-user-tier {$userId} {$currentTierName} Special --dry-run");
            }

            // Bước 3: Cập nhật orders hiện có (nếu được yêu cầu)
            if ($updateExistingOrders) {
                $this->info("\n3. Cập nhật orders hiện có...");

                if (!$dryRun) {
                    $this->call('excel-order:update-fulfillment-prices', [
                        'user_id' => $userId,
                        'from_tier' => $currentTierName,
                        'to_tier' => 'Special'
                    ]);
                } else {
                    $this->info("Chạy command: excel-order:update-fulfillment-prices {$userId} {$currentTierName} Special --dry-run");
                }
            } else {
                $this->info("\n3. Bỏ qua cập nhật orders hiện có (sử dụng --update-existing-orders để cập nhật)");
            }

            if (!$dryRun) {
                DB::commit();
                $this->info("\n✓ Hoàn thành cập nhật user {$userId} lên Special tier");
            } else {
                DB::rollBack();
                $this->warn("\nĐây là DRY RUN - không có thay đổi nào được thực hiện");
                $this->info("Để thực hiện thực sự, chạy lại command không có --dry-run");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Lỗi: " . $e->getMessage());
            Log::error('UpdateUserToSpecialTier error', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 1;
        }

        return 0;
    }
}
