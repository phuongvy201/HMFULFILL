<?php

namespace App\Http\Controllers;

use App\Services\UspsTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UspsTrackingController extends Controller
{
    protected $uspsService;

    public function __construct(UspsTrackingService $uspsService)
    {
        $this->uspsService = $uspsService;
    }

    /**
     * Track một tracking number
     */
    public function trackSingle(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:50'
        ]);

        $trackingNumber = $request->input('tracking_number');

        try {
            $result = $this->uspsService->trackSinglePackage($trackingNumber);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Unknown error'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('USPS Tracking Error', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track nhiều tracking numbers
     */
    public function trackMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_numbers' => 'required|array|max:35',
            'tracking_numbers.*' => 'string|max:50'
        ]);

        $trackingNumbers = $request->input('tracking_numbers');

        try {
            $result = $this->uspsService->trackMultiplePackages($trackingNumbers);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Unknown error'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('USPS Multiple Tracking Error', [
                'tracking_numbers' => $trackingNumbers,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra trạng thái giao hàng
     */
    public function checkDeliveryStatus(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:50'
        ]);

        $trackingNumber = $request->input('tracking_number');

        try {
            $result = $this->uspsService->checkDeliveryStatus($trackingNumber);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Unknown error'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('USPS Delivery Status Error', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track với cache
     */
    public function trackWithCache(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:50',
            'cache_minutes' => 'integer|min:1|max:1440' // Tối đa 24 giờ
        ]);

        $trackingNumber = $request->input('tracking_number');
        $cacheMinutes = $request->input('cache_minutes', 30);

        try {
            $result = $this->uspsService->trackWithCache($trackingNumber, $cacheMinutes);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'cached' => true
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Unknown error'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('USPS Cached Tracking Error', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa cache cho tracking number
     */
    public function clearCache(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:50'
        ]);

        $trackingNumber = $request->input('tracking_number');

        try {
            $result = $this->uspsService->clearTrackingCache($trackingNumber);

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'cleared' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('USPS Clear Cache Error', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị form tracking
     */
    public function showTrackingForm()
    {
        return view('tracking.usps-form');
    }
}
