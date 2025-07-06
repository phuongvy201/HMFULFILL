<?php

namespace App\Models;

use App\Services\TwofifteenService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExcelOrder extends Model
{
    protected $fillable = [
        'external_id',
        'brand',
        'channel',
        'buyer_email',
        'first_name',
        'last_name',
        'company',
        'address1',
        'address2',
        'city',
        'county',
        'post_code',
        'country',
        'phone1',
        'phone2',
        'comment',
        'shipping_method',
        'status',
        'api_response',
        'import_file_id',
        'warehouse',
        'created_by',
        'tracking_number',
    ];
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_ON_HOLD = 'on hold';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING = 'pending';
    protected $casts = [
        'api_response' => 'array'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ExcelOrderItem::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function importFile(): BelongsTo
    {
        return $this->belongsTo(ImportFile::class, 'import_file_id');
    }

    public function fulfillment(): HasOne
    {
        return $this->hasOne(ExcelOrderFulfillment::class);
    }
    public function orderMapping(): HasOne
    {
        return $this->hasOne(OrderMapping::class, 'external_id', 'external_id');
    }

    public function markAsProcessed($apiResponse = null, $internalId = null, $factory = null)
    {
        // Cập nhật trạng thái order - không lưu api_response khi thành công
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'api_response' => null  // Không lưu gì khi thành công
        ]);

        // Tạo hoặc cập nhật mapping nếu có thông tin
        if ($internalId && $factory) {
            OrderMapping::createOrUpdate(
                $this->external_id,
                $internalId,
                $factory,
                $apiResponse  // Vẫn lưu full response vào mapping để tracking
            );
        }

        return true;
    }

    public function markAsFailed($errorMessage = null, $errorResponse = null)
    {
        // Nếu không có errorResponse được truyền vào, tạo một cái đơn giản
        if (!$errorResponse) {
            $errorResponse = [
                'success' => false,
                'error' => $errorMessage,
                'timestamp' => now()->toISOString()
            ];
        }

        return $this->update([
            'status' => self::STATUS_FAILED,
            'api_response' => $errorResponse  // Chỉ lưu error response khi lỗi
        ]);
    }

    /**
     * Cập nhật tracking number và status cho đơn hàng
     * 
     * @param string $trackingNumber Tracking number mới
     * @return bool
     */
    public function updateTrackingAndStatus($trackingNumber, $status)
    {
        return $this->update([
            'tracking_number' => $trackingNumber,
            'status' => $status
        ]);
    }

    /**
     * Tính tổng doanh thu của đơn hàng
     * 
     * @return float
     */
    public function getTotalRevenue(): float
    {
        return $this->items->sum(function ($item) {
            return $item->print_price * $item->quantity;
        });
    }

    /**
     * Tính tổng doanh thu của user trong khoảng thời gian
     * 
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $status
     * @return float
     */
    public static function calculateUserRevenue(int $userId, Carbon $startDate, Carbon $endDate): float
    {
        $query = self::where('created_by', $userId)
            ->whereBetween('created_at', [$startDate, $endDate]);


        return $query->with('items')
            ->get()
            ->sum(function ($order) {
                return $order->getTotalRevenue();
            });
    }
}
