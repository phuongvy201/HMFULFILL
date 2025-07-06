<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserTierService;
use App\Models\User;
use App\Models\UserTier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserTierController extends Controller
{
    protected $tierService;

    public function __construct(UserTierService $tierService)
    {
        $this->tierService = $tierService;
    }

    /**
     * Hiển thị danh sách khách hàng với tier
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $tierFilter = $request->get('tier', '');

            // Lấy danh sách khách hàng với tier
            $customers = UserTier::getCustomerListWithTiersPaginated($perPage);

            // Lọc theo search
            if ($search) {
                $customers = $customers->filter(function ($customer) use ($search) {
                    return str_contains(strtolower($customer['customer_name']), strtolower($search)) ||
                        str_contains(strtolower($customer['email']), strtolower($search)) ||
                        str_contains(strtolower($customer['phone']), strtolower($search));
                });
            }

            // Lọc theo tier
            if ($tierFilter) {
                $customers = $customers->filter(function ($customer) use ($tierFilter) {
                    return $customer['current_tier'] === $tierFilter;
                });
            }

            // Lấy danh sách tier để hiển thị trong filter
            $tiers = [
                UserTier::TIER_WOOD => 'Wood',
                UserTier::TIER_SILVER => 'Silver',
                UserTier::TIER_GOLD => 'Gold',
                UserTier::TIER_DIAMOND => 'Diamond'
            ];

            return view('admin.user-tiers.index', compact('customers', 'tiers', 'search', 'tierFilter'));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách khách hàng tier: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải danh sách khách hàng');
        }
    }

    /**
     * Hiển thị chi tiết tier của khách hàng
     */
    public function show($id)
    {
        try {
            $customerDetails = UserTier::getCustomerTierDetails($id);

            if (!$customerDetails) {
                return back()->with('error', 'Không tìm thấy thông tin khách hàng');
            }

            return view('admin.user-tiers.show', compact('customerDetails'));
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết tier khách hàng: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải thông tin khách hàng');
        }
    }

    /**
     * Cập nhật tier thủ công cho khách hàng
     */
    public function updateTier(Request $request, $id)
    {
        try {
            $request->validate([
                'tier' => 'required|in:Wood,Silver,Gold,Diamond',
                'order_count' => 'required|integer|min:0',
                'month' => 'required|date_format:Y-m',
                'notes' => 'nullable|string|max:500'
            ]);

            $user = User::findOrFail($id);

            if ($user->role !== 'customer') {
                return back()->with('error', 'Chỉ có thể cập nhật tier cho khách hàng');
            }

            $month = \Carbon\Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();

            // Cập nhật hoặc tạo tier mới
            $userTier = UserTier::createOrUpdateTier(
                $user->id,
                $request->tier,
                $request->order_count,
                $month
            );

            // Lưu ghi chú nếu có
            if ($request->notes) {
                // Có thể tạo bảng notes riêng để lưu ghi chú
                Log::info("Tier update note for user {$user->id}: " . $request->notes);
            }

            return back()->with('success', 'Cập nhật tier thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật tier: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật tier');
        }
    }

    /**
     * API endpoint để lấy danh sách khách hàng với tier
     */
    public function apiIndex(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $tierFilter = $request->get('tier', '');

            $customers = UserTier::getCustomerListWithTiersPaginated($perPage);

            // Lọc theo search
            if ($search) {
                $customers = $customers->filter(function ($customer) use ($search) {
                    return str_contains(strtolower($customer['customer_name']), strtolower($search)) ||
                        str_contains(strtolower($customer['email']), strtolower($search)) ||
                        str_contains(strtolower($customer['phone']), strtolower($search));
                });
            }

            // Lọc theo tier
            if ($tierFilter) {
                $customers = $customers->filter(function ($customer) use ($tierFilter) {
                    return $customer['current_tier'] === $tierFilter;
                });
            }

            return response()->json([
                'success' => true,
                'data' => $customers->items(),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('API lỗi khi lấy danh sách khách hàng tier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách khách hàng'
            ], 500);
        }
    }

    /**
     * API endpoint để lấy chi tiết tier của khách hàng
     */
    public function apiShow($id)
    {
        try {
            $customerDetails = UserTier::getCustomerTierDetails($id);

            if (!$customerDetails) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $customerDetails
            ]);
        } catch (\Exception $e) {
            Log::error('API lỗi khi lấy chi tiết tier khách hàng: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải thông tin khách hàng'
            ], 500);
        }
    }

    /**
     * API endpoint để cập nhật tier thủ công
     */
    public function apiUpdateTier(Request $request, $id)
    {
        try {
            $request->validate([
                'tier' => 'required|in:Wood,Silver,Gold,Diamond',
                'order_count' => 'required|integer|min:0',
                'month' => 'required|date_format:Y-m',
                'notes' => 'nullable|string|max:500'
            ]);

            $user = User::findOrFail($id);

            if ($user->role !== 'customer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể cập nhật tier cho khách hàng'
                ], 400);
            }

            $month = \Carbon\Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();

            // Cập nhật hoặc tạo tier mới
            $userTier = UserTier::createOrUpdateTier(
                $user->id,
                $request->tier,
                $request->order_count,
                $month
            );

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tier thành công',
                'data' => $userTier
            ]);
        } catch (\Exception $e) {
            Log::error('API lỗi khi cập nhật tier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật tier'
            ], 500);
        }
    }

    /**
     * Chạy tính toán tier cho tất cả user
     */
    public function calculateTiers(Request $request)
    {
        try {
            $month = $request->filled('month')
                ? Carbon::createFromFormat('Y-m', $request->month)
                : Carbon::now()->subMonth();

            $results = $this->tierService->calculateAndUpdateTiers($month);

            return response()->json([
                'success' => true,
                'message' => 'Tính toán tier thành công!',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tính toán tier: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tính toán tier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Chạy tính toán tier cho user cụ thể
     */
    public function calculateTierForUser(Request $request, User $user)
    {
        try {
            $month = $request->filled('month')
                ? Carbon::createFromFormat('Y-m', $request->month)
                : Carbon::now()->subMonth();

            $result = $this->tierService->calculateTierForUser($user->id, $month);

            return response()->json([
                'success' => true,
                'message' => 'Tính toán tier thành công!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi tính toán tier cho user {$user->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tính toán tier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê tier
     */
    public function getStatistics()
    {
        try {
            $stats = UserTier::getTierOverview();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thống kê tier: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint để lấy danh sách khách hàng với tier hiện tại
     */
    public function getCustomersList(Request $request)
    {
        try {
            $search = $request->get('search');
            $tierFilter = $request->get('tier');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);

            $customers = UserTier::getCustomersWithCurrentTier($search, $tierFilter);

            // Phân trang
            $offset = ($page - 1) * $perPage;
            $paginatedCustomers = $customers->slice($offset, $perPage);
            $total = $customers->count();

            $data = [
                'customers' => $paginatedCustomers->values(),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách khách hàng: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách khách hàng: ' . $e->getMessage()
            ], 500);
        }
    }
}
