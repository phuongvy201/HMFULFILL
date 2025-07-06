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
     * Kiểm tra xem user có phải admin không
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Scope để lấy chỉ admin users
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }
}
