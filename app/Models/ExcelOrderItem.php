<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExcelOrderItem extends Model
{
    protected $fillable = [
        'excel_order_id',
        'part_number',
        'title',
        'quantity',
        'description',
        'label_name',
        'label_type'
    ];

    protected $casts = [
        'mockup_urls' => 'array',
        'design_urls' => 'array'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ExcelOrder::class, 'excel_order_id');
    }

    public function mockups(): HasMany
    {
        return $this->hasMany(ExcelOrderMockup::class);
    }

    public function designs(): HasMany
    {
        return $this->hasMany(ExcelOrderDesign::class);
    }
}
