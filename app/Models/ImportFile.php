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
    const STATUS_ON_HOLD = 'on hold';
    const STATUS_CANCELLED = 'cancelled';

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
        if (!in_array($value, [self::STATUS_PENDING, self::STATUS_PROCESSED, self::STATUS_FAILED, self::STATUS_PENDING_CONFIRMATION, self::STATUS_ON_HOLD, self::STATUS_CANCELLED])) {
            throw new \InvalidArgumentException("Invalid status value");
        }
        $this->attributes['status'] = $value;
    }

    /**
     * Get file URL from AWS S3
     */
    public function getFileUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        // Nếu file_path đã là URL thì trả về luôn
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        // Nếu là path, tạo URL từ AWS S3
        return 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $this->file_path;
    }
}
