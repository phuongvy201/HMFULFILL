<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Transaction;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance'
    ];

    protected $casts = [
        'balance' => 'decimal:2'
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add amount to wallet balance
     */
    public function deposit(float $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $this->increment('balance', $amount);
        return true;
    }

    /**
     * Subtract amount from wallet balance
     */
    public function withdraw(float $amount): bool
    {
        if ($amount <= 0 || $this->balance < $amount) {
            return false;
        }

        $this->decrement('balance', $amount);
        return true;
    }

    /**
     * Check if wallet has enough balance
     */
    public function hasEnoughBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Get total balance (current balance in wallet)
     */
    public function getTotalBalance(): float
    {
        return (float) $this->balance;
    }

    /**
     * Get available balance (total balance minus hold amount)
     */
    public function getAvailableBalance(): float
    {
        $holdAmount = $this->getHoldAmount();
        return max(0, $this->balance - $holdAmount);
    }

    /**
     * Get hold amount (pending transactions amount)
     */
    public function getHoldAmount(): float
    {
        $holdAmount = Transaction::where('user_id', $this->user_id)
            ->where('status', Transaction::STATUS_PENDING)
            ->where('type', Transaction::TYPE_DEDUCT)
            ->sum('amount');

        return (float) $holdAmount;
    }

    /**
     * Get credit amount (sum of approved topup transactions not yet reflected in balance if any)
     */
    public function getCreditAmount(): float
    {
        $creditAmount = Transaction::where('user_id', $this->user_id)
            ->where('status', Transaction::STATUS_APPROVED)
            ->where('type', Transaction::TYPE_TOPUP)
            ->sum('amount');

        return (float) $creditAmount;
    }

    /**
     * Get the transactions for the wallet.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
