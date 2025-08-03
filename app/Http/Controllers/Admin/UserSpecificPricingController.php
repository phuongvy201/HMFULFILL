<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserSpecificPricingService;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserSpecificPricingController extends Controller
{
    /**
     * Hiển thị danh sách user có giá riêng
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $users = User::where('role', 'customer');

        if ($search) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $users->whereHas('shippingPrices')
            ->with(['shippingPrices' => function ($query) {
                $query->with(['variant', 'variant.product']);
            }])
            ->paginate(15);

        return view('admin.user-specific-pricing.index', compact('users', 'search'));
    }

    /**
     * Hiển thị form tạo giá riêng cho user
     */
    public function create()
    {
        $users = User::where('role', 'customer')->get();
        $variants = ProductVariant::with('product')->get();
        $methods = ShippingPrice::$validMethods;

        return view('admin.user-specific-pricing.create', compact('users', 'variants', 'methods'));
    }

    /**
     * Lưu giá riêng cho user
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'variant_id' => 'required|exists:product_variants,id',
            'method' => 'required|in:' . implode(',', ShippingPrice::$validMethods),
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,VND,GBP'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shippingPrice = UserSpecificPricingService::setUserPrice(
                $request->user_id,
                $request->variant_id,
                $request->method,
                $request->price,
                $request->currency
            );

            return response()->json([
                'success' => true,
                'message' => 'User-specific price created successfully',
                'data' => $shippingPrice->load(['user', 'variant', 'variant.product'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user-specific price: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết giá riêng của user
     */
    public function show(int $userId)
    {
        $user = User::findOrFail($userId);
        $prices = UserSpecificPricingService::getUserPricesPaginated($userId);
        $stats = UserSpecificPricingService::getUserPriceStats($userId);

        return view('admin.user-specific-pricing.show', compact('user', 'prices', 'stats'));
    }

    /**
     * Hiển thị form chỉnh sửa giá riêng
     */
    public function edit(int $userId, int $variantId, string $method)
    {
        $user = User::findOrFail($userId);
        $variant = ProductVariant::with('product')->findOrFail($variantId);
        $shippingPrice = UserSpecificPricingService::getUserPrice($userId, $variantId, $method);

        if (!$shippingPrice) {
            return redirect()->back()->with('error', 'User-specific price not found');
        }

        $methods = ShippingPrice::$validMethods;

        return view('admin.user-specific-pricing.edit', compact('user', 'variant', 'shippingPrice', 'methods'));
    }

    /**
     * Cập nhật giá riêng cho user
     */
    public function update(Request $request, int $userId, int $variantId, string $method): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,VND,GBP'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shippingPrice = UserSpecificPricingService::setUserPrice(
                $userId,
                $variantId,
                $method,
                $request->price,
                $request->currency
            );

            return response()->json([
                'success' => true,
                'message' => 'User-specific price updated successfully',
                'data' => $shippingPrice->load(['user', 'variant', 'variant.product'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user-specific price: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa giá riêng cho user
     */
    public function destroy(int $userId, int $variantId, string $method): JsonResponse
    {
        try {
            $removed = UserSpecificPricingService::removeUserPrice($userId, $variantId, $method);

            if ($removed) {
                return response()->json([
                    'success' => true,
                    'message' => 'User-specific price removed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User-specific price not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove user-specific price: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API để lấy giá riêng của user
     */
    public function getUserPrice(int $userId, int $variantId, string $method): JsonResponse
    {
        $shippingPrice = UserSpecificPricingService::getUserPrice($userId, $variantId, $method);

        if (!$shippingPrice) {
            return response()->json([
                'success' => false,
                'message' => 'User-specific price not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shippingPrice->load(['user', 'variant', 'variant.product'])
        ]);
    }

    /**
     * API để lấy tất cả giá riêng của user
     */
    public function getAllUserPrices(int $userId): JsonResponse
    {
        $prices = UserSpecificPricingService::getAllUserPrices($userId);

        return response()->json([
            'success' => true,
            'data' => $prices
        ]);
    }

    /**
     * Copy giá từ user này sang user khác
     */
    public function copyPrices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id|different:from_user_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $copiedCount = UserSpecificPricingService::copyUserPrices(
                $request->from_user_id,
                $request->to_user_id
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully copied {$copiedCount} prices",
                'copied_count' => $copiedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy prices: ' . $e->getMessage()
            ], 500);
        }
    }
}
