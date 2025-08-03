<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExcelOrder;
use App\Models\ExcelOrderItem;
use App\Models\UserTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CustomerDashboardController extends Controller
{
    /**
     * Hiển thị dashboard cho customer
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $period = $request->input('period', 'month'); // day, week, month, year, custom



            // Xử lý custom date range
            if ($period === 'custom') {
                $startDateInput = $request->input('start_date');
                $endDateInput = $request->input('end_date');

                try {
                    // Hỗ trợ cả định dạng d/m/Y và Y-m-d
                    if ($startDateInput) {
                        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $startDateInput)) {
                            // Định dạng dd/mm/yyyy
                            $startDate = Carbon::createFromFormat('d/m/Y', $startDateInput)->startOfDay();
                        } else {
                            // Định dạng yyyy-mm-dd hoặc các định dạng khác
                            $startDate = Carbon::parse($startDateInput)->startOfDay();
                        }
                    } else {
                        $startDate = Carbon::now()->subDays(30)->startOfDay();
                    }

                    if ($endDateInput) {
                        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $endDateInput)) {
                            // Định dạng dd/mm/yyyy
                            $endDate = Carbon::createFromFormat('d/m/Y', $endDateInput)->endOfDay();
                        } else {
                            // Định dạng yyyy-mm-dd hoặc các định dạng khác
                            $endDate = Carbon::parse($endDateInput)->endOfDay();
                        }
                    } else {
                        $endDate = Carbon::now()->endOfDay();
                    }
                } catch (\Exception $e) {
                    Log::error('CustomerDashboard - Date parsing error:', [
                        'start_date_input' => $startDateInput,
                        'end_date_input' => $endDateInput,
                        'error' => $e->getMessage()
                    ]);

                    // Fallback to default dates
                    $startDate = Carbon::now()->subDays(30)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                }
            } else {
                $startDate = $this->getStartDate($period);
                $endDate = Carbon::now()->endOfDay();
            }

            // Thống kê tổng quan của customer
            $totalOrders = ExcelOrder::where('created_by', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Kiểm tra có đơn hàng nào của user không (tổng cộng)
            $totalUserOrders = ExcelOrder::where('created_by', $user->id)->count();

            // Lấy đơn hàng đầu tiên và cuối cùng để biết range thật
            $firstOrder = ExcelOrder::where('created_by', $user->id)
                ->orderBy('created_at', 'asc')
                ->first();
            $lastOrder = ExcelOrder::where('created_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $totalSpending = $this->calculateTotalSpending($user->id, $startDate, $endDate);
            $totalItems = $this->calculateTotalItems($user->id, $startDate, $endDate);
            $averageOrderValue = $totalOrders > 0 ? round($totalSpending / $totalOrders, 2) : 0;

            // Thống kê theo trạng thái đơn hàng
            $orderStatusStats = $this->getOrderStatusStats($user->id, $startDate, $endDate);

            // Đơn hàng gần đây
            $recentOrders = $this->getRecentOrders($user->id, 5);

            // Thống kê chi tiêu theo khoảng thời gian đã chọn
            if ($period === 'custom') {
                $dailySpending = $this->getCustomPeriodSpending($user->id, $startDate, $endDate);
            } else {
                $dailySpending = $this->getDailySpending($user->id, 7);
            }

            // Top sản phẩm đã mua
            $topProducts = $this->getTopProducts($user->id, $startDate, $endDate, 5);

            // Thông tin tier của customer
            $tierInfo = $this->getTierInfo($user->id);

            // Dữ liệu cho biểu đồ
            $chartData = $this->prepareChartData($dailySpending, $orderStatusStats);

            // Thông tin wallet
            $walletBalance = $user->wallet ? $user->wallet->balance : 0;



            // Thêm thông tin range thời gian có đơn hàng
            $orderDateRange = null;
            if ($totalUserOrders > 0) {
                $orderDateRange = [
                    'first_order_date' => $firstOrder ? $firstOrder->created_at->format('d/m/Y') : null,
                    'last_order_date' => $lastOrder ? $lastOrder->created_at->format('d/m/Y') : null,
                ];
            }

            return view('customer.dashboard', compact(
                'totalOrders',
                'totalSpending',
                'totalItems',
                'averageOrderValue',
                'orderStatusStats',
                'recentOrders',
                'dailySpending',
                'topProducts',
                'tierInfo',
                'chartData',
                'walletBalance',
                'period',
                'startDate',
                'endDate',
                'orderDateRange'
            ));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy dashboard customer: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tải dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Tính tổng chi tiêu của customer
     */
    private function calculateTotalSpending($userId, $startDate, $endDate = null)
    {
        $query = ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->where('excel_orders.created_by', $userId);

        if ($endDate) {
            $query->whereBetween('excel_orders.created_at', [$startDate, $endDate]);
        } else {
            $query->where('excel_orders.created_at', '>=', $startDate);
        }

        $totalSpending = $query->sum(DB::raw('excel_order_items.print_price * excel_order_items.quantity'));

        return $totalSpending;
    }

    /**
     * Tính tổng số sản phẩm đã mua
     */
    private function calculateTotalItems($userId, $startDate, $endDate = null)
    {
        $query = ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->where('excel_orders.created_by', $userId);

        if ($endDate) {
            $query->whereBetween('excel_orders.created_at', [$startDate, $endDate]);
        } else {
            $query->where('excel_orders.created_at', '>=', $startDate);
        }

        $totalItems = $query->sum('excel_order_items.quantity');

        return $totalItems;
    }

    /**
     * Lấy thống kê trạng thái đơn hàng
     */
    private function getOrderStatusStats($userId, $startDate, $endDate = null)
    {
        $query = ExcelOrder::where('created_by', $userId);

        if ($endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $query->where('created_at', '>=', $startDate);
        }

        $statusStats = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });

        return $statusStats;
    }

    /**
     * Lấy đơn hàng gần đây
     */
    private function getRecentOrders($userId, $limit = 5)
    {
        return ExcelOrder::where('created_by', $userId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                $order->total_amount = $order->items->sum(function ($item) {
                    return $item->print_price * $item->quantity;
                });
                return $order;
            });
    }

    /**
     * Lấy thống kê chi tiêu hàng ngày
     */
    private function getDailySpending($userId, $days = 7)
    {
        $stats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $startOfDay = Carbon::parse($date)->startOfDay();
            $endOfDay = Carbon::parse($date)->endOfDay();

            $orderCount = ExcelOrder::where('created_by', $userId)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $spending = ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
                ->where('excel_orders.created_by', $userId)
                ->whereBetween('excel_orders.created_at', [$startOfDay, $endOfDay])
                ->sum(DB::raw('excel_order_items.print_price * excel_order_items.quantity'));

            $stats[] = [
                'date' => $date,
                'orders' => $orderCount,
                'spending' => round($spending, 2)
            ];
        }

        return $stats;
    }

    /**
     * Lấy thống kê chi tiêu theo khoảng thời gian custom
     */
    private function getCustomPeriodSpending($userId, $startDate, $endDate)
    {
        $stats = [];
        $current = $startDate->copy();
        $daysDiff = $startDate->diffInDays($endDate);

        // Kiểm tra có đơn hàng nào của user trong khoảng thời gian này không
        $totalOrdersInRange = ExcelOrder::where('created_by', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Nếu khoảng thời gian quá dài (hơn 30 ngày), nhóm theo tuần
        if ($daysDiff > 30) {
            while ($current <= $endDate) {
                $weekStart = $current->copy()->startOfWeek();
                $weekEnd = $current->copy()->endOfWeek();

                // Đảm bảo không vượt quá endDate
                if ($weekEnd > $endDate) {
                    $weekEnd = $endDate->copy()->endOfDay();
                }

                $orderCount = ExcelOrder::where('created_by', $userId)
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->count();

                $spending = ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
                    ->where('excel_orders.created_by', $userId)
                    ->whereBetween('excel_orders.created_at', [$weekStart, $weekEnd])
                    ->sum(DB::raw('excel_order_items.print_price * excel_order_items.quantity'));

                $stats[] = [
                    'date' => $weekStart->format('Y-m-d') . ' - ' . $weekEnd->format('Y-m-d'),
                    'orders' => $orderCount,
                    'spending' => round($spending, 2)
                ];

                $current->addWeek();
            }
        } else {
            // Nếu khoảng thời gian ngắn (30 ngày hoặc ít hơn), hiển thị theo ngày
            while ($current <= $endDate) {
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();

                $orderCount = ExcelOrder::where('created_by', $userId)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();

                $spending = ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
                    ->where('excel_orders.created_by', $userId)
                    ->whereBetween('excel_orders.created_at', [$dayStart, $dayEnd])
                    ->sum(DB::raw('excel_order_items.print_price * excel_order_items.quantity'));

                $stats[] = [
                    'date' => $current->format('Y-m-d'),
                    'orders' => $orderCount,
                    'spending' => round($spending, 2)
                ];

                $current->addDay();
            }
        }

        return $stats;
    }

    /**
     * Lấy top sản phẩm đã mua
     */
    private function getTopProducts($userId, $startDate, $endDate = null, $limit = 5)
    {
        $query = ExcelOrderItem::join('excel_orders', 'excel_order_items.excel_order_id', '=', 'excel_orders.id')
            ->where('excel_orders.created_by', $userId);

        if ($endDate) {
            $query->whereBetween('excel_orders.created_at', [$startDate, $endDate]);
        } else {
            $query->where('excel_orders.created_at', '>=', $startDate);
        }

        $topProducts = $query->select(
            'excel_order_items.part_number',
            'excel_order_items.title',
            DB::raw('SUM(excel_order_items.quantity) as total_quantity'),
            DB::raw('SUM(excel_order_items.print_price * excel_order_items.quantity) as total_spent')
        )
            ->groupBy('excel_order_items.part_number', 'excel_order_items.title')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        return $topProducts;
    }

    /**
     * Lấy thông tin tier của customer
     */
    private function getTierInfo($userId)
    {
        $userTier = UserTier::getCurrentTier($userId);

        // Lấy tier thresholds theo tổng số đơn hàng
        $tierThresholds = [
            'Diamond' => 9000,  // >= 9000 đơn hàng
            'Gold' => 2500,     // >= 2500 và < 9000 đơn hàng
            'Silver' => 1500,   // >= 1500 và < 2500 đơn hàng
            'Wood' => 0         // < 1500 đơn hàng
        ];

        // Tính số đơn hàng tháng hiện tại
        $currentMonthOrders = $this->getCurrentMonthOrders($userId);

        // Tính chi tiêu tháng hiện tại
        $currentMonthSpending = $this->getCurrentMonthSpending($userId);

        $currentTier = $userTier ? $userTier->tier : 'Wood';
        $currentOrderCount = $userTier ? $userTier->order_count : 0;
        $currentRevenue = $userTier ? $userTier->revenue : 0;

        // Tính tier dự kiến dựa trên tổng số đơn hàng
        $expectedTier = 'Wood';
        if ($currentOrderCount >= 9000) {
            $expectedTier = 'Diamond';
        } elseif ($currentOrderCount >= 2500) {
            $expectedTier = 'Gold';
        } elseif ($currentOrderCount >= 1500) {
            $expectedTier = 'Silver';
        }

        // Tính progress đến tier tiếp theo dựa trên tổng số đơn hàng
        $nextTier = null;
        $progressToNext = 0;
        $ordersNeeded = 0;

        if ($currentOrderCount < 1500) {
            $nextTier = 'Silver';
            $ordersNeeded = 1500 - $currentOrderCount;
            $progressToNext = ($currentOrderCount / 1500) * 100;
        } elseif ($currentOrderCount >= 1500 && $currentOrderCount < 2500) {
            $nextTier = 'Gold';
            $ordersNeeded = 2500 - $currentOrderCount;
            $progressToNext = (($currentOrderCount - 1500) / (2500 - 1500)) * 100;
        } elseif ($currentOrderCount >= 2500 && $currentOrderCount < 9000) {
            $nextTier = 'Diamond';
            $ordersNeeded = 9000 - $currentOrderCount;
            $progressToNext = (($currentOrderCount - 2500) / (9000 - 2500)) * 100;
        } elseif ($currentOrderCount >= 9000) {
            $nextTier = null; // Đã đạt tier cao nhất
            $ordersNeeded = 0;
            $progressToNext = 100;
        }

        return [
            'tier_name' => $currentTier,
            'expected_tier' => $expectedTier,
            'total_spent' => $currentRevenue,
            'current_month_spent' => $currentMonthSpending,
            'total_orders' => $currentOrderCount,
            'current_month_orders' => $currentMonthOrders,
            'next_tier' => $nextTier,
            'orders_needed' => $ordersNeeded,
            'progress_to_next' => min(100, max(0, $progressToNext)),
            'tier_month' => $userTier ? $userTier->month->format('m/Y') : null,
            'tier_thresholds' => $tierThresholds
        ];
    }

    /**
     * Tính số đơn hàng tháng hiện tại
     */
    private function getCurrentMonthOrders($userId)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return ExcelOrder::where('created_by', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
    }

    /**
     * Tính chi tiêu tháng hiện tại
     */
    private function getCurrentMonthSpending($userId)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return ExcelOrder::join('excel_order_items', 'excel_orders.id', '=', 'excel_order_items.excel_order_id')
            ->where('excel_orders.created_by', $userId)
            ->whereBetween('excel_orders.created_at', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('excel_order_items.print_price * excel_order_items.quantity'));
    }

    /**
     * Chuẩn bị dữ liệu cho biểu đồ
     */
    private function prepareChartData($dailySpending, $orderStatusStats)
    {
        return [
            'daily' => [
                'labels' => array_column($dailySpending, 'date'),
                'orders' => array_column($dailySpending, 'orders'),
                'spending' => array_column($dailySpending, 'spending')
            ],
            'status' => [
                'labels' => array_keys($orderStatusStats->toArray()),
                'data' => array_values($orderStatusStats->toArray())
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
