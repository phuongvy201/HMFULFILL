<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class DesignTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'designer_id',
        'title',
        'description',
        'sides_count',
        'price',
        'status',
        'mockup_file',
        'design_file',
        'revision_notes',
        'completed_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'completed_at' => 'datetime'
    ];

    // Constants cho status
    const STATUS_PENDING = 'pending';
    const STATUS_JOINED = 'joined';
    const STATUS_COMPLETED = 'completed';
    const STATUS_APPROVED = 'approved';
    const STATUS_REVISION = 'revision';
    const STATUS_CANCELLED = 'cancelled';

    // Constants cho giá theo số mặt
    const PRICE_PER_SIDE = 1.5;

    /**
     * Get the customer that owns the design task.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the designer assigned to the design task.
     */
    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    /**
     * Calculate price based on number of sides
     */
    public static function calculatePrice(int $sidesCount): float
    {
        return $sidesCount * self::PRICE_PER_SIDE;
    }

    /**
     * Check if task can be joined
     */
    public function canBeJoined(): bool
    {
        return $this->status === self::STATUS_PENDING && is_null($this->designer_id);
    }

    /**
     * Check if task can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_PENDING && is_null($this->designer_id);
    }

    /**
     * Check if task is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_APPROVED]);
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        $statusNames = [
            self::STATUS_PENDING => 'Chờ nhận',
            self::STATUS_JOINED => 'Đang thiết kế',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REVISION => 'Cần chỉnh sửa',
            self::STATUS_CANCELLED => 'Đã hủy'
        ];

        return $statusNames[$this->status] ?? $this->status;
    }

    /**
     * Get mockup URLs from AWS S3
     */
    public function getMockupUrls(): array
    {
        if (!$this->mockup_file) {
            return [];
        }

        // Kiểm tra xem có phải JSON array không
        $mockupFiles = json_decode($this->mockup_file, true);

        if (is_array($mockupFiles)) {
            // Multiple files (JSON array)
            $urls = [];
            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION');

            foreach ($mockupFiles as $filePath) {
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
            $url = $this->getMockupUrl();
            return $url ? [$url] : [];
        }
    }

    /**
     * Get single mockup URL (for backward compatibility)
     */
    public function getMockupUrl(): ?string
    {
        if (!$this->mockup_file) {
            return null;
        }

        // Kiểm tra xem có phải JSON array không
        $mockupFiles = json_decode($this->mockup_file, true);

        if (is_array($mockupFiles) && !empty($mockupFiles)) {
            // Multiple files (JSON array) - trả về file đầu tiên
            $filePath = $mockupFiles[0];
            if (filter_var($filePath, FILTER_VALIDATE_URL)) {
                return $filePath;
            }

            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION');

            if ($bucket && $region) {
                $encodedPath = urlencode($filePath);
                return "https://{$bucket}.s3.{$region}.amazonaws.com/{$encodedPath}";
            }
        } else {
            // Single file (legacy)
            if (filter_var($this->mockup_file, FILTER_VALIDATE_URL)) {
                return $this->mockup_file;
            }

            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION');

            if ($bucket && $region) {
                $encodedPath = urlencode($this->mockup_file);
                return "https://{$bucket}.s3.{$region}.amazonaws.com/{$encodedPath}";
            }
        }

        return null;
    }

    /**
     * Get design URL from AWS S3
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

        // Tạo URL S3 với encoding đúng
        $bucket = env('AWS_BUCKET');
        $region = env('AWS_DEFAULT_REGION');

        if ($bucket && $region) {
            // Encode path để xử lý ký tự đặc biệt
            $encodedPath = urlencode($this->design_file);
            return "https://{$bucket}.s3.{$region}.amazonaws.com/{$encodedPath}";
        }

        return null;
    }

    /**
     * Check if mockup file is an image
     */
    public function isMockupImage(?string $mockupUrl = null): bool
    {
        if ($mockupUrl) {
            // Check specific URL
            $extension = strtolower(pathinfo($mockupUrl, PATHINFO_EXTENSION));
            return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        }

        // Check first mockup file (for backward compatibility)
        $mockupUrls = $this->getMockupUrls();
        if (empty($mockupUrls)) {
            return false;
        }

        $firstUrl = $mockupUrls[0];
        $extension = strtolower(pathinfo($firstUrl, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Check if design file is an image
     */
    public function isDesignImage(): bool
    {
        if (!$this->design_file) {
            return false;
        }
        $extension = strtolower(pathinfo($this->design_file, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Get file extension of mockup
     */
    public function getMockupFileExtension(): ?string
    {
        if (!$this->mockup_file) {
            return null;
        }
        return strtolower(pathinfo($this->mockup_file, PATHINFO_EXTENSION));
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
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        $badgeClasses = [
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_JOINED => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_APPROVED => 'badge-primary',
            self::STATUS_REVISION => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-secondary'
        ];

        return $badgeClasses[$this->status] ?? 'badge-secondary';
    }

    /**
     * Get customer full name
     */
    public function getCustomerFullName(): string
    {
        if (!$this->customer) {
            return 'N/A';
        }
        return trim(($this->customer->first_name ?? '') . ' ' . ($this->customer->last_name ?? ''));
    }

    /**
     * Get designer full name
     */
    public function getDesignerFullName(): string
    {
        if (!$this->designer) {
            return 'Chưa có designer';
        }
        return trim(($this->designer->first_name ?? '') . ' ' . ($this->designer->last_name ?? ''));
    }

    /**
     * Get the design revisions for the task.
     */
    public function revisions()
    {
        return $this->hasMany(DesignRevision::class);
    }

    /**
     * Get the latest revision
     */
    public function latestRevision()
    {
        return $this->hasOne(DesignRevision::class)->latest();
    }

    /**
     * Get the current design file (from latest revision or original)
     */
    public function getCurrentDesignFile(): ?string
    {
        $latestRevision = $this->latestRevision;
        if ($latestRevision && $latestRevision->design_file) {
            return $latestRevision->design_file;
        }
        return $this->design_file;
    }

    /**
     * Get the current design URL
     */
    public function getCurrentDesignUrl(): ?string
    {
        $latestRevision = $this->latestRevision;
        if ($latestRevision && $latestRevision->design_file) {
            return $latestRevision->getDesignUrl();
        }
        return $this->getDesignUrl();
    }

    /**
     * Get current version number
     */
    public function getCurrentVersion(): int
    {
        $latestRevision = $this->revisions()->latest('version')->first();
        return $latestRevision ? $latestRevision->version + 1 : 1;
    }
}
