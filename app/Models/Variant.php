<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model
{
    protected $fillable = [
        'value',
        'image_url',
        'sku',
        'price',
    ];

    /**
     * Quan hệ với bảng variant_attributes
     */
    public function variantAttributes(): HasMany
    {
        return $this->hasMany(VariantAttribute::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
