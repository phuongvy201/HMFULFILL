<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserTier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserTierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tierFilter = $request->input('tier');
        $perPage = $request->input('per_page', 15);

        $customers = UserTier::getCustomersWithCurrentTierPaginated($search, $tierFilter, $perPage);
        $tiers = ['Diamond', 'Gold', 'Silver', 'Wood', 'Special'];

        return view('admin.user-tiers.index', compact('customers', 'tiers', 'search', 'tierFilter'));
    }

    public function show($userId)
    {
        $tierDetails = UserTier::getCustomerTierDetails($userId);
        if (!$tierDetails) {
            return redirect()->route('admin.user-tiers.index')
                ->with('error', 'Không tìm thấy thông tin khách hàng');
        }

        return view('admin.user-tiers.show', compact('tierDetails'));
    }

    public function updateTier(Request $request, $userId)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);
            $month = Carbon::parse($request->month)->startOfMonth();
            $tier = $request->tier;
            $orderCount = $request->order_count;
            $revenue = $request->revenue;
            $notes = $request->notes;

            // Kiểm tra nếu là Special tier thì phải có ghi chú
            if ($tier === 'Special' && empty($notes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập ghi chú khi set tier Special'
                ]);
            }

            // Nếu là Special tier, sử dụng phương thức setSpecialTier
            if ($tier === 'Special') {
                $userTier = UserTier::setSpecialTier($userId, $month, $orderCount, $revenue);
            } else {
                $userTier = UserTier::createOrUpdateTier($userId, $tier, $orderCount, $month, $revenue);
            }

            // Lưu ghi chú nếu có
            if ($notes) {
                // Có thể thêm một bảng mới để lưu lịch sử thay đổi tier
                Log::info("Tier của user {$userId} được cập nhật thành {$tier}. Ghi chú: {$notes}");
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tier thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật tier: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật tier: ' . $e->getMessage()
            ]);
        }
    }
}
