<?php

namespace App\Http\Controllers;

use App\Models\DesignTask;
use App\Models\DesignRevision;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{
    /**
     * Hiển thị trang tạo yêu cầu thiết kế
     */
    public function create()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $balance = $wallet ? $wallet->getTotalBalance() : 0;

        return view('customer.design.create', compact('balance'));
    }

    /**
     * Lưu yêu cầu thiết kế mới
     */
    public function store(Request $request)
    {
        // Debug logging
        Log::info('Design task creation request', [
            'sides_count' => $request->sides_count,
            'files_count' => $request->hasFile('mockup_files') ? count($request->file('mockup_files')) : 0,
            'files' => $request->hasFile('mockup_files') ? array_map(function ($file) {
                return [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'valid' => $file->isValid()
                ];
            }, $request->file('mockup_files')) : []
        ]);

        $user = Auth::user();
        $sidesCount = (int)$request->sides_count; // Chuyển thành integer
        $price = DesignTask::calculatePrice($sidesCount);

        // Validation sau khi đã cast sides_count
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sides_count' => 'required|integer|min:1|max:10',
            'mockup_files' => 'required|array|size:' . $sidesCount,
            'mockup_files.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:51200', // 50MB max per file
        ], [
            'mockup_files.size' => 'Số lượng files phải bằng số mặt đã chọn.',
            'mockup_files.*.required' => 'Vui lòng upload đầy đủ files cho tất cả các mặt.',
            'mockup_files.*.file' => 'File không hợp lệ.',
            'mockup_files.*.mimes' => 'Chỉ chấp nhận file JPG, PNG, PDF.',
            'mockup_files.*.max' => 'File không được vượt quá 50MB.'
        ]);

        // Kiểm tra số dư
        $wallet = $user->wallet;
        if (!$wallet || !$wallet->hasEnoughBalance($price)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Số dư không đủ. Vui lòng nạp tiền trước.']);
        }

        DB::beginTransaction();
        try {
            // Upload multiple mockup files
            $mockupFiles = $request->file('mockup_files');
            $mockupPaths = [];

            if ($mockupFiles) {
                foreach ($mockupFiles as $index => $mockupFile) {
                    if ($mockupFile && $mockupFile->isValid()) {
                        $originalName = $mockupFile->getClientOriginalName();
                        // Chuẩn hóa tên file: thay dấu cách bằng dấu + và encode URL
                        $normalizedName = str_replace(' ', '+', $originalName);
                        $normalizedName = urlencode($normalizedName);
                        $mockupFileName = time() . '_' . ($index + 1) . '_' . $normalizedName;
                        $mockupPath = $mockupFile->storeAs('designs/mockups', $mockupFileName, 's3');
                        $mockupPaths[] = $mockupPath;
                    } else {
                        throw new \Exception('File upload không hợp lệ hoặc bị lỗi.');
                    }
                }
            }

            // Debug logging cho upload
            Log::info('Files upload result', [
                'expected_count' => $sidesCount,
                'expected_count_type' => gettype($sidesCount),
                'uploaded_count' => count($mockupPaths),
                'uploaded_count_type' => gettype(count($mockupPaths)),
                'uploaded_files' => $mockupPaths
            ]);

            // Kiểm tra số lượng files đã upload
            if (count($mockupPaths) !== $sidesCount) {
                throw new \Exception('Số lượng files upload không khớp với số mặt đã chọn.');
            }

            // Lưu tất cả paths dưới dạng JSON
            $mockupFilesJson = json_encode($mockupPaths);

            // Tạo design task
            $designTask = DesignTask::create([
                'customer_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'sides_count' => $sidesCount,
                'price' => $price,
                'status' => DesignTask::STATUS_PENDING,
                'mockup_file' => $mockupFilesJson, // Lưu JSON array của các file paths
            ]);

            // Trừ tiền từ ví
            $wallet->withdraw($price);

            // Tạo transaction record
            Transaction::create([
                'user_id' => $user->id,
                'transaction_code' => 'DEDUCT_' . strtoupper(uniqid()), // BẮT BUỘC
                'type' => Transaction::TYPE_DEDUCT,
                'method' => 'Bank VN', // hoặc phương thức phù hợp
                'amount' => $price,
                'status' => Transaction::STATUS_APPROVED,
                'note' => "Payment for design task: {$designTask->title}",
                // Nếu có các trường reference_id/reference_type thì thêm vào nếu cần
            ]);

            DB::commit();

            return redirect()->route('customer.design.my-tasks')
                ->with('success', 'Yêu cầu thiết kế đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo design task: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'sides_count' => $sidesCount,
                'files_count' => $request->hasFile('mockup_files') ? count($request->file('mockup_files')) : 0,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Trả về thông báo lỗi cụ thể
            $errorMessage = 'Có lỗi xảy ra. Vui lòng thử lại.';

            if (str_contains($e->getMessage(), 'Số lượng files upload không khớp')) {
                $errorMessage = 'Số lượng files upload không khớp với số mặt đã chọn. Vui lòng kiểm tra lại.';
            } elseif (str_contains($e->getMessage(), 'File upload không hợp lệ')) {
                $errorMessage = 'Có file upload không hợp lệ. Vui lòng kiểm tra định dạng và kích thước file.';
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $errorMessage]);
        }
    }

    /**
     * Hiển thị danh sách design tasks cho designer
     */
    public function designerTasks()
    {
        $user = Auth::user();

        if ($user->role !== 'design') {
            return redirect()->back()->withErrors(['error' => 'Bạn không có quyền truy cập.']);
        }

        // Lấy các task đang pending (chưa có designer)
        $pendingTasks = DesignTask::with('customer')
            ->where('status', DesignTask::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        // Lấy các task mà designer này đã nhận
        $myTasks = DesignTask::with('customer')
            ->where('designer_id', $user->id)
            ->whereIn('status', [DesignTask::STATUS_JOINED, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_APPROVED, DesignTask::STATUS_REVISION])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('designer.tasks.index', compact('pendingTasks', 'myTasks'));
    }

    /**
     * Designer nhận task
     */
    public function joinTask($taskId)
    {
        $user = Auth::user();

        if ($user->role !== 'design') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập.']);
        }

        DB::beginTransaction();
        try {
            // Sử dụng lockForUpdate để tránh race condition
            $task = DesignTask::where('id', $taskId)
                ->where('status', DesignTask::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (!$task) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Task đã được người khác nhận trước.']);
            }

            // Gán designer và cập nhật status
            $task->update([
                'designer_id' => $user->id,
                'status' => DesignTask::STATUS_JOINED
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Bạn đã nhận task thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi join task: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
        }
    }

    /**
     * Designer upload file thiết kế hoàn chỉnh
     */
    public function submitDesign(Request $request, $taskId)
    {
        $user = Auth::user();

        if ($user->role !== 'design') {
            return redirect()->back()->withErrors(['error' => 'Bạn không có quyền truy cập.']);
        }

        $task = DesignTask::where('id', $taskId)
            ->where('designer_id', $user->id)
            ->whereIn('status', [DesignTask::STATUS_JOINED, DesignTask::STATUS_REVISION])
            ->first();

        if (!$task) {
            return redirect()->back()->withErrors(['error' => 'Task không tồn tại hoặc không thể submit.']);
        }

        // Validation dựa trên số mặt
        $validationRules = [
            'notes' => 'nullable|string'
        ];

        if ($task->sides_count > 1) {
            // Nhiều mặt - yêu cầu nhiều files
            $validationRules['design_files'] = 'required|array|size:' . $task->sides_count;
            $validationRules['design_files.*'] = 'required|file|mimes:jpg,jpeg,png,pdf,ai,psd|max:51200';
        } else {
            // Một mặt - yêu cầu 1 file
            $validationRules['design_file'] = 'required|file|mimes:jpg,jpeg,png,pdf,ai,psd|max:51200';
        }

        $request->validate($validationRules, [
            'design_files.size' => 'Số lượng files phải bằng số mặt đã chọn.',
            'design_files.*.required' => 'Vui lòng upload đầy đủ files cho tất cả các mặt.',
            'design_files.*.file' => 'File không hợp lệ.',
            'design_files.*.mimes' => 'Chỉ chấp nhận file JPG, PNG, PDF, AI, PSD.',
            'design_files.*.max' => 'File không được vượt quá 50MB.',
            'design_file.required' => 'Vui lòng upload file thiết kế.',
            'design_file.file' => 'File không hợp lệ.',
            'design_file.mimes' => 'Chỉ chấp nhận file JPG, PNG, PDF, AI, PSD.',
            'design_file.max' => 'File không được vượt quá 50MB.'
        ]);

        try {
            $designPaths = [];

            if ($task->sides_count > 1) {
                // Upload nhiều files cho nhiều mặt
                $designFiles = $request->file('design_files');

                foreach ($designFiles as $index => $designFile) {
                    if ($designFile && $designFile->isValid()) {
                        $originalName = $designFile->getClientOriginalName();
                        $normalizedName = str_replace(' ', '+', $originalName);
                        $normalizedName = urlencode($normalizedName);
                        $designFileName = time() . '_' . ($index + 1) . '_' . $normalizedName;
                        $designPath = $designFile->storeAs('designs/completed', $designFileName, 's3');
                        $designPaths[] = $designPath;
                    } else {
                        throw new \Exception('File upload không hợp lệ hoặc bị lỗi.');
                    }
                }

                // Kiểm tra số lượng files đã upload
                if (count($designPaths) !== $task->sides_count) {
                    throw new \Exception('Số lượng files upload không khớp với số mặt đã chọn.');
                }
            } else {
                // Upload 1 file cho 1 mặt
                $designFile = $request->file('design_file');
                $originalName = $designFile->getClientOriginalName();
                $normalizedName = str_replace(' ', '+', $originalName);
                $normalizedName = urlencode($normalizedName);
                $designFileName = time() . '_' . $normalizedName;
                $designPath = $designFile->storeAs('designs/completed', $designFileName, 's3');
                $designPaths[] = $designPath;
            }

            // Lưu tất cả paths dưới dạng JSON
            $designFilesJson = json_encode($designPaths);

            // Xác định version number
            $latestRevision = $task->revisions()->latest()->first();
            $version = $latestRevision ? $latestRevision->version + 1 : 1;

            // Tạo revision mới
            $revision = DesignRevision::create([
                'design_task_id' => $task->id,
                'designer_id' => $user->id,
                'design_file' => $designFilesJson, // Lưu JSON array của các file paths
                'notes' => $request->notes,
                'revision_notes' => $task->status === DesignTask::STATUS_REVISION ? $task->revision_notes : null,
                'version' => $version,
                'status' => DesignRevision::STATUS_SUBMITTED,
                'submitted_at' => now()
            ]);

            // Cập nhật task
            $task->update([
                'status' => DesignTask::STATUS_COMPLETED,
                'completed_at' => now()
            ]);

            $message = $task->status === DesignTask::STATUS_REVISION
                ? 'Đã gửi thiết kế đã chỉnh sửa thành công!'
                : 'Đã gửi thiết kế thành công!';

            return redirect()->route('designer.tasks.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Lỗi khi submit design: ' . $e->getMessage(), [
                'task_id' => $taskId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra khi upload file. Vui lòng thử lại.']);
        }
    }

    /**
     * Khách hàng xem danh sách task của mình
     */
    public function myTasks()
    {
        $user = Auth::user();

        $tasks = DesignTask::with('designer')
            ->where('customer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('customer.design.my-tasks', compact('tasks'));
    }

    /**
     * Khách hàng xem chi tiết task
     */
    public function show($taskId)
    {
        $user = Auth::user();

        // Kiểm tra role để xác định view phù hợp
        if ($user->role === 'design') {
            // Designer view
            $task = DesignTask::with(['customer', 'designer'])
                ->where('id', $taskId)
                ->where(function ($query) use ($user) {
                    $query->where('customer_id', $user->id)
                        ->orWhere('designer_id', $user->id);
                })
                ->first();

            if (!$task) {
                return redirect()->back()->withErrors(['error' => 'Task không tồn tại.']);
            }

            return view('designer.tasks.show', compact('task'));
        } else {
            // Customer view
            $task = DesignTask::with(['customer', 'designer'])
                ->where('id', $taskId)
                ->where('customer_id', $user->id)
                ->first();

            if (!$task) {
                return redirect()->back()->withErrors(['error' => 'Task không tồn tại hoặc bạn không có quyền xem.']);
            }

            return view('customer.design.show', compact('task'));
        }
    }

    /**
     * Khách hàng phê duyệt hoặc yêu cầu chỉnh sửa
     */
    public function review(Request $request, $taskId)
    {
        $request->validate([
            'action' => 'required|in:approve,revision',
            'revision_notes' => 'required_if:action,revision|nullable|string'
        ]);

        $user = Auth::user();

        $task = DesignTask::where('id', $taskId)
            ->where('customer_id', $user->id)
            ->where('status', DesignTask::STATUS_COMPLETED)
            ->first();

        if (!$task) {
            return redirect()->back()->withErrors(['error' => 'Task không tồn tại hoặc không thể review.']);
        }

        try {
            $status = $request->action === 'approve' ? DesignTask::STATUS_APPROVED : DesignTask::STATUS_REVISION;

            $task->update([
                'status' => $status,
                'revision_notes' => $request->revision_notes
            ]);

            $message = $request->action === 'approve'
                ? 'Đã phê duyệt thiết kế thành công!'
                : 'Đã gửi yêu cầu chỉnh sửa!';

            return redirect()->route('customer.design.my-tasks')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Lỗi khi review design: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
        }
    }

    /**
     * Khách hàng hủy yêu cầu thiết kế và hoàn tiền
     */
    public function cancel(Request $request, $taskId)
    {
        $user = Auth::user();

        $task = DesignTask::where('id', $taskId)
            ->where('customer_id', $user->id)
            ->where('status', DesignTask::STATUS_PENDING)
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task không tồn tại hoặc không thể hủy.'
            ], 404);
        }

        if (!$task->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Task này không thể hủy. Chỉ có thể hủy task đang chờ designer nhận.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Tìm transaction trừ tiền ban đầu
            $transaction = Transaction::where('user_id', $user->id)
                ->where('type', Transaction::TYPE_DEDUCT)
                ->where('note', 'like', '%' . $task->title . '%')
                ->where('status', Transaction::STATUS_APPROVED)
                ->whereNull('refunded_at')
                ->first();

            if (!$transaction) {
                throw new \Exception('Không tìm thấy giao dịch trừ tiền ban đầu.');
            }

            // Hoàn tiền cho khách hàng
            $refundTransaction = $transaction->refund(
                $user->id,
                "Hoàn tiền do hủy yêu cầu thiết kế: {$task->title}"
            );

            if (!$refundTransaction) {
                throw new \Exception('Không thể xử lý hoàn tiền.');
            }

            // Cập nhật trạng thái task
            $task->update([
                'status' => DesignTask::STATUS_CANCELLED
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy yêu cầu thiết kế và hoàn tiền thành công!',
                'refund_amount' => number_format($task->price, 2)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi hủy design task: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị dashboard của designer
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Lấy thống kê cho designer
        $totalTasks = DesignTask::count();
        $myTasks = DesignTask::where('designer_id', $user->id)->count();
        $completedTasks = DesignTask::where('designer_id', $user->id)
            ->where('status', 'completed')
            ->count();
        $pendingTasks = DesignTask::where('status', 'pending')->count();

        // Lấy tasks gần đây
        $recentTasks = DesignTask::where('designer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Lấy danh sách tasks đã hoàn thành
        $completedTasksList = DesignTask::where('designer_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(6);

        return view('designer.dashboard', compact(
            'totalTasks',
            'myTasks',
            'completedTasks',
            'pendingTasks',
            'recentTasks',
            'completedTasksList'
        ));
    }
}
