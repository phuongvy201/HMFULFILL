<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExcelOrder;
use App\Models\ExcelOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderStatisticsController extends Controller
{
    /**
     * Hiển thị dashboard thống kê đơn hàng
     */
    public function dashboard(Request $request)
    {
        try {
            $period = $request->input('period', 'month'); // day, week, month, year
            $startDate = $this->getStartDate($period);

            // Thống kê tổng quan
            $totalOrders = ExcelOrder::where('created_at', '>=', $startDate)->count();
            $totalRevenue = $this->calculateTotalRevenue($startDate);
            $totalItems = $this->calculateTotalItems($startDate);
            $averageOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

            // Thống kê theo trạng thái
            $statusStats = $this->getStatusStatistics($startDate);

            // Thống kê theo warehouse
            $warehouseStats = $this->getWarehouseStatistics($startDate);

            // Thống kê theo thời gian (7 ngày gần nhất)
            $dailyStats = $this->getDailyStatistics(7);

            // Top sản phẩm bán chạy
            $topProducts = $this->getTopProducts($startDate, 10);

            // Thống kê theo khách hàng
            $customerStats = $this->getCustomerStatistics($startDate);

            // Thống kê doanh thu theo thời gian
            $revenueStats = $this->getRevenueStatistics($startDate, $period);

            // Dữ liệu cho biểu đồ
            $chartData = $this->prepareChartData($dailyStats, $statusStats, $warehouseStats);

            return view('admin.statistics.dashboard', compact(
                'totalOrders',
                'totalRevenue',
                'totalItems',
                'averageOrderValue',
                'statusStats',
                'warehouseStats',
                'dailyStats',
                'topProducts',
                'customerStats',
                'revenueStats',
                'chartData',
                'period'
            ));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thống kê dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tải dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị trang thống kê chi tiết
     */
    public function detailedStats(Request $request)
    {
        try {
            $period = $request->input('period', 'month');
            $startDate = $this->getStartDate($period);

            // Thống kê theo trạng thái
            $statusStats = $this->getStatusStatistics($startDate);

            // Thống kê theo warehouse
            $warehouseStats = $this->getWarehouseStatistics($startDate);

            // Thống kê theo khách hàng
            $customerStats = $this->getCustomerStatistics($startDate);

            // Top sản phẩm
            $topProducts = $this->getTopProducts($startDate, 20);

            // Thống kê doanh thu
            $revenueStats = $this->getRevenueStatistics($startDate, $period);

            return view('admin.statistics.detailed', compact(
                'statusStats',
                'warehouseStats',
                'customerStats',
                'topProducts',
                'revenueStats',
                'period'
            ));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thống kê chi tiết: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tải thống kê chi tiết: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị trang báo cáo
     */
    public function reports(Request $request)
    {
        try {
            $period = $request->input('period', 'month');
            $startDate = $this->getStartDate($period);

            // Dữ liệu cho báo cáo
            $reportData = [
                'period' => $period,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'total_orders' => ExcelOrder::where('created_at', '>=', $startDate)->count(),
                'total_revenue' => $this->calculateTotalRevenue($startDate),
                'total_items' => $this->calculateTotalItems($startDate),
                'status_breakdown' => $this->getStatusStatistics($startDate),
                'warehouse_breakdown' => $this->getWarehouseStatistics($startDate),
                'top_products' => $this->getTopProducts($startDate, 15),
                'customer_breakdown' => $this->getCustomerStatistics($startDate),
                'daily_trends' => $this->getDailyStatistics(30)
            ];

            return view('admin.statistics.reports', compact('reportData'));
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo báo cáo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tạo báo cáo: ' . $e->getMessage());
        }
    }

    /**
     * Tính tổng doanh thu
     */
    private function calculateTotalRevenue($startDate)
    {
        return ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->where('excel_orders.created_at', '>=', $startDate)
            ->sum(DB::raw('excel_order_items.print_price * excel_order_items.quantity'));
    }

    /**
     * Tính tổng số sản phẩm
     */
    private function calculateTotalItems($startDate)
    {
        return ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->where('excel_orders.created_at', '>=', $startDate)
            ->sum('excel_order_items.quantity');
    }

    /**
     * Lấy thống kê theo trạng thái
     */
    private function getStatusStatistics($startDate)
    {
        return ExcelOrder::where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });
    }

    /**
     * Lấy thống kê theo warehouse
     */
    private function getWarehouseStatistics($startDate)
    {
        return ExcelOrder::where('created_at', '>=', $startDate)
            ->select('warehouse', DB::raw('count(*) as count'))
            ->groupBy('warehouse')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->warehouse => $item->count];
            });
    }

    /**
     * Lấy thống kê theo ngày
     */
    private function getDailyStatistics($days = 7)
    {
        $stats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $startOfDay = Carbon::parse($date)->startOfDay();
            $endOfDay = Carbon::parse($date)->endOfDay();

            $orderCount = ExcelOrder::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            $revenue = ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
                ->whereBetween('excel_orders.created_at', [$startOfDay, $endOfDay])
                ->sum(DB::raw('excel_order_items.print_price * excel_order_items.quantity'));

            $stats[] = [
                'date' => $date,
                'orders' => $orderCount,
                'revenue' => round($revenue, 2)
            ];
        }

        return $stats;
    }

    /**
     * Lấy top sản phẩm bán chạy
     */
    private function getTopProducts($startDate, $limit = 10)
    {
        return ExcelOrderItem::join('excel_orders', 'excel_order_items.excel_order_id', '=', 'excel_orders.id')
            ->where('excel_orders.created_at', '>=', $startDate)
            ->select(
                'excel_order_items.part_number',
                'excel_order_items.title',
                DB::raw('SUM(excel_order_items.quantity) as total_quantity'),
                DB::raw('SUM(excel_order_items.print_price * excel_order_items.quantity) as total_revenue')
            )
            ->groupBy('excel_order_items.part_number', 'excel_order_items.title')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy thống kê theo khách hàng
     */
    private function getCustomerStatistics($startDate)
    {
        return ExcelOrder::where('excel_orders.created_at', '>=', $startDate)
            ->join('users', 'excel_orders.created_by', '=', 'users.id')
            ->join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                DB::raw('count(DISTINCT excel_orders.id) as order_count'),
                DB::raw('SUM(excel_order_items.print_price * excel_order_items.quantity) as total_revenue')
            )
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    /**
     * Lấy thống kê doanh thu theo thời gian
     */
    private function getRevenueStatistics($startDate, $period)
    {
        $query = ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->where('excel_orders.created_at', '>=', $startDate);

        switch ($period) {
            case 'day':
                $query->select(
                    DB::raw('DATE(excel_orders.created_at) as date'),
                    DB::raw('SUM(excel_order_items.print_price * excel_order_items.quantity) as revenue'),
                    DB::raw('COUNT(DISTINCT excel_orders.id) as orders')
                )
                    ->groupBy('date')
                    ->orderBy('date');
                break;

            case 'week':
                $query->select(
                    DB::raw('YEARWEEK(excel_orders.created_at) as week'),
                    DB::raw('SUM(excel_order_items.print_price * excel_order_items.quantity) as revenue'),
                    DB::raw('COUNT(DISTINCT excel_orders.id) as orders')
                )
                    ->groupBy('week')
                    ->orderBy('week');
                break;

            case 'month':
                $query->select(
                    DB::raw('DATE_FORMAT(excel_orders.created_at, "%Y-%m") as month'),
                    DB::raw('SUM(excel_order_items.print_price * excel_order_items.quantity) as revenue'),
                    DB::raw('COUNT(DISTINCT excel_orders.id) as orders')
                )
                    ->groupBy('month')
                    ->orderBy('month');
                break;

            case 'year':
                $query->select(
                    DB::raw('YEAR(excel_orders.created_at) as year'),
                    DB::raw('SUM(excel_order_items.print_price * excel_order_items.quantity) as revenue'),
                    DB::raw('COUNT(DISTINCT excel_orders.id) as orders')
                )
                    ->groupBy('year')
                    ->orderBy('year');
                break;
        }

        return $query->get();
    }

    /**
     * Chuẩn bị dữ liệu cho biểu đồ
     */
    private function prepareChartData($dailyStats, $statusStats, $warehouseStats)
    {
        return [
            'daily' => [
                'labels' => array_column($dailyStats, 'date'),
                'orders' => array_column($dailyStats, 'orders'),
                'revenue' => array_column($dailyStats, 'revenue')
            ],
            'status' => [
                'labels' => array_keys($statusStats->toArray()),
                'data' => array_values($statusStats->toArray())
            ],
            'warehouse' => [
                'labels' => array_keys($warehouseStats->toArray()),
                'data' => array_values($warehouseStats->toArray())
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
