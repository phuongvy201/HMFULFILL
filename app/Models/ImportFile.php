<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportFile extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'status',
        'error_logs'
    ];

    protected $casts = [
        'error_logs' => 'array'
    ];

    // Định nghĩa quan hệ với ExcelOrder
    public function excelOrders()
    {
        return $this->hasMany(ExcelOrder::class, 'import_file_id');
    }
}
