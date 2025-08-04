<?php

namespace App\Services;

use App\Models\ShippingPrice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShippingPriceCleanupService
{
    /**
     * Xóa shipping prices theo user_id
     */
    public static function deleteByUserId(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [
                'success' => false,
                'error' => "User with ID {$userId} not found"
            ];
        }

        $shippingPrices = ShippingPrice::where('user_id', $userId)->get();

        return self::deleteShippingPrices($shippingPrices, "user_id={$userId}");
    }

    /**
     * Xóa shipping prices theo variant_id
     */
    public static function deleteByVariantId(int $variantId): array
    {
        $shippingPrices = ShippingPrice::where('variant_id', $variantId)->get();

        return self::deleteShippingPrices($shippingPrices, "variant_id={$variantId}");
    }

    /**
     * Xóa shipping prices theo method
     */
    public static function deleteByMethod(string $method): array
    {
        $shippingPrices = ShippingPrice::where('method', $method)->get();

        return self::deleteShippingPrices($shippingPrices, "method={$method}");
    }

    /**
     * Xóa shipping prices cũ hơn X ngày
     */
    public static function deleteOlderThan(int $days): array
    {
        $date = now()->subDays($days);
        $shippingPrices = ShippingPrice::where('created_at', '<', $date)->get();

        return self::deleteShippingPrices($shippingPrices, "older_than={$days}_days");
    }

    /**
     * Xóa shipping prices với điều kiện tùy chỉnh
     */
    public static function deleteWithCriteria(array $criteria): array
    {
        $query = ShippingPrice::query();

        if (isset($criteria['user_id'])) {
            $query->where('user_id', $criteria['user_id']);
        }

        if (isset($criteria['variant_id'])) {
            $query->where('variant_id', $criteria['variant_id']);
        }

        if (isset($criteria['method'])) {
            $query->where('method', $criteria['method']);
        }

        if (isset($criteria['currency'])) {
            $query->where('currency', $criteria['currency']);
        }

        if (isset($criteria['older_than_days'])) {
            $date = now()->subDays($criteria['older_than_days']);
            $query->where('created_at', '<', $date);
        }

        $shippingPrices = $query->get();

        return self::deleteShippingPrices($shippingPrices, "custom_criteria");
    }

    /**
     * Xóa shipping prices cụ thể
     */
    private static function deleteShippingPrices($shippingPrices, string $criteria): array
    {
        $result = [
            'success' => false,
            'criteria' => $criteria,
            'total_found' => $shippingPrices->count(),
            'deleted_count' => 0,
            'errors' => []
        ];

        if ($shippingPrices->isEmpty()) {
            $result['success'] = true;
            $result['message'] = 'No shipping prices found to delete';
            return $result;
        }

        DB::beginTransaction();

        try {
            foreach ($shippingPrices as $price) {
                // Log trước khi xóa
                Log::info("Deleting shipping price", [
                    'price_id' => $price->id,
                    'user_id' => $price->user_id,
                    'variant_id' => $price->variant_id,
                    'method' => $price->method,
                    'price' => $price->price,
                    'currency' => $price->currency,
                    'criteria' => $criteria
                ]);

                $price->delete();
                $result['deleted_count']++;
            }

            DB::commit();
            $result['success'] = true;
            $result['message'] = "Successfully deleted {$result['deleted_count']} shipping prices";

            Log::info("Shipping prices cleanup completed", [
                'criteria' => $criteria,
                'deleted_count' => $result['deleted_count'],
                'total_found' => $result['total_found']
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $result['error'] = $e->getMessage();

            Log::error("Error during shipping prices cleanup", [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Tìm shipping prices theo điều kiện
     */
    public static function findShippingPrices(array $criteria = []): array
    {
        $query = ShippingPrice::query();

        if (isset($criteria['user_id'])) {
            $query->where('user_id', $criteria['user_id']);
        }

        if (isset($criteria['variant_id'])) {
            $query->where('variant_id', $criteria['variant_id']);
        }

        if (isset($criteria['method'])) {
            $query->where('method', $criteria['method']);
        }

        if (isset($criteria['currency'])) {
            $query->where('currency', $criteria['currency']);
        }

        if (isset($criteria['older_than_days'])) {
            $date = now()->subDays($criteria['older_than_days']);
            $query->where('created_at', '<', $date);
        }

        $shippingPrices = $query->with(['user', 'variant.product'])->get();

        return [
            'shipping_prices' => $shippingPrices,
            'count' => $shippingPrices->count(),
            'criteria' => $criteria
        ];
    }

    /**
     * Backup shipping prices trước khi xóa
     */
    public static function backupShippingPrices($shippingPrices): array
    {
        $backup = [
            'shipping_prices' => $shippingPrices->toArray(),
            'backup_date' => now()->toISOString(),
            'total_count' => $shippingPrices->count()
        ];

        $filename = 'shipping_prices_backup_' . date('Y-m-d_H-i-s') . '.json';
        $path = storage_path('app/backups/shipping_prices/' . $filename);

        // Tạo thư mục nếu chưa có
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($backup, JSON_PRETTY_PRINT));

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $path,
            'count' => $shippingPrices->count()
        ];
    }

    /**
     * Thống kê shipping prices
     */
    public static function getStatistics(): array
    {
        $total = ShippingPrice::count();
        $byUser = ShippingPrice::whereNotNull('user_id')->count();
        $byMethod = ShippingPrice::select('method', DB::raw('count(*) as count'))
            ->groupBy('method')
            ->get();
        $byCurrency = ShippingPrice::select('currency', DB::raw('count(*) as count'))
            ->groupBy('currency')
            ->get();

        return [
            'total' => $total,
            'user_specific' => $byUser,
            'by_method' => $byMethod,
            'by_currency' => $byCurrency
        ];
    }
}
