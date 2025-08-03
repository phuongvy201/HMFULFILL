<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserSpecificPricingImportService;
use App\Services\UserSpecificPricingService;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserSpecificPricingImportController extends Controller
{
    /**
     * Hiển thị trang import
     */
    public function index()
    {
        $users = User::where('role', 'customer')->get();
        $variants = ProductVariant::with('product')->get();
        $methods = ShippingPrice::$validMethods;

        return view('admin.user-specific-pricing.import', compact('users', 'variants', 'methods'));
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        $filename = UserSpecificPricingImportService::generateTemplate();
        $path = storage_path('app/public/' . $filename);

        return response()->download($path, 'user_specific_pricing_template.xlsx')->deleteFileAfterSend();
    }

    /**
     * Import từ CSV/Excel
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240' // 10MB max, hỗ trợ CSV và Excel
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');

        try {
            // Parse file dựa trên extension
            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, ['xlsx', 'xls'])) {
                // Parse Excel file
                $data = UserSpecificPricingImportService::parseExcelFile($file);
            } else {
                // Parse CSV file
                $data = UserSpecificPricingImportService::parseCsvFile($file);
            }

            // Validate dữ liệu
            $validationErrors = UserSpecificPricingImportService::validateImportData($data);
            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data validation failed',
                    'errors' => $validationErrors
                ], 422);
            }

            // Import dữ liệu
            $results = UserSpecificPricingImportService::importFromData($data);

            return response()->json([
                'success' => true,
                'message' => "Import completed. Success: {$results['success']}, Failed: {$results['failed']}",
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export giá riêng của user
     */
    public function exportUserPrices(int $userId)
    {
        $user = User::findOrFail($userId);
        $filename = UserSpecificPricingImportService::exportUserPrices($userId);
        $path = storage_path('app/public/' . $filename);

        return response()->download($path, "user_prices_{$user->email}.xlsx")->deleteFileAfterSend();
    }

    /**
     * Export tất cả giá riêng
     */
    public function exportAllPrices()
    {
        $filename = UserSpecificPricingImportService::exportAllPrices();
        $path = storage_path('app/public/' . $filename);

        return response()->download($path, 'all_user_specific_prices.xlsx')->deleteFileAfterSend();
    }

    /**
     * Import từ form (không cần Excel)
     */
    public function importFromForm(Request $request): JsonResponse
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
     * Import hàng loạt từ JSON
     */
    public function importBatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prices' => 'required|array',
            'prices.*.user_id' => 'required|exists:users,id',
            'prices.*.variant_id' => 'required|exists:product_variants,id',
            'prices.*.method' => 'required|in:' . implode(',', ShippingPrice::$validMethods),
            'prices.*.price' => 'required|numeric|min:0',
            'prices.*.currency' => 'required|in:USD,VND,GBP'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($request->prices as $index => $priceData) {
            try {
                $shippingPrice = UserSpecificPricingService::setUserPrice(
                    $priceData['user_id'],
                    $priceData['variant_id'],
                    $priceData['method'],
                    $priceData['price'],
                    $priceData['currency']
                );

                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'data' => $priceData
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Batch import completed. Success: {$results['success']}, Failed: {$results['failed']}",
            'data' => $results
        ]);
    }

    /**
     * Preview file CSV/Excel trước khi import
     */
    public function preview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');

        try {
            // Parse file dựa trên extension
            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, ['xlsx', 'xls'])) {
                // Parse Excel file
                $data = UserSpecificPricingImportService::parseExcelFile($file);
            } else {
                // Parse CSV file
                $data = UserSpecificPricingImportService::parseCsvFile($file);
            }

            // Validate dữ liệu
            $validationErrors = UserSpecificPricingImportService::validateImportData($data);
            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data validation failed',
                    'errors' => $validationErrors
                ], 422);
            }

            // Preview dữ liệu (tối đa 10 dòng đầu)
            $previewData = UserSpecificPricingImportService::previewData($data, 10);

            return response()->json([
                'success' => true,
                'message' => 'File preview generated successfully',
                'data' => $previewData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách user và variant để hỗ trợ import
     */
    public function getImportData(): JsonResponse
    {
        $users = User::where('role', 'customer')
            ->select('id', 'email', 'first_name', 'last_name')
            ->get();

        $variants = ProductVariant::with('product:id,name')
            ->select('id', 'sku', 'product_id')
            ->get();

        $methods = ShippingPrice::$validMethods;

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users,
                'variants' => $variants,
                'methods' => $methods
            ]
        ]);
    }
}
