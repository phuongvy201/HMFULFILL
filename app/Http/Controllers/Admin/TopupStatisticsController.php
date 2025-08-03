<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TopupStatisticsController extends Controller
{
    /**
     * Hiển thị dashboard thống kê TOPUP
     */
    public function dashboard(Request $request)
    {
        try {
            $period = $request->input('period', 'month'); // day, week, month, year
            $startDate = $this->getStartDate($period);

            // Thống kê tổng quan
            $totalTopup = $this->calculateTotalTopup($startDate);
            $pendingTopup = $this->calculatePendingTopup();
            $approvedTopup = $this->calculateApprovedTopup($startDate);
            $rejectedTopup = $this->calculateRejectedTopup($startDate);

            // Thống kê theo thời gian
            $monthlyStats = $this->getMonthlyTopupStats();
            $dailyStats = $this->getDailyTopupStats(7);

            // Top khách hàng nạp nhiều nhất
            $topCustomers = $this->getTopCustomers($startDate, 10);

            // Danh sách giao dịch nạp mới nhất
            $recentTransactions = $this->getRecentTransactions(20);

            // Thống kê theo trạng thái
            $statusStats = $this->getStatusStatistics($startDate);

            // Dữ liệu cho biểu đồ
            $chartData = $this->prepareChartData($dailyStats, $statusStats, $monthlyStats);

            return view('admin.statistics.topup-dashboard', compact(
                'totalTopup',
                'pendingTopup',
                'approvedTopup',
                'rejectedTopup',
                'monthlyStats',
                'dailyStats',
                'topCustomers',
                'recentTransactions',
                'statusStats',
                'chartData',
                'period'
            ));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thống kê TOPUP: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tải dashboard TOPUP: ' . $e->getMessage());
        }
    }

    /**
     * Tính tổng tiền nạp
     */
    private function calculateTotalTopup($startDate)
    {
        return Transaction::where('created_at', '>=', $startDate)
            ->where('type', Transaction::TYPE_TOPUP)
            ->where('status', Transaction::STATUS_APPROVED)
            ->sum('amount');
    }

    /**
     * Tính tiền nạp đang chờ duyệt
     */
    private function calculatePendingTopup()
    {
        return Transaction::where('type', Transaction::TYPE_TOPUP)
            ->where('status', Transaction::STATUS_PENDING)
            ->sum('amount');
    }

    /**
     * Tính tiền nạp đã duyệt
     */
    private function calculateApprovedTopup($startDate)
    {
        return Transaction::where('created_at', '>=', $startDate)
            ->where('type', Transaction::TYPE_TOPUP)
            ->where('status', Transaction::STATUS_APPROVED)
            ->sum('amount');
    }

    /**
     * Tính tiền nạp bị từ chối
     */
    private function calculateRejectedTopup($startDate)
    {
        return Transaction::where('created_at', '>=', $startDate)
            ->where('type', Transaction::TYPE_TOPUP)
            ->where('status', Transaction::STATUS_REJECTED)
            ->sum('amount');
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
     * Lấy thống kê nạp tiền theo tháng
     */
    private function getMonthlyTopupStats()
    {
        $isSQLite = $this->isSQLite();

        $query = Transaction::where('type', Transaction::TYPE_TOPUP)
            ->where('status', Transaction::STATUS_APPROVED);

        if ($isSQLite) {
            $query->select(
                DB::raw('strftime("%Y-%m", created_at) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as count')
            );
        } else {
            $query->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as count')
            );
        }

        return $query->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
    }

    /**
     * Lấy thống kê nạp tiền theo ngày
     */
    private function getDailyTopupStats($days = 7)
    {
        $stats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $startOfDay = Carbon::parse($date)->startOfDay();
            $endOfDay = Carbon::parse($date)->endOfDay();

            $amount = Transaction::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('type', Transaction::TYPE_TOPUP)
                ->where('status', Transaction::STATUS_APPROVED)
                ->sum('amount');

            $count = Transaction::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('type', Transaction::TYPE_TOPUP)
                ->where('status', Transaction::STATUS_APPROVED)
                ->count();

            $stats[] = [
                'date' => $date,
                'amount' => $amount,
                'count' => $count
            ];
        }

        return $stats;
    }

    /**
     * Lấy top khách hàng nạp nhiều nhất
     */
    private function getTopCustomers($startDate, $limit = 10)
    {
        return Transaction::join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.created_at', '>=', $startDate)
            ->where('transactions.type', Transaction::TYPE_TOPUP)
            ->where('transactions.status', Transaction::STATUS_APPROVED)
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                DB::raw('SUM(transactions.amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->orderBy('total_amount', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy danh sách giao dịch nạp mới nhất
     */
    private function getRecentTransactions($limit = 20)
    {
        return Transaction::with('user')
            ->where('type', Transaction::TYPE_TOPUP)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy thống kê theo trạng thái
     */
    private function getStatusStatistics($startDate)
    {
        return Transaction::where('created_at', '>=', $startDate)
            ->where('type', Transaction::TYPE_TOPUP)
            ->select('status', DB::raw('count(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => [
                    'count' => $item->count,
                    'amount' => $item->total_amount
                ]];
            });
    }

    /**
     * Chuẩn bị dữ liệu cho biểu đồ
     */
    private function prepareChartData($dailyStats, $statusStats, $monthlyStats)
    {
        return [
            'daily' => [
                'labels' => array_column($dailyStats, 'date'),
                'amount' => array_column($dailyStats, 'amount'),
                'count' => array_column($dailyStats, 'count')
            ],
            'status' => [
                'labels' => array_keys($statusStats->toArray()),
                'data' => array_values(array_column($statusStats->toArray(), 'count')),
                'amounts' => array_values(array_column($statusStats->toArray(), 'amount'))
            ],
            'monthly' => [
                'labels' => $monthlyStats->pluck('month')->toArray(),
                'amounts' => $monthlyStats->pluck('total_amount')->toArray(),
                'counts' => $monthlyStats->pluck('count')->toArray()
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
