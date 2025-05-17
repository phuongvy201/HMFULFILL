<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'approved_by'
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
    public function reject(string $note = null): bool
    {
        $data = ['status' => self::STATUS_REJECTED];
        if ($note) {
            $data['note'] = $note;
        }
        return $this->update($data);
    }
}
