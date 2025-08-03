<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class DesignRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'design_task_id',
        'designer_id',
        'design_file',
        'notes',
        'revision_notes',
        'version',
        'status',
        'submitted_at',
        'approved_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    // Constants cho status
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_REVISION = 'revision';

    /**
     * Get the design task that owns the revision.
     */
    public function designTask(): BelongsTo
    {
        return $this->belongsTo(DesignTask::class);
    }

    /**
     * Get the designer that created the revision.
     */
    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    /**
     * Get design URLs from AWS S3 (for multiple files)
     */
    public function getDesignUrls(): array
    {
        if (!$this->design_file) {
            return [];
        }

        // Kiểm tra xem có phải JSON array không
        $designFiles = json_decode($this->design_file, true);

        if (is_array($designFiles)) {
            // Multiple files (JSON array)
            $urls = [];
            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION');

            foreach ($designFiles as $filePath) {
                if (filter_var($filePath, FILTER_VALIDATE_URL)) {
                    $urls[] = $filePath;
                } elseif ($bucket && $region) {
                    $encodedPath = urlencode($filePath);
                    $urls[] = "https://{$bucket}.s3.{$region}.amazonaws.com/{$encodedPath}";
                }
            }
            return $urls;
        } else {
            // Single file (legacy)
            $url = $this->getDesignUrl();
            return $url ? [$url] : [];
        }
    }

    /**
     * Get single design URL (for backward compatibility)
     */
    public function getDesignUrl(): ?string
    {
        if (!$this->design_file) {
            return null;
        }

        // Nếu file đã là URL đầy đủ
        if (filter_var($this->design_file, FILTER_VALIDATE_URL)) {
            return $this->design_file;
        }

        // Kiểm tra xem có phải JSON array không
        $designFiles = json_decode($this->design_file, true);
        if (is_array($designFiles) && !empty($designFiles)) {
            // Lấy file đầu tiên từ array
            $filePath = $designFiles[0];
        } else {
            // Single file (legacy)
            $filePath = $this->design_file;
        }

        // Tạo URL S3 với encoding đúng
        $bucket = env('AWS_BUCKET');
        $region = env('AWS_DEFAULT_REGION');

        if ($bucket && $region) {
            // Encode path để xử lý ký tự đặc biệt
            $encodedPath = urlencode($filePath);
            return "https://{$bucket}.s3.{$region}.amazonaws.com/{$encodedPath}";
        }

        return null;
    }

    /**
     * Check if design file is an image (for specific URL)
     */
    public function isDesignImage(?string $designUrl = null): bool
    {
        if ($designUrl) {
            // Check specific URL
            $extension = strtolower(pathinfo($designUrl, PATHINFO_EXTENSION));
            return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        }

        // Check first design file (for backward compatibility)
        $designUrls = $this->getDesignUrls();
        if (empty($designUrls)) {
            return false;
        }

        $firstUrl = $designUrls[0];
        $extension = strtolower(pathinfo($firstUrl, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Get file extension of design
     */
    public function getDesignFileExtension(): ?string
    {
        if (!$this->design_file) {
            return null;
        }
        return strtolower(pathinfo($this->design_file, PATHINFO_EXTENSION));
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        $statusNames = [
            self::STATUS_SUBMITTED => 'Đã gửi',
            self::STATUS_APPROVED => 'Đã phê duyệt',
            self::STATUS_REVISION => 'Cần chỉnh sửa'
        ];

        return $statusNames[$this->status] ?? $this->status;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        $badgeClasses = [
            self::STATUS_SUBMITTED => 'bg-blue-100 text-blue-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_REVISION => 'bg-red-100 text-red-800'
        ];

        return $badgeClasses[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}
