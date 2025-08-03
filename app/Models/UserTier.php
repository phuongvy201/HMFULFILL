<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tier',
        'order_count',
        'month',
        'revenue'
    ];

    protected $casts = [
        'month' => 'date',
        'order_count' => 'integer',
        'revenue' => 'decimal:2'
    ];

    // Định nghĩa các tier
    const TIER_DIAMOND = 'Diamond';
    const TIER_GOLD = 'Gold';
    const TIER_SILVER = 'Silver';
    const TIER_WOOD = 'Wood';
    const TIER_SPECIAL = 'Special'; // Thêm tier Special

    // Ngưỡng đơn hàng cho từng tier
    const TIER_THRESHOLDS = [
        self::TIER_DIAMOND => 9000,
        self::TIER_GOLD => 4500,
        self::TIER_SILVER => 1500,
        self::TIER_WOOD => 0
        // Special tier không có ngưỡng vì được set thủ công
    ];

    /**
     * Relationship với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship với ShippingPrice
     */
    public function shippingPrices()
    {
        return $this->hasMany(ShippingPrice::class, 'tier_id');
    }

    /**
     * Xác định tier dựa trên số đơn hàng
     */
    public static function determineTier(int $orderCount): string
    {
        // Special tier không được xác định tự động
        if ($orderCount >= self::TIER_THRESHOLDS[self::TIER_DIAMOND]) {
            return self::TIER_DIAMOND;
        } elseif ($orderCount >= self::TIER_THRESHOLDS[self::TIER_GOLD]) {
            return self::TIER_GOLD;
        } elseif ($orderCount >= self::TIER_THRESHOLDS[self::TIER_SILVER]) {
            return self::TIER_SILVER;
        } else {
            return self::TIER_WOOD;
        }
    }

    /**
     * Kiểm tra xem một tier có phải là Special hay không
     */
    public static function isSpecialTier(string $tier): bool
    {
        return $tier === self::TIER_SPECIAL;
    }

    /**
     * Set tier Special cho user
     */
    public static function setSpecialTier(int $userId, Carbon $month, int $orderCount = 0, float $revenue = 0): self
    {
        // Luôn cập nhật tier Special, bỏ qua kiểm tra bảo vệ
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'month' => $month->startOfMonth()
            ],
            [
                'tier' => self::TIER_SPECIAL,
                'order_count' => $orderCount,
                'revenue' => $revenue
            ]
        );
    }

    /**
     * Xóa tier Special và chuyển về tier thường
     */
    public static function removeSpecialTier(int $userId, Carbon $month): ?self
    {
        $tierRecord = self::where('user_id', $userId)
            ->where('month', $month->startOfMonth())
            ->first();

        if ($tierRecord && $tierRecord->tier === self::TIER_SPECIAL) {
            // Tính toán tier thông thường dựa trên order count
            $normalTier = self::determineTier($tierRecord->order_count);

            $tierRecord->update([
                'tier' => $normalTier
            ]);

            return $tierRecord;
        }

        return null;
    }

    /**
     * Lấy tier hiện tại của user
     * 
     * @param int $userId
     * @return self|null
     */
    public static function getCurrentTier(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->orderBy('month', 'desc')
            ->first();
    }

    /**
     * Lấy tier của user cho tháng cụ thể
     */
    public static function getTierForMonth(int $userId, Carbon $month): ?self
    {
        return self::where('user_id', $userId)
            ->where('month', $month->startOfMonth())
            ->first();
    }

    /**
     * Tạo hoặc cập nhật tier cho user
     */
    public static function createOrUpdateTier(int $userId, string $tier, int $orderCount, Carbon $month, float $revenue = 0): self
    {
        $existingTier = self::where('user_id', $userId)
            ->where('month', $month->startOfMonth())
            ->first();

        // Nếu tier hiện tại là Special và tier mới không phải Special, không cập nhật
        if ($existingTier && $existingTier->tier === self::TIER_SPECIAL && $tier !== self::TIER_SPECIAL) {
            return $existingTier; // Giữ nguyên tier Special
        }

        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'month' => $month->startOfMonth()
            ],
            [
                'tier' => $tier,
                'order_count' => $orderCount,
                'revenue' => $revenue
            ]
        );
    }

    /**
     * Lấy danh sách khách hàng kèm tier hiện tại
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCustomerListWithTiers()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        return User::where('role', 'customer')
            ->with(['userTiers' => function ($query) use ($currentMonth, $previousMonth) {
                $query->whereIn('month', [$currentMonth, $previousMonth]);
            }])
            ->get()
            ->map(function ($user) use ($currentMonth, $previousMonth) {
                // Lấy tier hiện tại
                $currentTier = $user->userTiers->where('month', $currentMonth)->first();

                // Lấy tier tháng trước
                $previousTier = $user->userTiers->where('month', $previousMonth)->first();

                // Đếm số đơn hàng tháng trước từ bảng excel_orders
                $previousMonthOrderCount = \App\Models\ExcelOrder::where('created_by', $user->id)
                    ->whereBetween('created_at', [
                        $previousMonth->startOfMonth(),
                        $previousMonth->endOfMonth()
                    ])
                    ->count();

                // Tính doanh thu tháng trước từ tất cả các đơn hàng
                $previousMonthRevenue = \App\Models\ExcelOrder::calculateUserRevenue(
                    $user->id,
                    $previousMonth->startOfMonth(),
                    $previousMonth->endOfMonth()
                );

                return [
                    'id' => $user->id,
                    'customer_name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'current_tier' => $currentTier ? $currentTier->tier : 'Wood',
                    'current_tier_order_count' => $currentTier ? $currentTier->order_count : 0,
                    'current_tier_revenue' => $currentTier ? $currentTier->revenue : 0,
                    'previous_month_order_count' => $previousMonthOrderCount,
                    'previous_month_revenue' => $previousMonthRevenue,
                    'revenue' => $previousMonthRevenue, // Thêm key revenue để tương thích
                    'tier_effective_month' => $currentTier ? $currentTier->month->format('m/Y') : $currentMonth->format('m/Y'),
                    'tier_updated_at' => $currentTier ? $currentTier->updated_at->format('d/m/Y') : $currentMonth->format('d/m/Y'),
                    'tier_data' => $currentTier,
                    'previous_tier_data' => $previousTier,
                ];
            })
            ->sortBy('customer_name');
    }

    /**
     * Lấy danh sách khách hàng kèm tier hiện tại với phân trang
     * 
     * @param int $perPage Số lượng item trên mỗi trang
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getCustomerListWithTiersPaginated($perPage = 15)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $users = User::where('role', 'customer')
            ->with(['userTiers' => function ($query) use ($currentMonth, $previousMonth) {
                $query->whereIn('month', [$currentMonth, $previousMonth]);
            }])
            ->get()
            ->map(function ($user) use ($currentMonth, $previousMonth) {
                // Lấy tier hiện tại
                $currentTier = $user->userTiers->where('month', $currentMonth)->first();

                // Lấy tier tháng trước
                $previousTier = $user->userTiers->where('month', $previousMonth)->first();

                // Đếm số đơn hàng tháng trước từ bảng excel_orders
                $previousMonthOrderCount = \App\Models\ExcelOrder::where('created_by', $user->id)
                    ->whereBetween('created_at', [
                        $previousMonth->startOfMonth(),
                        $previousMonth->endOfMonth()
                    ])
                    ->count();

                // Tính doanh thu tháng trước từ tất cả các đơn hàng
                $previousMonthRevenue = \App\Models\ExcelOrder::calculateUserRevenue(
                    $user->id,
                    $previousMonth->startOfMonth(),
                    $previousMonth->endOfMonth()
                );

                return [
                    'id' => $user->id,
                    'customer_name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'current_tier' => $currentTier ? $currentTier->tier : 'Wood',
                    'current_tier_order_count' => $currentTier ? $currentTier->order_count : 0,
                    'current_tier_revenue' => $currentTier ? $currentTier->revenue : 0,
                    'previous_month_order_count' => $previousMonthOrderCount,
                    'previous_month_revenue' => $previousMonthRevenue,
                    'revenue' => $previousMonthRevenue, // Thêm key revenue để tương thích
                    'tier_effective_month' => $currentTier ? $currentTier->month->format('m/Y') : $currentMonth->format('m/Y'),
                    'tier_updated_at' => $currentTier ? $currentTier->updated_at->format('d/m/Y') : $currentMonth->format('d/m/Y'),
                    'tier_data' => $currentTier,
                    'previous_tier_data' => $previousTier,
                ];
            })
            ->sortBy('customer_name');

        // Tạo paginator
        $currentPage = request()->get('page', 1);
        $items = $users->forPage($currentPage, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $users->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Lấy thông tin tier chi tiết của một khách hàng
     * 
     * @param int $userId ID của user
     * @return array|null
     */
    public static function getCustomerTierDetails(int $userId)
    {
        $user = User::find($userId);

        if (!$user || $user->role !== 'customer') {
            return null;
        }

        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        // Lấy tier hiện tại
        $currentTier = $user->userTiers->where('month', $currentMonth)->first();

        // Lấy tier tháng trước
        $previousTier = $user->userTiers->where('month', $previousMonth)->first();

        // Đếm số đơn hàng tháng trước
        $previousMonthOrderCount = \App\Models\ExcelOrder::where('created_by', $user->id)
            ->whereBetween('created_at', [
                $previousMonth->startOfMonth(),
                $previousMonth->endOfMonth()
            ])
            ->count();

        // Tính doanh thu tháng trước từ tất cả các đơn hàng
        $previousMonthRevenue = \App\Models\ExcelOrder::calculateUserRevenue(
            $user->id,
            $previousMonth->startOfMonth(),
            $previousMonth->endOfMonth()
        );

        // Lấy lịch sử tier trong 6 tháng gần nhất
        $tierHistory = $user->userTiers()
            ->where('month', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->orderBy('month', 'desc')
            ->get();

        // Lấy thống kê đơn hàng theo trạng thái
        $orderStats = \App\Models\ExcelOrder::where('created_by', $user->id)
            ->whereBetween('created_at', [
                $previousMonth->startOfMonth(),
                $previousMonth->endOfMonth()
            ])
            ->selectRaw('status, COUNT(*) as count, SUM(
                (SELECT SUM(print_price * quantity) FROM excel_order_items WHERE excel_order_items.excel_order_id = excel_orders.id)
            ) as total_revenue')
            ->groupBy('status')
            ->get();

        return [
            'user' => $user,
            'current_tier' => $currentTier,
            'previous_tier' => $previousTier,
            'previous_month_order_count' => $previousMonthOrderCount,
            'previous_month_revenue' => $previousMonthRevenue,
            'tier_history' => $tierHistory,
            'order_stats' => $orderStats,
            'current_month' => $currentMonth->format('Y-m'),
            'previous_month' => $previousMonth->format('Y-m')
        ];
    }

    /**
     * Lấy thống kê tổng quan về tier
     * 
     * @return array
     */
    public static function getTierOverview()
    {
        try {
            $currentMonth = Carbon::now()->startOfMonth();

            // Lấy thống kê tier hiện tại từ bảng user_tiers
            $tierStats = self::select('tier')
                ->selectRaw('COUNT(*) as customer_count')
                ->selectRaw('AVG(revenue) as avg_revenue')
                ->selectRaw('SUM(revenue) as total_revenue')
                ->selectRaw('SUM(order_count) as total_orders')
                ->where('month', $currentMonth)
                ->groupBy('tier')
                ->orderByRaw("FIELD(tier, 'Special', 'Diamond', 'Gold', 'Silver', 'Wood')")
                ->get();

            // Lấy tổng số khách hàng có tier
            $totalCustomersWithTier = self::where('month', $currentMonth)->count();

            // Lấy tổng số khách hàng không có tier (chưa có record trong user_tiers)
            $totalCustomers = User::where('role', 'customer')->count();
            $customersWithoutTier = $totalCustomers - $totalCustomersWithTier;

            // Thêm khách hàng chưa có tier vào thống kê
            if ($customersWithoutTier > 0) {
                $tierStats->push((object)[
                    'tier' => 'Wood',
                    'customer_count' => $customersWithoutTier,
                    'avg_revenue' => 0,
                    'total_revenue' => 0,
                    'total_orders' => 0
                ]);
            }

            // Tính tổng balance từ bảng wallets
            $balanceStats = User::where('role', 'customer')
                ->leftJoin('user_tiers', function ($join) use ($currentMonth) {
                    $join->on('users.id', '=', 'user_tiers.user_id')
                        ->where('user_tiers.month', '=', $currentMonth);
                })
                ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
                ->selectRaw('COALESCE(user_tiers.tier, "Wood") as tier')
                ->selectRaw('SUM(COALESCE(wallets.balance, 0)) as total_balance')
                ->selectRaw('COUNT(DISTINCT users.id) as customer_count')
                ->groupBy('tier')
                ->get();

            // Merge balance data với tier stats
            $finalStats = $tierStats->map(function ($stat) use ($balanceStats) {
                $balanceData = $balanceStats->where('tier', $stat->tier)->first();
                $stat->total_balance = $balanceData ? $balanceData->total_balance : 0;
                return $stat;
            });

            return [
                'tier_stats' => $finalStats,
                'total_customers' => $totalCustomers,
                'customers_with_tier' => $totalCustomersWithTier,
                'customers_without_tier' => $customersWithoutTier,
                'current_month' => $currentMonth->format('Y-m')
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thống kê TIER: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy danh sách khách hàng với tier hiện tại
     * 
     * @param string|null $search
     * @param string|null $tierFilter
     * @return \Illuminate\Support\Collection
     */
    public static function getCustomersWithCurrentTier($search = null, $tierFilter = null)
    {
        $currentMonth = Carbon::now()->startOfMonth();

        $query = User::where('role', 'customer')
            ->leftJoin('user_tiers', function ($join) use ($currentMonth) {
                $join->on('users.id', '=', 'user_tiers.user_id')
                    ->where('user_tiers.month', '=', $currentMonth);
            })
            ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
            ->select([
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.phone',
                'wallets.balance',
                'users.created_at',
                'user_tiers.tier',
                'user_tiers.order_count',
                'user_tiers.revenue',
                'user_tiers.month as tier_month',
                'user_tiers.updated_at as tier_updated_at'
            ]);

        // Lọc theo search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.first_name', 'LIKE', "%{$search}%")
                    ->orWhere('users.last_name', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('users.phone', 'LIKE', "%{$search}%")
                    ->orWhereRaw("users.first_name || ' ' || users.last_name LIKE ?", ["%{$search}%"]);
            });
        }

        // Lọc theo tier
        if ($tierFilter) {
            if ($tierFilter === 'Wood') {
                // Wood bao gồm cả những user chưa có tier
                $query->where(function ($q) use ($tierFilter) {
                    $q->where('user_tiers.tier', $tierFilter)
                        ->orWhereNull('user_tiers.tier');
                });
            } else {
                $query->where('user_tiers.tier', $tierFilter);
            }
        }

        return $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'customer_name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'balance' => $user->balance ?? 0,
                'current_tier' => $user->tier ?? 'Wood',
                'current_tier_order_count' => $user->order_count ?? 0,
                'revenue' => $user->revenue ?? 0,
                'tier_effective_month' => $user->tier_month ? Carbon::parse($user->tier_month)->format('m/Y') : null,
                'tier_updated_at' => $user->tier_updated_at ? Carbon::parse($user->tier_updated_at)->format('d/m/Y') : null,
                'created_at' => $user->created_at->format('d/m/Y')
            ];
        });
    }

    /**
     * Lấy danh sách khách hàng với tier hiện tại với phân trang
     * 
     * @param string|null $search
     * @param string|null $tierFilter
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getCustomersWithCurrentTierPaginated($search = null, $tierFilter = null, $perPage = 15)
    {
        $currentMonth = Carbon::now()->startOfMonth();

        $query = User::where('role', 'customer')
            ->leftJoin('user_tiers', function ($join) use ($currentMonth) {
                $join->on('users.id', '=', 'user_tiers.user_id')
                    ->where('user_tiers.month', '=', $currentMonth);
            })
            ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
            ->select([
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.phone',
                'wallets.balance',
                'users.created_at',
                'user_tiers.tier',
                'user_tiers.order_count',
                'user_tiers.revenue',
                'user_tiers.month as tier_month',
                'user_tiers.updated_at as tier_updated_at'
            ]);

        // Lọc theo search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.first_name', 'LIKE', "%{$search}%")
                    ->orWhere('users.last_name', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('users.phone', 'LIKE', "%{$search}%")
                    ->orWhereRaw("users.first_name || ' ' || users.last_name LIKE ?", ["%{$search}%"]);
            });
        }

        // Lọc theo tier
        if ($tierFilter) {
            if ($tierFilter === 'Wood') {
                // Wood bao gồm cả những user chưa có tier
                $query->where(function ($q) use ($tierFilter) {
                    $q->where('user_tiers.tier', $tierFilter)
                        ->orWhereNull('user_tiers.tier');
                });
            } else {
                $query->where('user_tiers.tier', $tierFilter);
            }
        }

        // Thêm order by để sắp xếp
        $query->orderBy('users.first_name')->orderBy('users.last_name');

        // Pagination
        $results = $query->paginate($perPage);

        // Transform data
        $results->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'customer_name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'balance' => $user->balance ?? 0,
                'current_tier' => $user->tier ?? 'Wood',
                'current_tier_order_count' => $user->order_count ?? 0,
                'revenue' => $user->revenue ?? 0,
                'tier_effective_month' => $user->tier_month ? Carbon::parse($user->tier_month)->format('m/Y') : null,
                'tier_updated_at' => $user->tier_updated_at ? Carbon::parse($user->tier_updated_at)->format('d/m/Y') : null,
                'created_at' => $user->created_at->format('d/m/Y')
            ];
        });

        return $results;
    }
}
