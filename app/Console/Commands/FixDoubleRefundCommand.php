<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\ExcelOrder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixDoubleRefundCommand extends Command
{
    protected $signature = 'fix:double-refund {--dry-run : Chạy thử mà không thực sự sửa dữ liệu} {--user-id= : Chỉ fix cho user cụ thể}';
    protected $description = 'Kiểm tra và fix các trường hợp double refund khi cancel order và xóa file';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $userId = $this->option('user-id');

        if ($isDryRun) {
            $this->warn('🔍 CHẠY THỬ - Không có dữ liệu nào được thay đổi');
        }

        $this->info('🔍 Bắt đầu kiểm tra double refund...');

        try {
            // Tìm các orders đã bị cancel và có refund transaction
            $query = ExcelOrder::with(['items'])
                ->where('status', 'cancelled')
                ->whereNotNull('import_file_id'); // Chỉ check orders thuộc file

            if ($userId) {
                $query->where('created_by', $userId);
                $this->info("🎯 Chỉ kiểm tra user ID: {$userId}");
            }

            $cancelledOrders = $query->get();
            $this->info("📊 Tìm thấy {$cancelledOrders->count()} đơn hàng đã cancel thuộc file");

            if ($cancelledOrders->isEmpty()) {
                $this->info('✅ Không tìm thấy trường hợp nào cần kiểm tra');
                return 0;
            }

            $suspiciousUsers = [];
            $totalDoubleRefund = 0;

            foreach ($cancelledOrders as $order) {
                // Tìm transaction refund của order này
                $orderRefund = Transaction::where('type', Transaction::TYPE_REFUND)
                    ->where('note', 'like', "%{$order->external_id}%")
                    ->where('user_id', $order->created_by)
                    ->first();

                if (!$orderRefund) {
                    continue; // Không có refund transaction
                }

                // Tìm transaction refund của file chứa order này
                $fileRefund = Transaction::where('type', Transaction::TYPE_REFUND)
                    ->where('note', 'like', "%file on hold%")
                    ->where('user_id', $order->created_by)
                    ->where('created_at', '>', $orderRefund->created_at)
                    ->first();

                if ($fileRefund) {
                    // Tính giá trị order
                    $orderValue = $order->items->sum(function ($item) {
                        return $item->print_price * $item->quantity;
                    });

                    if (!isset($suspiciousUsers[$order->created_by])) {
                        $suspiciousUsers[$order->created_by] = [
                            'orders' => [],
                            'total_double_refund' => 0
                        ];
                    }

                    $suspiciousUsers[$order->created_by]['orders'][] = [
                        'order_id' => $order->id,
                        'external_id' => $order->external_id,
                        'order_value' => $orderValue,
                        'order_refund_id' => $orderRefund->id,
                        'file_refund_id' => $fileRefund->id,
                        'order_refund_date' => $orderRefund->created_at,
                        'file_refund_date' => $fileRefund->created_at
                    ];

                    $suspiciousUsers[$order->created_by]['total_double_refund'] += $orderValue;
                    $totalDoubleRefund += $orderValue;
                }
            }

            if (empty($suspiciousUsers)) {
                $this->info('✅ Không tìm thấy trường hợp double refund nào');
                return 0;
            }

            $this->warn("⚠️  Tìm thấy {$totalDoubleRefund} USD có thể bị double refund cho " . count($suspiciousUsers) . " user");

            // Hiển thị chi tiết
            foreach ($suspiciousUsers as $userId => $data) {
                $user = User::find($userId);
                $this->newLine();
                $this->info("👤 User: {$user->first_name} {$user->last_name} (ID: {$userId})");
                $this->warn("   💰 Tổng double refund: $" . number_format($data['total_double_refund'], 2));

                foreach ($data['orders'] as $orderData) {
                    $this->line("   📦 Order {$orderData['external_id']} - $" . number_format($orderData['order_value'], 2));
                    $this->line("      ├─ Order refund: {$orderData['order_refund_date']} (ID: {$orderData['order_refund_id']})");
                    $this->line("      └─ File refund: {$orderData['file_refund_date']} (ID: {$orderData['file_refund_id']})");
                }
            }

            if (!$isDryRun) {
                $this->newLine();
                if ($this->confirm('Bạn có muốn fix các trường hợp double refund bằng cách trừ tiền từ wallet?')) {
                    $this->fixDoubleRefund($suspiciousUsers);
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Lỗi: " . $e->getMessage());
            Log::error('Error in FixDoubleRefundCommand', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    private function fixDoubleRefund(array $suspiciousUsers)
    {
        $this->info('🔧 Bắt đầu fix double refund...');

        DB::beginTransaction();

        try {
            foreach ($suspiciousUsers as $userId => $data) {
                $user = User::find($userId);
                $wallet = $user->wallet;

                if (!$wallet) {
                    $this->error("❌ Không tìm thấy wallet cho user {$userId}");
                    continue;
                }

                $totalAmount = $data['total_double_refund'];

                // Kiểm tra số dư
                if ($wallet->balance < $totalAmount) {
                    $this->error("❌ User {$userId} không đủ số dư để fix ({$wallet->balance} < {$totalAmount})");
                    continue;
                }

                // Trừ tiền từ wallet
                if ($wallet->withdraw($totalAmount)) {
                    // Tạo transaction ghi nhận việc fix
                    Transaction::create([
                        'user_id' => $userId,
                        'type' => Transaction::TYPE_DEDUCT,
                        'method' => Transaction::METHOD_VND,
                        'amount' => $totalAmount,
                        'status' => Transaction::STATUS_APPROVED,
                        'transaction_code' => 'FIX_DOUBLE_REFUND_' . $userId . '_' . time(),
                        'note' => "Fix double refund cho " . count($data['orders']) . " orders. Order IDs: " . implode(', ', array_column($data['orders'], 'external_id')),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $this->info("✅ Fixed user {$userId}: -$" . number_format($totalAmount, 2));
                } else {
                    $this->error("❌ Không thể trừ tiền từ wallet user {$userId}");
                }
            }

            DB::commit();
            $this->info('✅ Fix double refund hoàn thành');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Lỗi khi fix: " . $e->getMessage());
            throw $e;
        }
    }
}
