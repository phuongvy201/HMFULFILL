<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingPrice extends Model
{
    protected $fillable = ['variant_id', 'method', 'price'];

    // Định nghĩa các hằng số cho shipping methods
    const METHOD_TIKTOK_1ST = 'tiktok_1st';
    const METHOD_TIKTOK_NEXT = 'tiktok_next';
    const METHOD_SELLER_1ST = 'seller_1st';
    const METHOD_SELLER_NEXT = 'seller_next';

    // Danh sách các method hợp lệ
    public static $validMethods = [
        self::METHOD_TIKTOK_1ST,
        self::METHOD_TIKTOK_NEXT,
        self::METHOD_SELLER_1ST,
        self::METHOD_SELLER_NEXT
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
