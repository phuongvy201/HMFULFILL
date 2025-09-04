<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'role',
        'email_verified_at',
        'api_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }
    
    /**
     * Relationship với UserTier
     */
    public function userTiers()
    {
        return $this->hasMany(UserTier::class);
    }

    /**
     * Lấy tier hiện tại của user
     */
    public function getCurrentTier()
    {
        return $this->userTiers()
            ->where('month', now()->startOfMonth())
            ->first();
    }

    /**
     * Lấy tier của user cho tháng cụ thể
     */
    public function getTierForMonth($month)
    {
        return $this->userTiers()
            ->where('month', $month->startOfMonth())
            ->first();
    }

    /**
     * Relationship với ShippingPrice (giá riêng cho user)
     */
    public function shippingPrices()
    {
        return $this->hasMany(ShippingPrice::class);
    }

    /**
     * Kiểm tra xem user có phải admin không
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra xem user có phải designer không
     */
    public function isDesigner(): bool
    {
        return $this->role === 'design';
    }

    /**
     * Kiểm tra xem user có phải customer không
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Scope để lấy chỉ admin users
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope để lấy chỉ designer users
     */
    public function scopeDesigner($query)
    {
        return $query->where('role', 'design');
    }

    /**
     * Scope để lấy chỉ customer users
     */
    public function scopeCustomer($query)
    {
        return $query->where('role', 'customer');
    }

    /**
     * Lấy tên đầy đủ của user
     */
    public function getFullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Lấy tổng số dư của user
     */
    public function getTotalBalance(): float
    {
        return $this->wallet ? $this->wallet->getTotalBalance() : 0;
    }

    /**
     * Kiểm tra xem user có đủ số dư không
     */
    public function hasEnoughBalance(float $amount): bool
    {
        return $this->wallet ? $this->wallet->hasEnoughBalance($amount) : false;
    }
}
