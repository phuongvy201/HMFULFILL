<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_code',
        'type',
        'method',
        'amount',
        'status',
        'note',
        'approved_at',
        'approved_by',
        'refunded_at',
        'refunded_by',
        'refund_transaction_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'type' => 'string',
        'method' => 'string',
        'status' => 'string'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'approved_at'
    ];

    // Định nghĩa các hằng số cho type, method và status
    public const TYPE_TOPUP = 'topup';
    public const TYPE_DEDUCT = 'deduct';
    public const TYPE_REFUND = 'refund';

    public const METHOD_VND = 'Bank VN';
    public const METHOD_PAYPAL = 'Payoneer';
    public const METHOD_PINGPONG = 'PingPong';
    public const METHOD_LIANLIANPAY = 'LianLianPay';
    public const METHOD_WORLDFIRST = 'Worldfirst';
    public const METHOD_PAYPAL_NEW = 'Paypal';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if transaction is topup
     */
    public function isTopup(): bool
    {
        return $this->type === self::TYPE_TOPUP;
    }

    /**
     * Check if transaction is deduct
     */
    public function isDeduct(): bool
    {
        return $this->type === self::TYPE_DEDUCT;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if transaction is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Approve the transaction
     */
    public function approve(): bool
    {
        return $this->update(['status' => self::STATUS_APPROVED]);
    }

    /**
     * Reject the transaction
     */
    public function reject(?string $note = null): bool
    {
        $data = ['status' => self::STATUS_REJECTED];
        if ($note) {
            $data['note'] = $note;
        }
        return $this->update($data);
    }

    /**
     * Check if transaction can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->status === self::STATUS_APPROVED &&
            !$this->isRefunded() &&
            in_array($this->type, [self::TYPE_TOPUP, self::TYPE_DEDUCT]);
    }

    /**
     * Check if transaction is refunded
     */
    public function isRefunded(): bool
    {
        return !is_null($this->refunded_at);
    }

    /**
     * Refund the transaction
     */
    public function refund(int $refundedBy, ?string $refundNote = null): ?Transaction
    {
        if (!$this->canBeRefunded()) {
            throw new \Exception('Transaction cannot be refunded');
        }

        // Kiểm tra số dư wallet nếu cần thiết
        $wallet = Wallet::where('user_id', $this->user_id)->first();
        if (!$wallet) {
            throw new \Exception('User wallet not found');
        }

        // Nếu refund topup, cần kiểm tra số dư có đủ không
        if ($this->type === self::TYPE_TOPUP && !$wallet->hasEnoughBalance($this->amount)) {
            throw new \Exception('Insufficient balance for refund');
        }

        DB::beginTransaction();
        try {
            // Tạo giao dịch refund mới
            $refundTransaction = self::create([
                'user_id' => $this->user_id,
                'transaction_code' => 'REFUND_' . strtoupper(uniqid()),
                'type' => self::TYPE_REFUND,
                'method' => $this->method,
                'amount' => $this->amount,
                'status' => self::STATUS_APPROVED,
                'note' => $refundNote ?? "Refund for transaction {$this->transaction_code}",
                'approved_at' => now(),
                'approved_by' => $refundedBy
            ]);

            // Cập nhật transaction gốc
            $this->update([
                'refunded_at' => now(),
                'refunded_by' => $refundedBy,
                'refund_transaction_id' => $refundTransaction->id
            ]);

            // Cập nhật wallet balance
            if ($this->type === self::TYPE_TOPUP) {
                // Refund topup: trừ tiền khỏi wallet
                $wallet->withdraw($this->amount);
            } elseif ($this->type === self::TYPE_DEDUCT) {
                // Refund deduct: cộng tiền vào wallet
                $wallet->deposit($this->amount);
            }

            DB::commit();
            return $refundTransaction;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get refund transaction if exists
     */
    public function refundTransaction()
    {
        return $this->belongsTo(Transaction::class, 'refund_transaction_id');
    }

    /**
     * Get original transaction if this is a refund
     */
    public function originalTransaction()
    {
        return $this->hasOne(Transaction::class, 'refund_transaction_id');
    }

    /**
     * Check if transaction is refund type
     */
    public function isRefund(): bool
    {
        return $this->type === self::TYPE_REFUND;
    }
}
