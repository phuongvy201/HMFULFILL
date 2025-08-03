<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ExcelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TierStatisticsController extends Controller
{
    /**
     * Hiển thị dashboard thống kê TIER
     */
    public function dashboard(Request $request)
    {
        try {
            $period = $request->input('period', 'month'); // day, week, month, year
            $startDate = $this->getStartDate($period);

            // Thống kê tổng quan theo tier
            $tierStats = $this->getTierStatistics($startDate);
            $totalCustomers = User::where('role', 'customer')->count();
            $totalRevenue = $this->calculateTotalRevenue($startDate);

            // Top khách hàng trong tier cao nhất
            $topTierCustomers = $this->getTopTierCustomers($startDate, 10);

            // Thống kê doanh thu theo tier
            $tierRevenueStats = $this->getTierRevenueStatistics($startDate);

            // Thống kê đơn hàng theo tier
            $tierOrderStats = $this->getTierOrderStatistics($startDate);

            // Dữ liệu cho biểu đồ
            $chartData = $this->prepareChartData($tierStats, $tierRevenueStats, $tierOrderStats);

            return view('admin.statistics.tier-dashboard', compact(
                'tierStats',
                'totalCustomers',
                'totalRevenue',
                'topTierCustomers',
                'tierRevenueStats',
                'tierOrderStats',
                'chartData',
                'period'
            ));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thống kê TIER: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tải dashboard TIER: ' . $e->getMessage());
        }
    }

    /**
     * Kiểm tra database driver hiện tại
     */
    private function isSQLite()
    {
        $driver = config('database.default');
        return config("database.connections.{$driver}.driver") === 'sqlite';
    }

    /**
     * Lấy thống kê theo tier
     */
    private function getTierStatistics($startDate)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $isSQLite = $this->isSQLite();

        $query = User::where('role', 'customer')
            ->leftJoin('user_tiers', function ($join) use ($currentMonth) {
                $join->on('users.id', '=', 'user_tiers.user_id')
                    ->where('user_tiers.month', '=', $currentMonth);
            })
            ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
            ->select(
                DB::raw('COALESCE(user_tiers.tier, "Wood") as tier'),
                DB::raw('COUNT(DISTINCT users.id) as customer_count'),
                DB::raw('SUM(COALESCE(wallets.balance, 0)) as total_balance')
            )
            ->groupBy('tier');

        if ($isSQLite) {
            // SQLite sử dụng CASE WHEN thay vì FIELD()
            $query->orderByRaw("CASE 
                WHEN tier = 'Diamond' THEN 1
                WHEN tier = 'Gold' THEN 2
                WHEN tier = 'Silver' THEN 3
                WHEN tier = 'Special' THEN 4
                ELSE 5
            END");
        } else {
            $query->orderByRaw("FIELD(tier, 'Diamond', 'Gold', 'Silver', 'Wood', 'Special')");
        }

        return $query->get();
    }

    /**
     * Tính tổng doanh thu
     */
    private function calculateTotalRevenue($startDate)
    {
        $result = ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->where('excel_orders.created_at', '>=', $startDate)
            ->sum(DB::raw('excel_order_items.print_price * excel_order_items.quantity'));

        return (float) ($result ?? 0);
    }

    /**
     * Lấy top khách hàng trong tier cao nhất
     */
    private function getTopTierCustomers($startDate, $limit = 10)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $isSQLite = $this->isSQLite();

        $query = User::where('role', 'customer')
            ->leftJoin('user_tiers', function ($join) use ($currentMonth) {
                $join->on('users.id', '=', 'user_tiers.user_id')
                    ->where('user_tiers.month', '=', $currentMonth);
            })
            ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
            ->where(function ($query) {
                $query->where('user_tiers.tier', '!=', 'Wood')
                    ->orWhereNotNull('user_tiers.tier');
            })
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                DB::raw('COALESCE(user_tiers.tier, "Wood") as tier'),
                DB::raw('COALESCE(wallets.balance, 0) as balance'),
                DB::raw("(SELECT COUNT(*) FROM excel_orders WHERE excel_orders.created_by = users.id AND excel_orders.created_at >= '{$startDate}') as order_count"),
                DB::raw("(SELECT SUM(eoi.print_price * eoi.quantity) FROM excel_orders eo JOIN excel_order_items eoi ON eo.id = eoi.excel_order_id WHERE eo.created_by = users.id AND eo.created_at >= '{$startDate}') as total_revenue")
            );

        if ($isSQLite) {
            $query->orderByRaw("CASE 
                WHEN tier = 'Diamond' THEN 1
                WHEN tier = 'Gold' THEN 2
                WHEN tier = 'Silver' THEN 3
                ELSE 4
            END");
        } else {
            $query->orderByRaw("FIELD(tier, 'Diamond', 'Gold', 'Silver', 'Wood', 'Special')");
        }

        return $query->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy thống kê doanh thu theo tier
     */
    private function getTierRevenueStatistics($startDate)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $isSQLite = $this->isSQLite();

        $query = ExcelOrder::join('users', 'excel_orders.created_by', '=', 'users.id')
            ->join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->leftJoin('user_tiers', function ($join) use ($currentMonth) {
                $join->on('users.id', '=', 'user_tiers.user_id')
                    ->where('user_tiers.month', '=', $currentMonth);
            })
            ->where('excel_orders.created_at', '>=', $startDate)
            ->where('users.role', 'customer')
            ->select(
                DB::raw('COALESCE(user_tiers.tier, "Wood") as tier'),
                DB::raw('SUM(excel_order_items.print_price * excel_order_items.quantity) as total_revenue'),
                DB::raw('COUNT(DISTINCT excel_orders.id) as order_count'),
                DB::raw('COUNT(DISTINCT users.id) as customer_count')
            )
            ->groupBy('tier');

        if ($isSQLite) {
            $query->orderByRaw("CASE 
                WHEN tier = 'Diamond' THEN 1
                WHEN tier = 'Gold' THEN 2
                WHEN tier = 'Silver' THEN 3
                WHEN tier = 'Special' THEN 4
                ELSE 5
            END");
        } else {
            $query->orderByRaw("FIELD(tier, 'Diamond', 'Gold', 'Silver', 'Wood', 'Special')");
        }

        return $query->get();
    }

    /**
     * Lấy thống kê đơn hàng theo tier
     */
    private function getTierOrderStatistics($startDate)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $isSQLite = $this->isSQLite();

        $query = ExcelOrder::join('users', 'excel_orders.created_by', '=', 'users.id')
            ->leftJoin('user_tiers', function ($join) use ($currentMonth) {
                $join->on('users.id', '=', 'user_tiers.user_id')
                    ->where('user_tiers.month', '=', $currentMonth);
            })
            ->where('excel_orders.created_at', '>=', $startDate)
            ->where('users.role', 'customer')
            ->select(
                DB::raw('COALESCE(user_tiers.tier, "Wood") as tier'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COUNT(DISTINCT users.id) as customer_count'),
                DB::raw('AVG(
                    (SELECT SUM(eoi.print_price * eoi.quantity) 
                     FROM excel_order_items eoi 
                     WHERE eoi.excel_order_id = excel_orders.id)
                ) as avg_order_value')
            )
            ->groupBy('tier');

        if ($isSQLite) {
            $query->orderByRaw("CASE 
                WHEN tier = 'Diamond' THEN 1
                WHEN tier = 'Gold' THEN 2
                WHEN tier = 'Silver' THEN 3
                WHEN tier = 'Special' THEN 4
                    ELSE 5
            END");
        } else {
            $query->orderByRaw("FIELD(tier, 'Diamond', 'Gold', 'Silver', 'Wood', 'Special')");
        }

        return $query->get();
    }

    /**
     * Chuẩn bị dữ liệu cho biểu đồ
     */
    private function prepareChartData($tierStats, $tierRevenueStats, $tierOrderStats)
    {
        return [
            'tier_distribution' => [
                'labels' => $tierStats->pluck('tier')->map(function ($tier) {
                    return 'Tier ' . $tier;
                })->toArray(),
                'data' => $tierStats->pluck('customer_count')->toArray(),
                'colors' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6']
            ],
            'tier_revenue' => [
                'labels' => $tierRevenueStats->pluck('tier')->map(function ($tier) {
                    return 'Tier ' . $tier;
                })->toArray(),
                'revenue' => $tierRevenueStats->pluck('total_revenue')->toArray(),
                'orders' => $tierRevenueStats->pluck('order_count')->toArray()
            ],
            'tier_orders' => [
                'labels' => $tierOrderStats->pluck('tier')->map(function ($tier) {
                    return 'Tier ' . $tier;
                })->toArray(),
                'orders' => $tierOrderStats->pluck('order_count')->toArray(),
                'avg_value' => $tierOrderStats->pluck('avg_order_value')->toArray()
            ]
        ];
    }

    /**
     * Lấy ngày bắt đầu theo period
     */
    private function getStartDate($period)
    {
        switch ($period) {
            case 'day':
                return Carbon::now()->startOfDay();
            case 'week':
                return Carbon::now()->subDays(7)->startOfDay();
            case 'month':
                return Carbon::now()->subDays(30)->startOfDay();
            case 'year':
                return Carbon::now()->subDays(365)->startOfDay();
            default:
                return Carbon::now()->subDays(30)->startOfDay();
        }
    }
}
