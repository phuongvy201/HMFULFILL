<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ShippingOverride extends Model
{
    protected $fillable = [
        'shipping_price_id',
        'user_ids',
        'tier_name',
        'override_price',
        'currency'
    ];

    protected $casts = [
        'user_ids' => 'array',
        'override_price' => 'decimal:2'
    ];

    /**
     * Boot method để đảm bảo user_ids luôn là array
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Đảm bảo user_ids luôn là array
            if (is_string($model->user_ids)) {
                $model->user_ids = json_decode($model->user_ids, true) ?: [];
            }

            if (!is_array($model->user_ids)) {
                $model->user_ids = [];
            }
        });
    }

    /**
     * Relationship với ShippingPrice
     */
    public function shippingPrice(): BelongsTo
    {
        return $this->belongsTo(ShippingPrice::class);
    }

    /**
     * Lấy user đầu tiên từ user_ids (cho hiển thị trong view)
     */
    public function getFirstUserAttribute()
    {
        $userIds = $this->user_ids ?? [];
        $firstUserId = $userIds[0] ?? null;

        if ($firstUserId) {
            return User::find($firstUserId);
        }

        return null;
    }

    /**
     * Debug method để kiểm tra user_ids
     */
    public function debugUserIds(): array
    {
        return [
            'raw' => $this->getRawOriginal('user_ids'),
            'casted' => $this->user_ids,
            'type' => gettype($this->user_ids),
            'is_array' => is_array($this->user_ids),
            'count' => is_array($this->user_ids) ? count($this->user_ids) : 0
        ];
    }

    /**
     * Kiểm tra xem override này có áp dụng cho user cụ thể không
     */
    public function appliesToUser(int $userId): bool
    {
        return in_array($userId, $this->user_ids ?? []);
    }

    /**
     * Kiểm tra xem override này có áp dụng cho tier cụ thể không
     */
    public function appliesToTier(string $tierName): bool
    {
        return $this->tier_name === $tierName;
    }

    /**
     * Thêm user vào danh sách user_ids
     */
    public function addUser(int $userId): void
    {
        $userIds = $this->user_ids ?? [];
        if (!in_array($userId, $userIds)) {
            $userIds[] = $userId;
            $this->update(['user_ids' => $userIds]);
        }
    }

    /**
     * Xóa user khỏi danh sách user_ids
     */
    public function removeUser(int $userId): void
    {
        $userIds = $this->user_ids ?? [];
        $userIds = array_filter($userIds, fn($id) => $id !== $userId);
        $this->update(['user_ids' => array_values($userIds)]);
    }

    /**
     * Tìm override cho user cụ thể
     */
    public static function findForUser(int $shippingPriceId, int $userId): ?self
    {
        return self::where('shipping_price_id', $shippingPriceId)
            ->where(function ($query) use ($userId) {
                $query->whereJsonContains('user_ids', $userId)
                    ->orWhereJsonContains('user_ids', (string) $userId);
            })
            ->first();
    }

    /**
     * Tìm override cho tier cụ thể
     */
    public static function findForTier(int $shippingPriceId, string $tierName): ?self
    {
        return self::where('shipping_price_id', $shippingPriceId)
            ->where('tier_name', $tierName)
            ->first();
    }

    /**
     * Tạo hoặc cập nhật override cho user
     */
    public static function createOrUpdateForUser(
        int $shippingPriceId,
        int $userId,
        float $overridePrice,
        string $currency = 'USD'
    ): self {
        $existing = self::findForUser($shippingPriceId, $userId);

        if ($existing) {
            $existing->update([
                'override_price' => $overridePrice,
                'currency' => $currency
            ]);
            return $existing;
        }

        return self::create([
            'shipping_price_id' => $shippingPriceId,
            'user_ids' => [$userId],
            'override_price' => $overridePrice,
            'currency' => $currency
        ]);
    }

    /**
     * Tạo hoặc cập nhật override cho tier
     */
    public static function createOrUpdateForTier(
        int $shippingPriceId,
        string $tierName,
        float $overridePrice,
        string $currency = 'USD'
    ): self {
        return self::updateOrCreate(
            [
                'shipping_price_id' => $shippingPriceId,
                'tier_name' => $tierName
            ],
            [
                'override_price' => $overridePrice,
                'currency' => $currency
            ]
        );
    }

    /**
     * Xóa override cho user
     */
    public static function removeForUser(int $shippingPriceId, int $userId): bool
    {
        $override = self::findForUser($shippingPriceId, $userId);

        if ($override) {
            $override->removeUser($userId);

            // Nếu không còn user nào, xóa override
            if (empty($override->user_ids)) {
                return $override->delete();
            }

            return true;
        }

        return false;
    }

    /**
     * Xóa override cho tier
     */
    public static function removeForTier(int $shippingPriceId, string $tierName): bool
    {
        return self::where('shipping_price_id', $shippingPriceId)
            ->where('tier_name', $tierName)
            ->delete() > 0;
    }

    /**
     * Import shipping overrides cho nhiều user_id
     * 
     * @param array $data Mảng dữ liệu với format:
     * [
     *     [
     *         'shipping_price_id' => 1,
     *         'user_ids' => [1, 2, 3], // hoặc 'user_ids' => '1,2,3'
     *         'override_price' => 10.50,
     *         'currency' => 'USD'
     *     ],
     *     ...
     * ]
     * @param bool $updateExisting Có cập nhật override hiện có không
     * @return array Kết quả import
     */
    public static function importShippingOverrides(array $data, bool $updateExisting = true): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'details' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                try {
                    // Validate dữ liệu
                    if (!isset($row['shipping_price_id']) || !isset($row['user_ids']) || !isset($row['override_price'])) {
                        throw new \Exception("Thiếu thông tin bắt buộc: shipping_price_id, user_ids, override_price");
                    }

                    $shippingPriceId = (int) $row['shipping_price_id'];
                    $overridePrice = (float) $row['override_price'];
                    $currency = $row['currency'] ?? 'USD';

                    // Xử lý user_ids
                    $userIds = $row['user_ids'];
                    if (is_string($userIds)) {
                        $userIds = array_map('intval', array_filter(explode(',', $userIds)));
                    } elseif (is_array($userIds)) {
                        $userIds = array_map('intval', $userIds);
                    } else {
                        throw new \Exception("user_ids phải là string hoặc array");
                    }

                    if (empty($userIds)) {
                        throw new \Exception("user_ids không được rỗng");
                    }

                    // Kiểm tra shipping_price_id có tồn tại không
                    if (!ShippingPrice::find($shippingPriceId)) {
                        throw new \Exception("Shipping price ID {$shippingPriceId} không tồn tại");
                    }

                    // Xử lý từng user_id
                    foreach ($userIds as $userId) {
                        if ($updateExisting) {
                            // Cập nhật hoặc tạo mới
                            self::createOrUpdateForUser($shippingPriceId, $userId, $overridePrice, $currency);
                        } else {
                            // Chỉ tạo mới nếu chưa tồn tại
                            $existing = self::findForUser($shippingPriceId, $userId);
                            if (!$existing) {
                                self::createOrUpdateForUser($shippingPriceId, $userId, $overridePrice, $currency);
                            }
                        }
                    }

                    $results['success']++;
                    $results['details'][] = [
                        'row' => $index + 1,
                        'shipping_price_id' => $shippingPriceId,
                        'user_ids' => $userIds,
                        'override_price' => $overridePrice,
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ];

                    Log::error('Shipping override import error', [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Import shipping overrides từ file Excel/CSV
     * 
     * @param string $filePath Đường dẫn file
     * @param bool $updateExisting Có cập nhật override hiện có không
     * @return array Kết quả import
     */
    public static function importFromFile(string $filePath, bool $updateExisting = true): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File không tồn tại: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'csv':
                return self::importFromCsv($filePath, $updateExisting);
            case 'xlsx':
            case 'xls':
                return self::importFromExcel($filePath, $updateExisting);
            default:
                throw new \Exception("Định dạng file không được hỗ trợ. Chỉ hỗ trợ CSV và Excel.");
        }
    }

    /**
     * Import từ file CSV
     */
    private static function importFromCsv(string $filePath, bool $updateExisting): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \Exception("Không thể mở file CSV");
        }

        // Đọc header
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception("File CSV rỗng hoặc không có header");
        }

        // Đọc dữ liệu
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                $data[] = [
                    'shipping_price_id' => $row[0] ?? null,
                    'user_ids' => $row[1] ?? null,
                    'override_price' => $row[2] ?? null,
                    'currency' => $row[3] ?? 'USD'
                ];
            }
        }

        fclose($handle);
        return self::importShippingOverrides($data, $updateExisting);
    }

    /**
     * Import từ file Excel
     */
    private static function importFromExcel(string $filePath, bool $updateExisting): array
    {
        // Cần cài đặt package: composer require phpoffice/phpspreadsheet
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new \Exception("Cần cài đặt package phpoffice/phpspreadsheet để import Excel");
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];

        foreach ($worksheet->getRowIterator(2) as $row) { // Bỏ qua header
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }

            if (count($rowData) >= 3) {
                $data[] = [
                    'shipping_price_id' => $rowData[0] ?? null,
                    'user_ids' => $rowData[1] ?? null,
                    'override_price' => $rowData[2] ?? null,
                    'currency' => $rowData[3] ?? 'USD'
                ];
            }
        }

        return self::importShippingOverrides($data, $updateExisting);
    }

    /**
     * Import shipping overrides cho user từ dữ liệu array
     * 
     * @param array $data Mảng dữ liệu với format:
     * [
     *     [
     *         'variant_id' => 1,
     *         'shipping_method' => 'tiktok_1st',
     *         'user_ids' => [1, 2, 3],
     *         'override_price' => 10.50,
     *         'currency' => 'USD'
     *     ],
     *     ...
     * ]
     * @param bool $updateExisting Có cập nhật override hiện có không
     * @return array Kết quả import
     */
    public static function importUserOverrides(array $data, bool $updateExisting = true): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'details' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                try {
                    // Validate dữ liệu
                    if (
                        !isset($row['variant_id']) || !isset($row['shipping_method']) ||
                        !isset($row['user_ids']) || !isset($row['override_price'])
                    ) {
                        throw new \Exception("Thiếu thông tin bắt buộc: variant_id, shipping_method, user_ids, override_price");
                    }

                    $variantId = (int) $row['variant_id'];
                    $shippingMethod = $row['shipping_method'];
                    $overridePrice = (float) $row['override_price'];
                    $currency = $row['currency'] ?? 'USD';

                    // Xử lý user_ids
                    $userIds = $row['user_ids'];
                    if (is_string($userIds)) {
                        $userIds = array_map('intval', array_filter(explode(',', $userIds)));
                    } elseif (is_array($userIds)) {
                        $userIds = array_map('intval', $userIds);
                    } else {
                        throw new \Exception("user_ids phải là string hoặc array");
                    }

                    if (empty($userIds)) {
                        throw new \Exception("user_ids không được rỗng");
                    }

                    // Tìm shipping price
                    $shippingPrice = ShippingPrice::where('variant_id', $variantId)
                        ->where('method', $shippingMethod)
                        ->first();

                    if (!$shippingPrice) {
                        throw new \Exception("Không tìm thấy shipping price cho variant {$variantId} và method {$shippingMethod}");
                    }

                    // Xử lý từng user_id
                    foreach ($userIds as $userId) {
                        if ($updateExisting) {
                            // Cập nhật hoặc tạo mới
                            self::createOrUpdateForUser($shippingPrice->id, $userId, $overridePrice, $currency);
                        } else {
                            // Chỉ tạo mới nếu chưa tồn tại
                            $existing = self::findForUser($shippingPrice->id, $userId);
                            if (!$existing) {
                                self::createOrUpdateForUser($shippingPrice->id, $userId, $overridePrice, $currency);
                            }
                        }
                    }

                    $results['success']++;
                    $results['details'][] = [
                        'row' => $index + 1,
                        'variant_id' => $variantId,
                        'shipping_method' => $shippingMethod,
                        'user_ids' => $userIds,
                        'override_price' => $overridePrice,
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ];

                    Log::error('User shipping override import error', [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Lấy tất cả overrides cho một user cụ thể
     */
    public static function getOverridesForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where(function ($query) use ($userId) {
            $query->whereJsonContains('user_ids', $userId)
                ->orWhereJsonContains('user_ids', (string) $userId);
        })
            ->with(['shippingPrice.variant.product'])
            ->get();
    }

    /**
     * Lấy tất cả overrides cho một variant và shipping method
     */
    public static function getOverridesForVariantAndMethod(int $variantId, string $shippingMethod): \Illuminate\Database\Eloquent\Collection
    {
        return self::whereHas('shippingPrice', function ($query) use ($variantId, $shippingMethod) {
            $query->where('variant_id', $variantId)
                ->where('method', $shippingMethod);
        })->get();
    }
}
