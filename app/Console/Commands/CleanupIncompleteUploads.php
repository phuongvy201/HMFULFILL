<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\S3MultipartUploadService;

class CleanupIncompleteUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup 
                           {--hours=24 : Number of hours after which incomplete uploads should be cleaned}
                           {--dry-run : Show what would be cleaned without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup incomplete multipart uploads from S3';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $dryRun = $this->option('dry-run');

        $this->info("Cleaning up incomplete multipart uploads older than {$hours} hours...");

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode - no actual cleanup will be performed');
        }

        $uploadService = new S3MultipartUploadService();

        try {
            if ($dryRun) {
                // List what would be cleaned
                $uploads = $uploadService->listMultipartUploads();
                $cutoffTime = now()->subHours($hours);
                $toCleanCount = 0;

                $this->info('Incomplete uploads that would be cleaned:');
                $this->table(
                    ['Key', 'Upload ID', 'Initiated', 'Age (hours)'],
                    collect($uploads)->filter(function ($upload) use ($cutoffTime) {
                        return new \DateTime($upload['Initiated']) < $cutoffTime;
                    })->map(function ($upload) use (&$toCleanCount) {
                        $toCleanCount++;
                        $initiated = new \DateTime($upload['Initiated']);
                        $ageHours = $initiated->diff(now())->h + ($initiated->diff(now())->days * 24);

                        return [
                            'key' => $upload['Key'],
                            'upload_id' => substr($upload['UploadId'], 0, 20) . '...',
                            'initiated' => $upload['Initiated'],
                            'age' => $ageHours
                        ];
                    })->toArray()
                );

                $this->info("Total uploads that would be cleaned: {$toCleanCount}");
            } else {
                // Perform actual cleanup
                $cleanedCount = $uploadService->cleanupIncompleteUploads($hours);

                if ($cleanedCount > 0) {
                    $this->info("Successfully cleaned up {$cleanedCount} incomplete uploads");
                } else {
                    $this->info('No incomplete uploads found to clean up');
                }
            }
        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
