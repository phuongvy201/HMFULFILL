<?php

return [
    /*
    |--------------------------------------------------------------------------
    | S3 Multipart Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for S3 multipart upload functionality
    |
    */

    // Threshold size để sử dụng multipart upload (bytes)
    // Files lớn hơn threshold này sẽ dùng multipart upload
    'multipart_threshold' => env('S3_MULTIPART_THRESHOLD', 100 * 1024 * 1024), // 100MB

    // Chunk sizes cho các loại file khác nhau (bytes)
    'chunk_sizes' => [
        'small' => 5 * 1024 * 1024,   // 5MB - for files <= 50MB
        'medium' => 10 * 1024 * 1024, // 10MB - for files <= 500MB
        'large' => 20 * 1024 * 1024,  // 20MB - for files <= 2GB
        'xlarge' => 50 * 1024 * 1024, // 50MB - for files > 2GB
    ],

    // File size thresholds để xác định chunk size
    'size_thresholds' => [
        'small' => 50 * 1024 * 1024,    // 50MB
        'medium' => 500 * 1024 * 1024,  // 500MB
        'large' => 2 * 1024 * 1024 * 1024, // 2GB
    ],

    // Retry configuration
    'retry' => [
        'max_attempts' => env('S3_UPLOAD_MAX_RETRIES', 3),
        'delay_multiplier' => 2, // Exponential backoff multiplier
        'max_delay' => 60, // Maximum delay in seconds
    ],

    // Cleanup configuration
    'cleanup' => [
        'auto_cleanup_enabled' => env('S3_AUTO_CLEANUP_ENABLED', true),
        'incomplete_upload_ttl_hours' => env('S3_CLEANUP_TTL_HOURS', 24),
    ],

    // Logging configuration
    'logging' => [
        'enabled' => env('S3_UPLOAD_LOGGING_ENABLED', true),
        'log_progress' => env('S3_LOG_PROGRESS', true),
        'progress_interval' => 10, // Log progress every 10%
    ],

    // Performance settings
    'performance' => [
        'concurrent_uploads' => env('S3_CONCURRENT_UPLOADS', 3),
        'memory_limit' => env('S3_UPLOAD_MEMORY_LIMIT', '512M'),
        'time_limit' => env('S3_UPLOAD_TIME_LIMIT', 600), // 10 minutes
    ],

    // File type specific settings
    'file_types' => [
        'design_files' => [
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'ai', 'psd', 'eps', 'svg'],
            'max_file_size' => 200 * 1024 * 1024, // 200MB per file
            'compression_enabled' => false,
        ],
        'images' => [
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'max_file_size' => 50 * 1024 * 1024, // 50MB
            'compression_enabled' => true,
        ],
        'documents' => [
            'allowed_extensions' => ['pdf', 'doc', 'docx', 'txt'],
            'max_file_size' => 100 * 1024 * 1024, // 100MB
            'compression_enabled' => false,
        ],
    ],

    // S3 specific settings
    's3' => [
        'storage_class' => env('S3_STORAGE_CLASS', 'STANDARD'),
        'server_side_encryption' => env('S3_SERVER_SIDE_ENCRYPTION', 'AES256'),
        'metadata_directive' => 'REPLACE',
        'cache_control' => 'max-age=31536000', // 1 year
    ],

    // Monitoring and alerts
    'monitoring' => [
        'enable_metrics' => env('S3_ENABLE_METRICS', true),
        'slow_upload_threshold_seconds' => 300, // 5 minutes
        'large_file_threshold_mb' => 100,
        'alert_on_failures' => env('S3_ALERT_ON_FAILURES', true),
    ],
];
