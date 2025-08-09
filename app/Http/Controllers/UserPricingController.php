<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Models\ShippingOverride;
use App\Models\User;
use App\Services\UserPricingImportService;
use Illuminate\Support\Facades\Auth;

class UserPricingController extends Controller
{
    /**
     * Import user pricing từ file Excel
     */
    public function import(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        try {
            $file = $request->file('excel_file');

            if (!$file) {
                return redirect()->back()->with('error', 'Vui lòng chọn file Excel');
            }

            $service = new UserPricingImportService();
            $results = $service->importFromFile($file);

            $message = "Import hoàn thành! ";
            $message .= "Tổng dòng: {$results['total_rows']}, ";
            $message .= "Thành công: {$results['success_count']}, ";
            $message .= "Lỗi: {$results['error_count']}";

            if ($results['error_count'] > 0) {
                $message .= "\n\nChi tiết lỗi:\n";
                foreach ($results['errors'] as $error) {
                    $message .= "Dòng {$error['row']}: {$error['message']}\n";
                }
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xử lý file Excel User Pricing: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi nhập dữ liệu: ' . $e->getMessage());
        }
    }

    /**
     * Export template Excel cho User Pricing
     */
    public function exportTemplate()
    {
        $service = new UserPricingImportService();
        $filepath = $service->exportTemplate();

        return response()->download($filepath, 'user_pricing_template.xlsx')->deleteFileAfterSend();
    }

    /**
     * Hiển thị form import
     */
    public function showImportForm()
    {
        return view('admin.user-pricing.import');
    }

    /**
     * Hiển thị danh sách user pricing
     */
    public function index()
    {
        $userPricings = ShippingOverride::with(['shippingPrice.variant.product'])
            ->whereNotNull('user_ids')
            ->where('user_ids', '!=', '[]')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $users = User::select('id', 'first_name', 'last_name', 'email')->orderBy('first_name')->get();
        $variants = ProductVariant::select('id', 'sku')->orderBy('sku')->get();

        return view('admin.user-pricing.index', compact('userPricings', 'users', 'variants'));
    }

    /**
     * Xóa user pricing
     */
    public function destroy($id)
    {
        try {
            $pricing = ShippingOverride::findOrFail($id);

            // Log thông tin trước khi xóa để audit
            Log::info('User pricing đã được xóa', [
                'id' => $pricing->id,
                'user_ids' => $pricing->user_ids,
                'shipping_price_id' => $pricing->shipping_price_id,
                'override_price' => $pricing->override_price,
                'currency' => $pricing->currency,
                'deleted_by' => Auth::check() ? Auth::user()->id : 'system'
            ]);

            $pricing->delete();

            return response()->json([
                'success' => true,
                'message' => 'User pricing đã được xóa thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa user pricing: ' . $e->getMessage(), [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa user pricing: ' . $e->getMessage()
            ], 500);
        }
    }
}
