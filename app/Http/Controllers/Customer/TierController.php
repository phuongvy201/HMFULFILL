<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\UserTier;
use App\Models\ExcelOrder;
use App\Services\UserTierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TierController extends Controller
{
    protected $tierService;

    public function __construct(UserTierService $tierService)
    {
        $this->tierService = $tierService;
    }

    /**
     * Display customer tier information
     */
    public function index()
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        // Lấy tier hiện tại
        $currentTier = UserTier::getCurrentTier($user->id);

        // Lấy số đơn hàng tháng hiện tại
        $currentMonthOrders = ExcelOrder::where('created_by', $user->id)
            ->whereBetween('created_at', [
                $currentMonth->startOfDay(),
                $currentMonth->copy()->endOfMonth()->endOfDay()
            ])
            ->count();

        // Tính tổng tiền fulfill tháng hiện tại
        $currentMonthRevenue = ExcelOrder::calculateUserRevenue(
            $user->id,
            $currentMonth->startOfDay(),
            $currentMonth->copy()->endOfMonth()->endOfDay()
        );

        // Tính tổng tiền fulfill tổng cộng
        $totalRevenue = ExcelOrder::calculateUserRevenue(
            $user->id,
            Carbon::createFromDate(2020, 1, 1),
            Carbon::now()->endOfDay()
        );

        // Lấy lịch sử tier (6 tháng gần nhất)
        $tierHistory = UserTier::where('user_id', $user->id)
            ->where('month', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->orderBy('month', 'desc')
            ->get();

        // Tính số đơn cần thêm để lên tier
        $nextTierInfo = $this->getNextTierInfo($currentTier ? $currentTier->tier : 'Wood', $currentMonthOrders);

        // Lấy quyền lợi tier
        $tierBenefits = $this->getTierBenefits($currentTier ? $currentTier->tier : 'Wood');

        return view('customer.tier.index', compact(
            'currentTier',
            'currentMonthOrders',
            'currentMonthRevenue',
            'totalRevenue',
            'tierHistory',
            'nextTierInfo',
            'tierBenefits',
            'currentMonth'
        ));
    }

    /**
     * API endpoint để lấy thông tin tier
     */
    public function apiGetTierInfo()
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();

        // Lấy tier hiện tại
        $currentTier = UserTier::getCurrentTier($user->id);

        // Lấy số đơn hàng tháng hiện tại
        $currentMonthOrders = ExcelOrder::where('created_by', $user->id)
            ->whereBetween('created_at', [
                $currentMonth->startOfDay(),
                $currentMonth->copy()->endOfMonth()->endOfDay()
            ])
            ->count();

        // Tính tổng tiền fulfill tháng hiện tại
        $currentMonthRevenue = ExcelOrder::calculateUserRevenue(
            $user->id,
            $currentMonth->startOfDay(),
            $currentMonth->copy()->endOfMonth()->endOfDay()
        );

        // Tính số đơn cần thêm để lên tier
        $nextTierInfo = $this->getNextTierInfo($currentTier ? $currentTier->tier : 'Wood', $currentMonthOrders);

        return response()->json([
            'success' => true,
            'data' => [
                'current_tier' => $currentTier ? $currentTier->tier : 'Wood',
                'tier_effective_date' => $currentTier ? $currentTier->month->format('d/m/Y') : $currentMonth->format('d/m/Y'),
                'current_month_orders' => $currentMonthOrders,
                'current_month_revenue' => $currentMonthRevenue,
                'next_tier_info' => $nextTierInfo
            ]
        ]);
    }

    /**
     * Tính số đơn cần thêm để lên tier tiếp theo
     */
    private function getNextTierInfo($currentTier, $currentOrders)
    {
        $tierThresholds = [
            'Wood' => ['next' => 'Silver', 'threshold' => 1500],
            'Silver' => ['next' => 'Gold', 'threshold' => 4500],
            'Gold' => ['next' => 'Diamond', 'threshold' => 9000],
            'Diamond' => ['next' => null, 'threshold' => null],
            'Special' => ['next' => null, 'threshold' => null] // Thêm xử lý cho Special tier
        ];

        // Xử lý đặc biệt cho Special tier
        if ($currentTier === 'Special') {
            return [
                'next_tier' => null,
                'orders_needed' => 0,
                'message' => 'Bạn đang ở tier đặc biệt được set bởi admin!'
            ];
        }

        if ($currentTier === 'Diamond') {
            return [
                'next_tier' => null,
                'orders_needed' => 0,
                'message' => 'Bạn đã đạt tier cao nhất!'
            ];
        }

        $nextTierData = $tierThresholds[$currentTier];
        $ordersNeeded = max(0, $nextTierData['threshold'] - $currentOrders);

        return [
            'next_tier' => $nextTierData['next'],
            'threshold' => $nextTierData['threshold'],
            'orders_needed' => $ordersNeeded,
            'current_orders' => $currentOrders,
            'message' => $ordersNeeded > 0 ?
                "Bạn cần thêm {$ordersNeeded} đơn hàng để lên tier {$nextTierData['next']}" :
                "Chúc mừng! Bạn đã đủ điều kiện lên tier {$nextTierData['next']}!"
        ];
    }

    /**
     * Get tier benefits
     */
    private function getTierBenefits($tier)
    {
        $benefits = [
            'Wood' => [
                'Basic pricing according to price list',
            ],
            'Silver' => [
                'Exclusive benefits for Silver members – enjoy special discounted prices on all products'
            ],
            'Gold' => [
                'Exclusive benefits for Gold members – enjoy special discounted prices on all products'
            ],
            'Diamond' => [
                'Exclusive benefits for Diamond members – enjoy special discounted prices on all products'
            ],
            'Special' => [
                'Exclusive benefits for Special members – enjoy special discounted prices on all products (set by admin)'
            ]
        ];

        return $benefits[$tier] ?? $benefits['Wood'];
    }
}
