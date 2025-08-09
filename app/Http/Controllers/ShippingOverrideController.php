<?php

namespace App\Http\Controllers;

use App\Services\ShippingOverrideService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ShippingOverrideController extends Controller
{
    /**
     * Lấy tất cả overrides cho một variant
     */
    public function getOverridesForVariant(int $variantId): JsonResponse
    {
        try {
            $overrides = ShippingOverrideService::getOverridesForVariant($variantId);

            return response()->json([
                'success' => true,
                'data' => $overrides,
                'message' => 'Lấy danh sách overrides thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách overrides: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy overrides cho một user
     */
    public function getOverridesForUser(int $userId): JsonResponse
    {
        try {
            $overrides = ShippingOverrideService::getOverridesForUser($userId);

            return response()->json([
                'success' => true,
                'data' => $overrides,
                'message' => 'Lấy danh sách overrides cho user thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách overrides: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy overrides cho một tier
     */
    public function getOverridesForTier(string $tierName): JsonResponse
    {
        try {
            $overrides = ShippingOverrideService::getOverridesForTier($tierName);

            return response()->json([
                'success' => true,
                'data' => $overrides,
                'message' => 'Lấy danh sách overrides cho tier thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách overrides: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thiết lập giá riêng cho user
     */
    public function setUserPrice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'variant_id' => 'required|integer|exists:product_variants,id',
                'method' => 'required|string|in:tiktok_1st,tiktok_next,seller_1st,seller_next',
                'user_id' => 'required|integer|exists:users,id',
                'price' => 'required|numeric|min:0',
                'currency' => 'required|string|in:USD,VND,GBP'
            ]);

            $override = ShippingOverrideService::setUserPrice(
                variantId: $validated['variant_id'],
                method: $validated['method'],
                userId: $validated['user_id'],
                price: $validated['price'],
                currency: $validated['currency']
            );

            return response()->json([
                'success' => true,
                'data' => $override,
                'message' => 'Thiết lập giá riêng cho user thành công'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thiết lập giá: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thiết lập giá riêng cho tier
     */
    public function setTierPrice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'variant_id' => 'required|integer|exists:product_variants,id',
                'method' => 'required|string|in:tiktok_1st,tiktok_next,seller_1st,seller_next',
                'tier_name' => 'required|string|in:Wood,Silver,Gold,Diamond,Special',
                'price' => 'required|numeric|min:0',
                'currency' => 'required|string|in:USD,VND,GBP'
            ]);

            $override = ShippingOverrideService::setTierPrice(
                variantId: $validated['variant_id'],
                method: $validated['method'],
                tierName: $validated['tier_name'],
                price: $validated['price'],
                currency: $validated['currency']
            );

            return response()->json([
                'success' => true,
                'data' => $override,
                'message' => 'Thiết lập giá riêng cho tier thành công'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thiết lập giá: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa giá riêng cho user
     */
    public function removeUserPrice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'variant_id' => 'required|integer|exists:product_variants,id',
                'method' => 'required|string|in:tiktok_1st,tiktok_next,seller_1st,seller_next',
                'user_id' => 'required|integer|exists:users,id'
            ]);

            $success = ShippingOverrideService::removeUserPrice(
                variantId: $validated['variant_id'],
                method: $validated['method'],
                userId: $validated['user_id']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa giá riêng cho user thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giá riêng cho user'
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa giá: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa giá riêng cho tier
     */
    public function removeTierPrice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'variant_id' => 'required|integer|exists:product_variants,id',
                'method' => 'required|string|in:tiktok_1st,tiktok_next,seller_1st,seller_next',
                'tier_name' => 'required|string|in:Wood,Silver,Gold,Diamond,Special'
            ]);

            $success = ShippingOverrideService::removeTierPrice(
                variantId: $validated['variant_id'],
                method: $validated['method'],
                tierName: $validated['tier_name']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa giá riêng cho tier thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giá riêng cho tier'
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa giá: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm user vào override
     */
    public function addUserToOverride(Request $request, int $overrideId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id'
            ]);

            $success = ShippingOverrideService::addUserToOverride(
                overrideId: $overrideId,
                userId: $validated['user_id']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm user vào override thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy override'
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa user khỏi override
     */
    public function removeUserFromOverride(Request $request, int $overrideId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id'
            ]);

            $success = ShippingOverrideService::removeUserFromOverride(
                overrideId: $overrideId,
                userId: $validated['user_id']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa user khỏi override thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy override'
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách users trong override
     */
    public function getUsersInOverride(int $overrideId): JsonResponse
    {
        try {
            $userIds = ShippingOverrideService::getUsersInOverride($overrideId);

            return response()->json([
                'success' => true,
                'data' => $userIds,
                'message' => 'Lấy danh sách users thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách users: ' . $e->getMessage()
            ], 500);
        }
    }
}
