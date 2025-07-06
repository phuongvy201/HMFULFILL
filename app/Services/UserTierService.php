<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTier;
use App\Models\ExcelOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserTierService
{
    /**
     * Tính toán và cập nhật tier cho tất cả user dựa trên đơn hàng tháng trước
     */
    public function calculateAndUpdateTiers(Carbon $month = null): array
    {
        $month = $month ?? Carbon::now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        Log::info('Bắt đầu tính toán tier cho tháng', [
            'month' => $startOfMonth->format('Y-m'),
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date' => $endOfMonth->format('Y-m-d')
        ]);

        // Lấy tất cả user có đơn hàng trong tháng
        $usersWithOrders = ExcelOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with('creator')
            ->distinct()
            ->pluck('created_by')
            ->filter();

        $results = [
            'total_users' => $usersWithOrders->count(),
            'updated_tiers' => [],
            'errors' => []
        ];

        foreach ($usersWithOrders as $userId) {
            try {
                $result = $this->calculateTierForUser($userId, $month);
                $results['updated_tiers'][] = $result;

                Log::info("Đã cập nhật tier cho user {$userId}", $result);
            } catch (\Exception $e) {
                $error = [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ];
                $results['errors'][] = $error;
                Log::error("Lỗi khi tính toán tier cho user {$userId}: " . $e->getMessage());
            }
        }

        Log::info('Hoàn tất tính toán tier', [
            'total_processed' => count($results['updated_tiers']),
            'total_errors' => count($results['errors'])
        ]);

        return $results;
    }

    /**
     * Tính toán tier cho một user cụ thể
     */
    public function calculateTierForUser(int $userId, Carbon $month): array
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Đếm số đơn hàng của user trong tháng
        $orderCount = ExcelOrder::where('created_by', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Tính tổng doanh thu từ tất cả các đơn hàng trong tháng
        $revenue = ExcelOrder::calculateUserRevenue($userId, $startOfMonth, $endOfMonth);

        // Xác định tier dựa trên số đơn hàng
        $tier = UserTier::determineTier($orderCount);

        // Lưu tier vào database (cho tháng hiện tại)
        $effectiveMonth = Carbon::now()->startOfMonth();
        $userTier = UserTier::createOrUpdateTier($userId, $tier, $orderCount, $effectiveMonth, $revenue);

        return [
            'user_id' => $userId,
            'order_count' => $orderCount,
            'revenue' => $revenue,
            'tier' => $tier,
            'effective_month' => $effectiveMonth->format('Y-m'),
            'previous_tier' => $this->getPreviousTier($userId, $month)
        ];
    }

    /**
     * Lấy tier của tháng trước để so sánh
     */
    private function getPreviousTier(int $userId, Carbon $month): ?string
    {
        $previousMonth = $month->copy()->subMonth();
        $previousTier = UserTier::getTierForMonth($userId, $previousMonth);

        return $previousTier ? $previousTier->tier : null;
    }

    /**
     * Lấy tier hiện tại của user
     */
    public function getCurrentTier(int $userId): ?UserTier
    {
        return UserTier::getCurrentTier($userId);
    }

    /**
     * Lấy thông tin tier chi tiết cho một user
     * 
     * @param int $userId
     * @return array
     */
    public function getUserTierInfo(int $userId): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $currentTier = UserTier::getCurrentTier($userId);

        // Sử dụng order_count từ UserTier record thay vì đếm lại từ ExcelOrder
        $thisMonthOrders = $currentTier ? $currentTier->order_count : 0;

        // Xác định tier hiện tại và tier tiếp theo
        $currentTierName = $currentTier ? $currentTier->tier : 'Wood';
        $nextTierThreshold = $this->getNextTierThreshold($currentTierName, $thisMonthOrders);

        return [
            'current_tier' => $currentTierName,
            'this_month_orders' => $thisMonthOrders,
            'next_tier_threshold' => $nextTierThreshold,
            'current_tier_data' => $currentTier
        ];
    }

    /**
     * Lấy thông tin threshold cho tier tiếp theo
     * 
     * @param string $currentTier
     * @param int $currentOrders
     * @return array|null
     */
    private function getNextTierThreshold(string $currentTier, int $currentOrders): ?array
    {
        $tierThresholds = [
            'Wood' => 0,
            'Silver' => 1500,
            'Gold' => 4500,
            'Diamond' => 9000 // Diamond là tier cao nhất
        ];

        $nextTiers = [
            'Wood' => 'Silver',
            'Silver' => 'Gold',
            'Gold' => 'Diamond'
        ];

        if ($currentTier === 'Diamond') {
            return null; // Đã đạt tier cao nhất
        }

        $nextTier = $nextTiers[$currentTier];
        $threshold = $tierThresholds[$nextTier];
        $ordersNeeded = max(0, $threshold - $currentOrders);

        return [
            'tier' => $nextTier,
            'threshold' => $threshold,
            'orders_needed' => $ordersNeeded,
            'current_orders' => $currentOrders
        ];
    }

    /**
     * Lấy thống kê tier của tất cả user
     */
    public function getTierStatistics(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();

        $tierStats = UserTier::where('effective_month', $currentMonth)
            ->selectRaw('tier, COUNT(*) as user_count, AVG(order_count) as avg_orders')
            ->groupBy('tier')
            ->get()
            ->keyBy('tier');

        $totalUsers = $tierStats->sum('user_count');

        // Đảm bảo có đủ tất cả các tier
        $allTiers = ['Diamond', 'Gold', 'Silver', 'Wood'];
        $tierDistribution = [];

        foreach ($allTiers as $tier) {
            $tierDistribution[$tier] = [
                'user_count' => $tierStats->get($tier)->user_count ?? 0,
                'percentage' => $totalUsers > 0 ? round((($tierStats->get($tier)->user_count ?? 0) / $totalUsers) * 100, 2) : 0,
                'avg_orders' => round($tierStats->get($tier)->avg_orders ?? 0, 2)
            ];
        }

        return [
            'total_users' => $totalUsers,
            'tier_distribution' => $tierDistribution,
            'effective_month' => $currentMonth->format('Y-m')
        ];
    }
}
