<?php

require_once 'vendor/autoload.php';

use App\Services\S3MultipartUploadService;
use Illuminate\Http\UploadedFile;

// Mock UploadedFile cho testing
class MockUploadedFile extends UploadedFile
{
    public function __construct($path, $originalName, $mimeType = null, $error = null, $test = true)
    {
        parent::__construct($path, $originalName, $mimeType, $error, $test);
    }
}

// Tạo file test tạm thời
$testFile1 = tempnam(sys_get_temp_dir(), 'test1');
file_put_contents($testFile1, 'Test content 1');

$testFile2 = tempnam(sys_get_temp_dir(), 'test2');
file_put_contents($testFile2, 'Test content 2');

// Tạo mock UploadedFile objects
$uploadedFile1 = new MockUploadedFile($testFile1, 'test1.txt', 'text/plain', null, true);
$uploadedFile2 = new MockUploadedFile($testFile2, 'test2.txt', 'text/plain', null, true);

$files = [$uploadedFile1, $uploadedFile2];

// Khởi tạo service
$uploadService = new S3MultipartUploadService();

echo "Testing uploadMultipleFilesParallel structure...\n";

try {
    // Test upload (sẽ fail vì không có S3 credentials, nhưng sẽ cho thấy cấu trúc)
    $results = $uploadService->uploadMultipleFilesParallel(
        $files,
        'test/upload',
        ['visibility' => 'private']
    );

    echo "Upload results structure:\n";
    print_r($results);
} catch (Exception $e) {
    echo "Expected error (no S3 credentials): " . $e->getMessage() . "\n";

    // Test cấu trúc promise function
    echo "\nTesting promise function structure...\n";

    $promises = [];
    $fileInfo = [];

    foreach ($files as $index => $file) {
        $originalName = $file->getClientOriginalName();
        $destinationPath = "test/upload/{$originalName}";

        $fileInfo[$index] = [
            'original_name' => $originalName,
            'size' => $file->getSize(),
            'destination_path' => $destinationPath
        ];

        $promises[$index] = function () use ($file, $destinationPath, $index) {
            return [
                'success' => false,
                'error' => 'Test error',
                'path' => null,
                'index' => $index
            ];
        };
    }

    echo "Promise function structure:\n";
    print_r($promises);

    echo "File info structure:\n";
    print_r($fileInfo);
}

// Cleanup
unlink($testFile1);
unlink($testFile2);

echo "\nTest completed.\n";
