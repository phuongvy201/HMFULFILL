<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ChunkUploadController extends Controller
{
    /**
     * Xử lý upload chunk
     */
    public function uploadChunk(Request $request)
    {
        Log::info('Chunk upload request received', [
            'upload_id' => $request->upload_id,
            'chunk' => $request->chunk,
            'chunks' => $request->chunks,
            'filename' => $request->filename,
            'total_size' => $request->total_size,
            'has_file' => $request->hasFile('file'),
            'file_size' => $request->hasFile('file') ? $request->file('file')->getSize() : 0
        ]);

        $request->validate([
            'file' => 'required|file',
            'chunk' => 'required|integer|min:0',
            'chunks' => 'required|integer|min:1',
            'filename' => 'required|string',
            'upload_id' => 'required|string',
            'total_size' => 'required|integer|min:1',
        ]);

        try {
            $uploadId = $request->upload_id;
            $chunk = $request->chunk;
            $chunks = $request->chunks;
            $filename = $request->filename;
            $totalSize = $request->total_size;

            Log::info('Processing chunk upload', [
                'upload_id' => $uploadId,
                'chunk' => $chunk,
                'chunks' => $chunks,
                'filename' => $filename,
                'total_size' => $totalSize
            ]);

            // Kiểm tra kích thước file
            if ($totalSize > 100 * 1024 * 1024) { // 100MB limit
                Log::warning('File too large', ['total_size' => $totalSize]);
                return response()->json([
                    'success' => false,
                    'error' => 'File quá lớn. Kích thước tối đa là 100MB.'
                ], 400);
            }

            // Tạo thư mục tạm cho upload
            $tempDir = "temp_uploads/{$uploadId}";
            $chunkPath = "{$tempDir}/chunk_{$chunk}";

            // Tạo thư mục nếu chưa tồn tại
            if (!Storage::disk('local')->exists($tempDir)) {
                Storage::disk('local')->makeDirectory($tempDir);
                Log::info('Created temp directory', ['temp_dir' => $tempDir]);
            }

            // Lưu chunk
            $file = $request->file('file');
            $chunkContent = file_get_contents($file->getRealPath());
            Storage::disk('local')->put($chunkPath, $chunkContent);

            Log::info('Chunk saved', [
                'chunk_path' => $chunkPath,
                'chunk_size' => strlen($chunkContent)
            ]);

            // Lưu metadata
            $metadata = [
                'filename' => $filename,
                'total_size' => $totalSize,
                'chunks' => $chunks,
                'uploaded_chunks' => [],
                'created_at' => now()->toISOString(),
            ];

            // Cập nhật danh sách chunks đã upload
            $existingMetadata = Cache::get("upload_metadata_{$uploadId}", []);
            Log::info('Existing metadata', [
                'upload_id' => $uploadId,
                'existing_metadata' => $existingMetadata
            ]);

            $existingMetadata['uploaded_chunks'][] = $chunk;
            $existingMetadata['uploaded_chunks'] = array_unique($existingMetadata['uploaded_chunks']);

            Log::info('Updated metadata', [
                'upload_id' => $uploadId,
                'updated_chunks' => $existingMetadata['uploaded_chunks']
            ]);

            // Merge với metadata mới
            $metadata = array_merge($metadata, $existingMetadata);
            Cache::put("upload_metadata_{$uploadId}", $metadata, 3600); // 1 giờ

            Log::info('Final metadata saved', [
                'upload_id' => $uploadId,
                'final_metadata' => $metadata
            ]);

            // Kiểm tra xem đã upload hết chunks chưa
            Log::info('Checking chunks completion', [
                'uploaded_chunks_count' => count($metadata['uploaded_chunks']),
                'total_chunks' => $chunks,
                'uploaded_chunks' => $metadata['uploaded_chunks'],
                'uploaded_type' => gettype(count($metadata['uploaded_chunks'])),
                'total_type' => gettype($chunks)
            ]);

            if (count($metadata['uploaded_chunks']) == $chunks) {
                Log::info('All chunks uploaded, merging files');

                // Ghép các chunks lại thành file hoàn chỉnh
                $finalPath = $this->mergeChunks($uploadId, $filename, $chunks);

                Log::info('File merged successfully', ['final_path' => $finalPath]);

                return response()->json([
                    'success' => true,
                    'completed' => true,
                    'file_path' => $finalPath,
                    'message' => 'Upload hoàn tất!'
                ]);
            } else {
                Log::info('Not all chunks uploaded yet', [
                    'uploaded' => count($metadata['uploaded_chunks']),
                    'total' => $chunks,
                    'remaining' => $chunks - count($metadata['uploaded_chunks'])
                ]);
            }

            return response()->json([
                'success' => true,
                'completed' => false,
                'uploaded_chunks' => $metadata['uploaded_chunks'],
                'message' => "Đã upload chunk {$chunk}/{$chunks}"
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi upload chunk: ' . $e->getMessage(), [
                'upload_id' => $request->upload_id,
                'chunk' => $request->chunk,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi upload chunk. Vui lòng thử lại.'
            ], 500);
        }
    }

    /**
     * Ghép các chunks thành file hoàn chỉnh
     */
    private function mergeChunks($uploadId, $filename, $chunks)
    {
        Log::info('Starting merge chunks', [
            'upload_id' => $uploadId,
            'filename' => $filename,
            'chunks' => $chunks
        ]);

        $tempDir = "temp_uploads/{$uploadId}";
        $finalPath = "designs/mockups/" . time() . '_' . Str::random(10) . '_' . $filename;

        // Tạo file hoàn chỉnh trên local trước
        $tempFinalPath = storage_path("app/temp_final_{$uploadId}_{$filename}");

        Log::info('Creating temp file', ['temp_path' => $tempFinalPath]);

        // Tạo thư mục app nếu chưa tồn tại
        $appDir = storage_path('app');
        if (!is_dir($appDir)) {
            mkdir($appDir, 0755, true);
            Log::info('Created app directory', ['app_dir' => $appDir]);
        }

        $handle = fopen($tempFinalPath, 'wb');

        if (!$handle) {
            Log::error('Cannot create temp file', ['temp_path' => $tempFinalPath]);
            throw new \Exception('Không thể tạo file đích');
        }

        try {
            // Ghép từng chunk
            for ($i = 0; $i < $chunks; $i++) {
                $chunkPath = "{$tempDir}/chunk_{$i}";

                Log::info('Processing chunk', [
                    'chunk_index' => $i,
                    'chunk_path' => $chunkPath,
                    'exists' => Storage::disk('local')->exists($chunkPath)
                ]);

                if (Storage::disk('local')->exists($chunkPath)) {
                    $chunkContent = Storage::disk('local')->get($chunkPath);
                    $bytesWritten = fwrite($handle, $chunkContent);

                    Log::info('Chunk merged', [
                        'chunk_index' => $i,
                        'chunk_size' => strlen($chunkContent),
                        'bytes_written' => $bytesWritten
                    ]);

                    // Xóa chunk sau khi ghép
                    Storage::disk('local')->delete($chunkPath);
                } else {
                    Log::error('Chunk not found', [
                        'chunk_index' => $i,
                        'chunk_path' => $chunkPath
                    ]);
                }
            }

            fclose($handle);

            Log::info('Temp file created', [
                'temp_path' => $tempFinalPath,
                'file_size' => filesize($tempFinalPath)
            ]);

            // Upload file hoàn chỉnh lên S3
            $s3FileName = time() . '_' . Str::random(10) . '_' . $filename;
            Log::info('Uploading to S3', [
                's3_file_name' => $s3FileName,
                'temp_path' => $tempFinalPath
            ]);

            // Tạo thư mục trên S3 nếu chưa tồn tại
            if (!Storage::disk('s3')->exists('designs/mockups')) {
                Storage::disk('s3')->makeDirectory('designs/mockups');
                Log::info('Created S3 directory', ['s3_dir' => 'designs/mockups']);
            }

            $s3Path = Storage::disk('s3')->putFileAs(
                'designs/mockups',
                $tempFinalPath,
                $s3FileName
            );

            Log::info('S3 upload completed', ['s3_path' => $s3Path]);

            // Xóa file tạm local
            unlink($tempFinalPath);

            // Xóa thư mục tạm
            Storage::disk('local')->deleteDirectory($tempDir);

            // Xóa metadata
            Cache::forget("upload_metadata_{$uploadId}");

            return $s3Path;
        } catch (\Exception $e) {
            if ($handle) {
                fclose($handle);
            }
            // Xóa file tạm nếu có lỗi
            if (file_exists($tempFinalPath)) {
                unlink($tempFinalPath);
            }
            throw $e;
        }
    }

    /**
     * Kiểm tra trạng thái upload
     */
    public function checkUploadStatus($uploadId)
    {
        $metadata = Cache::get("upload_metadata_{$uploadId}");

        if (!$metadata) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin upload'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'metadata' => $metadata,
            'progress' => count($metadata['uploaded_chunks']) / $metadata['chunks'] * 100
        ]);
    }

    /**
     * Hủy upload
     */
    public function cancelUpload(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|string'
        ]);

        $uploadId = $request->upload_id;
        $tempDir = "temp_uploads/{$uploadId}";

        // Xóa thư mục tạm
        Storage::disk('local')->deleteDirectory($tempDir);

        // Xóa metadata
        Cache::forget("upload_metadata_{$uploadId}");

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy upload'
        ]);
    }
}
