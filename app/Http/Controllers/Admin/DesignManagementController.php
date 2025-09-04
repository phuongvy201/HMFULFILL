<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\DesignTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DesignManagementController extends Controller
{
    /**
     * Hiển thị trang tổng hợp design tasks
     */
    public function index(Request $request)
    {
        $query = DesignTask::with(['customer', 'designer'])
            ->orderBy('created_at', 'desc');

        // Filter theo status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter theo designer
        if ($request->filled('designer_id') && $request->designer_id !== 'all') {
            $query->where('designer_id', $request->designer_id);
        }

        // Filter theo customer
        if ($request->filled('customer_id') && $request->customer_id !== 'all') {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter theo date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter theo price range
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        $tasks = $query->paginate(20);

        // Lấy danh sách designers và customers cho filter
        $designers = User::where('role', 'designer')->get();
        $customers = User::where('role', 'customer')->get();

        // Thống kê tổng quan
        $stats = [
            'total' => DesignTask::count(),
            'pending' => DesignTask::where('status', 'pending')->count(),
            'joined' => DesignTask::where('status', 'joined')->count(),
            'completed' => DesignTask::where('status', 'completed')->count(),
            'approved' => DesignTask::where('status', 'approved')->count(),
            'revision' => DesignTask::where('status', 'revision')->count(),
            'cancelled' => DesignTask::where('status', 'cancelled')->count(),
        ];

        // Thống kê theo tháng
        $monthlyStats = DesignTask::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                SUM(price) as total_revenue
            ')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        // Top designers
        $topDesigners = DesignTask::selectRaw('
                designer_id,
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_tasks,
                SUM(price) as total_earnings
            ')
            ->whereNotNull('designer_id')
            ->where('status', '!=', 'cancelled')
            ->groupBy('designer_id')
            ->with('designer')
            ->orderBy('total_tasks', 'desc')
            ->limit(10)
            ->get();

        return view('admin.design.index', compact(
            'tasks',
            'designers',
            'customers',
            'stats',
            'monthlyStats',
            'topDesigners'
        ));
    }

    /**
     * Xuất CSV cho design tasks
     */
    public function exportCSV(Request $request)
    {
        $query = DesignTask::with(['customer', 'designer']);

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('designer_id') && $request->designer_id !== 'all') {
            $query->where('designer_id', $request->designer_id);
        }
        if ($request->filled('customer_id') && $request->customer_id !== 'all') {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        $tasks = $query->get();

        $filename = 'design_tasks_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($tasks) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Tiêu đề',
                'Mô tả',
                'Trạng thái',
                'Giá ($)',
                'Số mặt',
                'Khách hàng',
                'Designer',
                'Sản phẩm',
                'Ngày tạo',
                'Ngày cập nhật',
                'Ghi chú',
                'File mockup',
                'File design'
            ]);

            foreach ($tasks as $task) {
                fputcsv($file, [
                    $task->id,
                    $task->title,
                    $task->description,
                    $task->getStatusDisplayName(),
                    number_format($task->price, 2),
                    $task->sides_count,
                    $task->customer ? $task->customer->first_name . ' ' . $task->customer->last_name : 'N/A',
                    $task->designer ? $task->designer->first_name . ' ' . $task->designer->last_name : 'N/A',
                    'N/A', // Product relationship not available
                    $task->created_at->format('d/m/Y H:i'),
                    $task->updated_at->format('d/m/Y H:i'),
                    $task->notes,
                    $task->mockup_file ? 'Có' : 'Không',
                    $task->design_file ? 'Có' : 'Không'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Hiển thị chi tiết task
     */
    public function show($id)
    {
        $task = DesignTask::with(['customer', 'designer', 'comments.user'])
            ->findOrFail($id);

        return view('admin.design.show', compact('task'));
    }

    /**
     * Cập nhật trạng thái task
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,joined,completed,approved,revision,cancelled'
        ]);

        $task = DesignTask::findOrFail($id);
        $oldStatus = $task->status;
        $task->status = $request->status;
        $task->save();

        // Log activity
        activity()
            ->performedOn($task)
            ->log("Admin đã thay đổi trạng thái task từ '{$oldStatus}' thành '{$request->status}'");

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công',
            'new_status' => $task->getStatusDisplayName()
        ]);
    }

    /**
     * Xóa task
     */
    public function destroy($id)
    {
        $task = DesignTask::findOrFail($id);
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa task thành công'
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,change_status',
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:design_tasks,id'
        ]);

        $taskIds = $request->task_ids;

        switch ($request->action) {
            case 'delete':
                DesignTask::whereIn('id', $taskIds)->delete();
                $message = 'Đã xóa ' . count($taskIds) . ' tasks';
                break;

            case 'change_status':
                $request->validate([
                    'new_status' => 'required|in:pending,joined,completed,approved,revision,cancelled'
                ]);

                DesignTask::whereIn('id', $taskIds)->update([
                    'status' => $request->new_status
                ]);
                $message = 'Đã cập nhật trạng thái ' . count($taskIds) . ' tasks';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Dashboard statistics
     */
    public function dashboard()
    {
        // Thống kê tổng quan
        $stats = [
            'total_tasks' => DesignTask::count(),
            'pending_tasks' => DesignTask::where('status', 'pending')->count(),
            'active_tasks' => DesignTask::whereIn('status', ['joined', 'completed'])->count(),
            'completed_tasks' => DesignTask::where('status', 'completed')->count(),
            'approved_tasks' => DesignTask::where('status', 'approved')->count(),
            'total_revenue' => DesignTask::where('status', 'approved')->sum('price'),
        ];

        // Thống kê theo tháng (12 tháng gần nhất)
        $monthlyStats = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthData = DesignTask::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->get();

            $monthlyStats->push([
                'month' => $date->format('M Y'),
                'total' => $monthData->count(),
                'completed' => $monthData->where('status', 'completed')->count(),
                'approved' => $monthData->where('status', 'approved')->count(),
                'revenue' => $monthData->where('status', 'approved')->sum('price'),
            ]);
        }

        // Top designers
        $topDesigners = DesignTask::selectRaw('
                designer_id,
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_tasks,
                SUM(CASE WHEN status = "approved" THEN price ELSE 0 END) as total_earnings
            ')
            ->whereNotNull('designer_id')
            ->groupBy('designer_id')
            ->with('designer')
            ->orderBy('total_tasks', 'desc')
            ->limit(5)
            ->get();

        // Recent tasks
        $recentTasks = DesignTask::with(['customer', 'designer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.design.dashboard', compact(
            'stats',
            'monthlyStats',
            'topDesigners',
            'recentTasks'
        ));
    }
}
