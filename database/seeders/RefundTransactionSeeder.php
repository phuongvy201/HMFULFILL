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
    // public function run(): void
    // {
    //     // ID của giao dịch cần hoàn tiền (bạn có thể thay đổi ID này)
    //     $transactionId = 1126;

    //     // ID của admin hoặc người thực hiện refund
    //     $refundedBy = 1;

    //     // Tìm giao dịch cần hoàn tiền
    //     $transaction = Transaction::find($transactionId);

    //     if (!$transaction) {
    //         echo "Transaction không tồn tại.\n";
    //         return;
    //     }

    //     if (!$transaction->canBeRefunded()) {
    //         echo "Giao dịch không đủ điều kiện để refund.\n";
    //         return;
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Thực hiện refund
    //         $refund = $transaction->refund($refundedBy, 'Refund transaction 1126');

    //         echo "Refund thành công! Mã giao dịch hoàn tiền: {$refund->transaction_code}\n";
    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         echo "Refund thất bại: " . $e->getMessage() . "\n";
    //     }
    // }
    public function run()
    {
        $userId = 129; // ID người dùng
        $amount = 11.11; // Số tiền trừ
        $note = "Refund external_id: 576762671741442197";

        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            $this->command->error("Wallet not found for user ID: {$userId}");
            return;
        }

        // Trừ tiền khỏi ví
        $wallet->deposit($amount);

        // Tạo giao dịch deduct
        Transaction::create([
            'user_id' => $userId,
            'transaction_code' => 'Refund_' . strtoupper(uniqid()),
            'type' => Transaction::TYPE_REFUND, // Bạn cần đảm bảo hằng số TYPE_DEDUCT tồn tại
            'method' => Transaction::METHOD_VND,
            'amount' => $amount,
            'status' => Transaction::STATUS_APPROVED,
            'note' => $note,
            'approved_at' => Carbon::now(),
            'approved_by' => 1, // ID admin thực hiện deduct
        ]);

        $this->command->info("Manual deduct created for user ID: {$userId}");
    }
    
}
