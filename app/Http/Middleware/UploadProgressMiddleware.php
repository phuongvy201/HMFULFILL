<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UploadProgressMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log upload request info
        if ($request->hasFile('design_file') || $request->hasFile('design_files')) {
            $files = $request->hasFile('design_files')
                ? $request->file('design_files')
                : [$request->file('design_file')];

            $totalSize = 0;
            $fileInfo = [];

            foreach ($files as $index => $file) {
                if ($file && $file->isValid()) {
                    $size = $file->getSize();
                    $totalSize += $size;

                    $fileInfo[] = [
                        'name' => $file->getClientOriginalName(),
                        'size' => $size,
                        'mime' => $file->getMimeType()
                    ];
                }
            }

            Log::info('Design upload request started', [
                'user_id' => Auth::id(),
                'total_files' => count($fileInfo),
                'total_size' => $totalSize,
                'files' => $fileInfo,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Set timeout for large uploads
            if ($totalSize > 50 * 1024 * 1024) { // > 50MB
                set_time_limit(600); // 10 minutes
                ini_set('memory_limit', '512M');
            }
        }

        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // milliseconds

        // Log completion
        if ($request->hasFile('design_file') || $request->hasFile('design_files')) {
            Log::info('Design upload request completed', [
                'user_id' => Auth::id(),
                'duration_ms' => $duration,
                'status_code' => $response->getStatusCode(),
                'memory_peak' => memory_get_peak_usage(true)
            ]);
        }

        return $response;
    }
}
