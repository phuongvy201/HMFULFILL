<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DesignUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'design';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Lấy task ID từ route parameter
        $taskId = $this->route('taskId');
        $task = null;

        if ($taskId) {
            $task = \App\Models\DesignTask::find($taskId);
        }

        $maxFileSize = config('multipart-upload.file_types.design_files.max_file_size', 200 * 1024 * 1024);
        $allowedExtensions = config(
            'multipart-upload.file_types.design_files.allowed_extensions',
            ['jpg', 'jpeg', 'png', 'pdf', 'ai', 'psd']
        );

        $rules = [
            'notes' => 'nullable|string|max:1000'
        ];

        if ($task && $task->sides_count > 1) {
            // Nhiều mặt - yêu cầu nhiều files
            $rules['design_files'] = 'required|array|size:' . $task->sides_count;
            $rules['design_files.*'] = [
                'required',
                'file',
                'mimes:' . implode(',', $allowedExtensions),
                'max:' . ($maxFileSize / 1024), // Laravel expects KB
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $this->validateFileIntegrity($value, $fail);
                    }
                }
            ];
        } else {
            // Một mặt - yêu cầu 1 file
            $rules['design_file'] = [
                'required',
                'file',
                'mimes:' . implode(',', $allowedExtensions),
                'max:' . ($maxFileSize / 1024), // Laravel expects KB
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $this->validateFileIntegrity($value, $fail);
                    }
                }
            ];
        }

        return $rules;
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        $maxSizeMB = config('multipart-upload.file_types.design_files.max_file_size', 200 * 1024 * 1024) / (1024 * 1024);

        return [
            'design_files.size' => 'Số lượng files phải bằng số mặt đã chọn.',
            'design_files.*.required' => 'Vui lòng upload đầy đủ files cho tất cả các mặt.',
            'design_files.*.file' => 'File không hợp lệ.',
            'design_files.*.mimes' => 'Chỉ chấp nhận file: ' . implode(', ', config('multipart-upload.file_types.design_files.allowed_extensions', [])),
            'design_files.*.max' => "File không được vượt quá {$maxSizeMB}MB.",
            'design_file.required' => 'Vui lòng upload file thiết kế.',
            'design_file.file' => 'File không hợp lệ.',
            'design_file.mimes' => 'Chỉ chấp nhận file: ' . implode(', ', config('multipart-upload.file_types.design_files.allowed_extensions', [])),
            'design_file.max' => "File không được vượt quá {$maxSizeMB}MB.",
            'notes.max' => 'Ghi chú không được vượt quá 1000 ký tự.'
        ];
    }

    /**
     * Validate file integrity
     */
    protected function validateFileIntegrity($file, $fail)
    {
        // Kiểm tra file có bị corrupt không
        if ($file->getSize() === 0) {
            $fail('File rỗng hoặc bị lỗi.');
            return;
        }

        // Kiểm tra MIME type thực tế
        $realMimeType = mime_content_type($file->getRealPath());
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/postscript', // AI files
            'application/octet-stream', // PSD files
            'image/vnd.adobe.photoshop' // PSD files
        ];

        if (!in_array($realMimeType, $allowedMimeTypes)) {
            $fail('File type không được hỗ trợ.');
            return;
        }

        // Kiểm tra file signature (magic bytes) cho một số loại file
        $this->validateFileSignature($file, $fail);
    }

    /**
     * Validate file signature (magic bytes)
     */
    protected function validateFileSignature($file, $fail)
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            $fail('Không thể đọc file.');
            return;
        }

        $header = fread($handle, 16);
        fclose($handle);

        $signatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'pdf' => ["%PDF"],
            'psd' => ["8BPS"],
            'ai' => ["%!PS-Adobe", "%PDF"] // AI files can start with either
        ];

        $extension = strtolower($file->getClientOriginalExtension());

        if (isset($signatures[$extension])) {
            $validSignature = false;
            foreach ($signatures[$extension] as $signature) {
                if (strpos($header, $signature) === 0) {
                    $validSignature = true;
                    break;
                }
            }

            if (!$validSignature) {
                $fail("File {$extension} không hợp lệ hoặc bị corrupt.");
            }
        }
    }

    /**
     * Get the task from route
     */
    protected function getTask()
    {
        return $this->route('task');
    }
}
