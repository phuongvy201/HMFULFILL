<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\S3MultipartUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class S3ManagementController extends Controller
{
    protected $uploadService;

    public function __construct()
    {
        $this->uploadService = new S3MultipartUploadService();
    }

    /**
     * Hiển thị trang quản lý S3
     */
    public function index()
    {
        try {
            // Lấy danh sách incomplete uploads
            $incompleteUploads = $this->uploadService->listMultipartUploads();

            // Tính toán thống kê
            $stats = [
                'total_incomplete' => count($incompleteUploads),
                'oldest_upload' => null,
                'total_size_estimate' => 0
            ];

            if (!empty($incompleteUploads)) {
                // Tìm upload cũ nhất
                $oldestTime = null;
                foreach ($incompleteUploads as $upload) {
                    $initiatedTime = new \DateTime($upload['Initiated']);
                    if (!$oldestTime || $initiatedTime < $oldestTime) {
                        $oldestTime = $initiatedTime;
                        $stats['oldest_upload'] = $upload;
                    }
                }
            }

            return view('admin.s3-management.index', compact('incompleteUploads', 'stats'));
        } catch (\Exception $e) {
            Log::error('Failed to load S3 management page', [
                'error' => $e->getMessage()
            ]);

            return view('admin.s3-management.index', [
                'incompleteUploads' => [],
                'stats' => ['total_incomplete' => 0, 'oldest_upload' => null, 'total_size_estimate' => 0],
                'error' => 'Không thể tải thông tin S3: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cleanup incomplete uploads
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'hours' => 'nullable|integer|min:1|max:168', // max 1 week
            'confirm' => 'required|accepted'
        ]);

        try {
            $hours = $request->input('hours', 24);
            $cleanedCount = $this->uploadService->cleanupIncompleteUploads($hours);

            Log::info('Manual S3 cleanup performed', [
                'hours' => $hours,
                'cleaned_count' => $cleanedCount,
                'admin_user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Đã cleanup {$cleanedCount} incomplete uploads thành công!",
                'cleaned_count' => $cleanedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Manual S3 cleanup failed', [
                'error' => $e->getMessage(),
                'admin_user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cleanup thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get S3 statistics
     */
    public function getStats()
    {
        try {
            $incompleteUploads = $this->uploadService->listMultipartUploads();
            $now = new \DateTime();

            $stats = [
                'total_incomplete' => count($incompleteUploads),
                'by_age' => [
                    'less_than_1_hour' => 0,
                    'less_than_24_hours' => 0,
                    'less_than_7_days' => 0,
                    'older_than_7_days' => 0
                ],
                'by_prefix' => []
            ];

            foreach ($incompleteUploads as $upload) {
                $initiatedTime = new \DateTime($upload['Initiated']);
                $ageHours = $now->diff($initiatedTime)->h + ($now->diff($initiatedTime)->days * 24);

                // Phân loại theo tuổi
                if ($ageHours < 1) {
                    $stats['by_age']['less_than_1_hour']++;
                } elseif ($ageHours < 24) {
                    $stats['by_age']['less_than_24_hours']++;
                } elseif ($ageHours < 168) { // 7 days
                    $stats['by_age']['less_than_7_days']++;
                } else {
                    $stats['by_age']['older_than_7_days']++;
                }

                // Phân loại theo prefix
                $prefix = explode('/', $upload['Key'])[0] ?? 'unknown';
                $stats['by_prefix'][$prefix] = ($stats['by_prefix'][$prefix] ?? 0) + 1;
            }

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Abort specific multipart upload
     */
    public function abortUpload(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|string',
            'key' => 'required|string',
            'confirm' => 'required|accepted'
        ]);

        try {
            $this->uploadService->getS3Client()->abortMultipartUpload([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $request->key,
                'UploadId' => $request->upload_id
            ]);

            Log::info('Manual abort multipart upload', [
                'upload_id' => $request->upload_id,
                'key' => $request->key,
                'admin_user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Upload đã được abort thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to abort multipart upload', [
                'upload_id' => $request->upload_id,
                'key' => $request->key,
                'error' => $e->getMessage(),
                'admin_user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể abort upload: ' . $e->getMessage()
            ], 500);
        }
    }
}
