<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Models\VariantAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VariantCleanupService
{
    /**
     * Xóa variant và tất cả dữ liệu liên quan
     */
    public static function deleteVariant(ProductVariant $variant): array
    {
        $result = [
            'success' => false,
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'deleted_data' => [
                'shipping_prices' => 0,
                'attributes' => 0
            ],
            'error' => null
        ];

        DB::beginTransaction();

        try {
            // Xóa shipping prices
            $shippingPricesCount = $variant->shippingPrices()->count();
            if ($shippingPricesCount > 0) {
                $variant->shippingPrices()->delete();
                $result['deleted_data']['shipping_prices'] = $shippingPricesCount;
            }

            // Xóa variant attributes
            $attributesCount = $variant->attributes()->count();
            if ($attributesCount > 0) {
                $variant->attributes()->delete();
                $result['deleted_data']['attributes'] = $attributesCount;
            }

            // Xóa variant
            $variant->delete();
            $result['success'] = true;

            Log::info("Variant deleted successfully", [
                'variant_id' => $variant->id,
                'sku' => $variant->sku,
                'product_id' => $variant->product_id,
                'deleted_data' => $result['deleted_data']
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $result['error'] = $e->getMessage();

            Log::error("Error deleting variant", [
                'variant_id' => $variant->id,
                'sku' => $variant->sku,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Xóa nhiều variants
     */
    public static function deleteVariants(array $variantIds): array
    {
        $results = [
            'total' => count($variantIds),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($variantIds as $variantId) {
            $variant = ProductVariant::find($variantId);

            if (!$variant) {
                $results['failed']++;
                $results['details'][] = [
                    'variant_id' => $variantId,
                    'success' => false,
                    'error' => 'Variant not found'
                ];
                continue;
            }

            $result = self::deleteVariant($variant);
            $results['details'][] = $result;

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Tìm variants có thể xóa an toàn
     */
    public static function findSafeToDeleteVariants(array $criteria = []): array
    {
        $query = ProductVariant::query();

        // Lọc theo product ID
        if (isset($criteria['product_id'])) {
            $query->where('product_id', $criteria['product_id']);
        }

        // Lọc theo SKU
        if (isset($criteria['sku'])) {
            $query->where('sku', $criteria['sku']);
        }

        // Lọc theo ngày tạo
        if (isset($criteria['older_than_days'])) {
            $date = now()->subDays($criteria['older_than_days']);
            $query->where('created_at', '<', $date);
        }

        // Chỉ lấy variants không có shipping prices (an toàn để xóa)
        if (isset($criteria['unused_only']) && $criteria['unused_only']) {
            $query->whereDoesntHave('shippingPrices');
        }

        $variants = $query->with(['product', 'attributes', 'shippingPrices'])->get();

        return [
            'variants' => $variants,
            'count' => $variants->count(),
            'criteria' => $criteria
        ];
    }

    /**
     * Kiểm tra variant có an toàn để xóa không
     */
    public static function isSafeToDelete(ProductVariant $variant): array
    {
        $checks = [
            'has_shipping_prices' => $variant->shippingPrices()->count() > 0,
            'has_attributes' => $variant->attributes()->count() > 0,
            'has_product' => $variant->product()->exists(),
            'is_used_in_orders' => false // Cần implement nếu có bảng orders
        ];

        $isSafe = !$checks['has_shipping_prices'] && !$checks['is_used_in_orders'];

        return [
            'is_safe' => $isSafe,
            'checks' => $checks,
            'warnings' => array_filter($checks, function ($value) {
                return $value;
            })
        ];
    }

    /**
     * Backup variant trước khi xóa
     */
    public static function backupVariant(ProductVariant $variant): array
    {
        $backup = [
            'variant' => $variant->toArray(),
            'attributes' => $variant->attributes()->get()->toArray(),
            'shipping_prices' => $variant->shippingPrices()->get()->toArray(),
            'backup_date' => now()->toISOString()
        ];

        // Lưu backup vào file hoặc database
        $filename = 'variant_backup_' . $variant->id . '_' . date('Y-m-d_H-i-s') . '.json';
        $path = storage_path('app/backups/variants/' . $filename);

        // Tạo thư mục nếu chưa có
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($backup, JSON_PRETTY_PRINT));

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $path
        ];
    }

    /**
     * Restore variant từ backup
     */
    public static function restoreVariant(string $backupFile): array
    {
        $path = storage_path('app/backups/variants/' . $backupFile);

        if (!file_exists($path)) {
            return [
                'success' => false,
                'error' => 'Backup file not found'
            ];
        }

        $backup = json_decode(file_get_contents($path), true);

        DB::beginTransaction();

        try {
            // Tạo lại variant
            $variant = ProductVariant::create($backup['variant']);

            // Tạo lại attributes
            foreach ($backup['attributes'] as $attribute) {
                unset($attribute['id']); // Reset ID
                $attribute['variant_id'] = $variant->id;
                VariantAttribute::create($attribute);
            }

            // Tạo lại shipping prices
            foreach ($backup['shipping_prices'] as $price) {
                unset($price['id']); // Reset ID
                $price['variant_id'] = $variant->id;
                ShippingPrice::create($price);
            }

            DB::commit();

            return [
                'success' => true,
                'variant_id' => $variant->id,
                'restored_data' => [
                    'attributes' => count($backup['attributes']),
                    'shipping_prices' => count($backup['shipping_prices'])
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy danh sách backup files
     */
    public static function getBackupFiles(): array
    {
        $backupDir = storage_path('app/backups/variants');

        if (!file_exists($backupDir)) {
            return [];
        }

        $files = glob($backupDir . '/*.json');
        $backups = [];

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true);
            $backups[] = [
                'filename' => basename($file),
                'variant_id' => $content['variant']['id'] ?? null,
                'sku' => $content['variant']['sku'] ?? null,
                'backup_date' => $content['backup_date'] ?? null,
                'size' => filesize($file)
            ];
        }

        return $backups;
    }
}
