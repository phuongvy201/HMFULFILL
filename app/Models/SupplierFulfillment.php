<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierFulfillment extends Model
{
    protected $fillable = [
        'external_id',
        'brand',
        'channel',
        'buyer_email',
        'shipping_address',
        'items',
        'comment',
        'shipping',
        'status'
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'items' => 'array'
    ];
}
