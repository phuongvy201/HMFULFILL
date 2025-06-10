<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportFile extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'status',
        'error_logs',
        'user_id',
        'warehouse'
    ];

    protected $casts = [
        'error_logs' => 'array'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';

    // Định nghĩa quan hệ với ExcelOrder
    public function excelOrders()
    {
        return $this->hasMany(ExcelOrder::class, 'import_file_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Phương thức để thiết lập trạng thái
    public function setStatusAttribute($value)
    {
        if (!in_array($value, [self::STATUS_PENDING, self::STATUS_PROCESSED, self::STATUS_FAILED, self::STATUS_PENDING_CONFIRMATION])) {
            throw new \InvalidArgumentException("Invalid status value");
        }
        $this->attributes['status'] = $value;
    }
}
