<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RefundController extends Controller
{
    /**
     * Hiển thị trang refund với danh sách transactions có thể refund
     */
    public function index(Request $request)
    {
        try {
            $query = Transaction::with(['user'])
                ->where('status', Transaction::STATUS_APPROVED)
                ->whereNull('refunded_at')
                ->whereIn('type', [Transaction::TYPE_TOPUP, Transaction::TYPE_DEDUCT]);

            // Lọc theo loại transaction
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            // Lọc theo user
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            // Lọc theo khoảng thời gian
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Lọc theo transaction code
            if ($request->has('transaction_code') && $request->transaction_code) {
                $query->where('transaction_code', 'like', '%' . $request->transaction_code . '%');
            }

            $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

            // Lấy danh sách users cho dropdown
            $users = User::select('id', 'first_name', 'last_name', 'email')
                ->orderBy('first_name')
                ->get();

            return view('admin.topup.refundable-transaction', compact('transactions', 'users'));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách refundable transactions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tải danh sách: ' . $e->getMessage());
        }
    }

    /**
     * Refund một transaction cụ thể
     */
    public function refundTransaction(Request $request, $transactionId)
    {
        $request->validate([
            'refund_note' => 'nullable|string|max:255'
        ]);

        try {
            $transaction = Transaction::findOrFail($transactionId);

            if (!$transaction->canBeRefunded()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction không thể refund được'
                ], 400);
            }

            // Thực hiện refund
            $refundTransaction = $transaction->refund(
                Auth::id(),
                $request->refund_note ?? "Refund transaction {$transaction->transaction_code}"
            );

            Log::info('Transaction refunded successfully', [
                'original_transaction_id' => $transaction->id,
                'refund_transaction_id' => $refundTransaction->id,
                'refunded_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund thành công! Mã giao dịch hoàn tiền: ' . $refundTransaction->transaction_code,
                'refund_transaction_code' => $refundTransaction->transaction_code
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi refund transaction: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Refund thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refund theo số tiền tự nhập
     */
    public function refundCustomAmount(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'refund_note' => 'required|string|max:255',
            'method' => 'required|in:' . implode(',', [
                Transaction::METHOD_VND,
                Transaction::METHOD_PAYPAL,
                Transaction::METHOD_PINGPONG,
                Transaction::METHOD_LIANLIANPAY,
                Transaction::METHOD_WORLDFIRST,
                Transaction::METHOD_PAYPAL_NEW
            ])
        ]);

        try {
            DB::beginTransaction();

            // Tìm hoặc tạo wallet cho user
            $wallet = Wallet::firstOrCreate(['user_id' => $request->user_id], ['balance' => 0]);

            // Tạo transaction refund
            $refundTransaction = Transaction::create([
                'user_id' => $request->user_id,
                'transaction_code' => 'REFUND_' . strtoupper(uniqid()),
                'type' => Transaction::TYPE_REFUND,
                'method' => $request->method,
                'amount' => $request->amount,
                'status' => Transaction::STATUS_APPROVED,
                'note' => $request->refund_note,
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            // Cộng tiền vào wallet
            $wallet->deposit($request->amount);

            DB::commit();

            Log::info('Custom refund created successfully', [
                'refund_transaction_id' => $refundTransaction->id,
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund thành công! Mã giao dịch: ' . $refundTransaction->transaction_code,
                'refund_transaction_code' => $refundTransaction->transaction_code
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi tạo custom refund: ' . $e->getMessage(), [
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Refund thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thông tin user và wallet balance
     */
    public function getUserInfo($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $wallet = Wallet::where('user_id', $userId)->first();

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'balance' => $wallet ? $wallet->balance : 0,
                    'available_balance' => $wallet ? $wallet->getAvailableBalance() : 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy user'
            ], 404);
        }
    }

    /**
     * Lấy danh sách refund transactions
     */
    public function getRefundHistory(Request $request)
    {
        try {
            $query = Transaction::with(['user'])
                ->where('type', Transaction::TYPE_REFUND)
                ->where('status', Transaction::STATUS_APPROVED);

            // Lọc theo user
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            // Lọc theo khoảng thời gian
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $refunds = $query->orderBy('created_at', 'desc')->paginate(20);

            return view('admin.topup.refund-history', compact('refunds'));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy refund history: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tải lịch sử refund: ' . $e->getMessage());
        }
    }
}
