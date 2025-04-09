<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcelOrderDesign extends Model
{
    protected $fillable = [
        'excel_order_item_id',
        'title',
        'url'
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(ExcelOrderItem::class, 'excel_order_item_id');
    }
}
