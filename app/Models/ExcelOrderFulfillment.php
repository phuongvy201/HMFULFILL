<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ExcelOrder;

class ExcelOrderFulfillment extends Model
{
    protected $table = 'excel_order_fulfillments';

    protected $fillable = [
        'excel_order_id',
        'total_quantity',
        'total_price',
        'status',
        'factory_response',
        'error_message',
    ];

    public function excelOrder()
    {
        return $this->belongsTo(ExcelOrder::class, 'excel_order_id');
    }
}
