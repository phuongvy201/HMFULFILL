<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // ID của giao dịch cần hoàn tiền (bạn có thể thay đổi ID này)
        $transactionId = 293;

        // ID của admin hoặc người thực hiện refund
        $refundedBy = 1;

        // Tìm giao dịch cần hoàn tiền
        $transaction = Transaction::find($transactionId);

        if (!$transaction) {
            echo "Transaction không tồn tại.\n";
            return;
        }

        if (!$transaction->canBeRefunded()) {
            echo "Giao dịch không đủ điều kiện để refund.\n";
            return;
        }

        DB::beginTransaction();
        try {
            // Thực hiện refund
            $refund = $transaction->refund($refundedBy, 'Refund transaction 293');

            echo "Refund thành công! Mã giao dịch hoàn tiền: {$refund->transaction_code}\n";
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Refund thất bại: " . $e->getMessage() . "\n";
        }
    }
    // public function run()
    // {
    //     $userId = 124; // ID người dùng
    //     $amount = 18.22; // Số tiền hoàn
    //     $note = "Refund Sweatshirt";

    //     $wallet = Wallet::where('user_id', $userId)->first();

    //     if (!$wallet) {
    //         $this->command->error("Wallet not found for user ID: {$userId}");
    //         return;
    //     }

    //     // Cộng tiền vào ví
    //     $wallet->deposit($amount);

    //     // Tạo giao dịch refund không liên quan đến giao dịch gốc
    //     Transaction::create([
    //         'user_id' => $userId,
    //         'transaction_code' => 'REFUND_' . strtoupper(uniqid()),
    //         'type' => Transaction::TYPE_REFUND,
    //         'method' => Transaction::METHOD_VND,
    //         'amount' => $amount,
    //         'status' => Transaction::STATUS_APPROVED,
    //         'note' => $note,
    //         'approved_at' => Carbon::now(),
    //         'approved_by' => 1 // ID admin tạo refund
    //     ]);

    //     $this->command->info("Manual refund created for user ID: {$userId}");
    // }
}
