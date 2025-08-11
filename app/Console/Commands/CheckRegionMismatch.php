<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\S3\S3Client;
use Aws\Ec2\Ec2Client;
use Illuminate\Support\Facades\Http;

class CheckRegionMismatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aws:check-region-mismatch 
                           {--fix : Tự động sửa cấu hình nếu có mismatch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra region mismatch giữa EC2 và S3 bucket';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== KIỂM TRA REGION MISMATCH ===\n");

        // 1. Lấy EC2 region
        $ec2Region = $this->getEC2Region();
        $this->info("1. EC2 Region: {$ec2Region}");

        // 2. Lấy S3 bucket region
        $s3Region = $this->getS3BucketRegion();
        $this->info("2. S3 Bucket Region: {$s3Region}");

        // 3. Lấy config region
        $configRegion = env('AWS_DEFAULT_REGION', 'ap-southeast-1');
        $this->info("3. Config Region: {$configRegion}");

        // 4. So sánh và đưa ra kết luận
        $this->compareRegions($ec2Region, $s3Region, $configRegion);

        // 5. Test performance
        $this->testPerformance($ec2Region, $s3Region);

        // 6. Đề xuất giải pháp
        $this->suggestSolutions($ec2Region, $s3Region, $configRegion);

        return 0;
    }

    /**
     * Lấy region của EC2 instance
     */
    private function getEC2Region(): string
    {
        try {
            // Cách 1: Sử dụng EC2 metadata service
            $response = Http::timeout(5)->get('http://169.254.169.254/latest/meta-data/placement/region');

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Exception $e) {
            $this->warn("   Không thể lấy EC2 region từ metadata service");
        }

        try {
            // Cách 2: Sử dụng AWS SDK
            $ec2Client = new Ec2Client([
                'version' => 'latest',
                'region' => 'us-east-1', // Default region để lấy instance info
            ]);

            $result = $ec2Client->describeRegions();
            $regions = $result['Regions'];

            // Tìm region có EC2 instance
            foreach ($regions as $region) {
                try {
                    $ec2Client = new Ec2Client([
                        'version' => 'latest',
                        'region' => $region['RegionName'],
                    ]);

                    $instances = $ec2Client->describeInstances();
                    if (!empty($instances['Reservations'])) {
                        return $region['RegionName'];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            $this->warn("   Không thể lấy EC2 region từ AWS SDK");
        }

        return 'unknown';
    }

    /**
     * Lấy region của S3 bucket
     */
    private function getS3BucketRegion(): string
    {
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
            ]);

            $bucket = config('filesystems.disks.s3.bucket');

            $result = $s3Client->getBucketLocation(['Bucket' => $bucket]);
            $region = $result['LocationConstraint'];

            // us-east-1 trả về null
            return $region ?: 'us-east-1';
        } catch (\Exception $e) {
            $this->error("   Không thể lấy S3 bucket region: " . $e->getMessage());
            return 'unknown';
        }
    }

    /**
     * So sánh regions
     */
    private function compareRegions(string $ec2Region, string $s3Region, string $configRegion)
    {
        $this->info("\n4. So sánh Regions:");

        $mismatches = [];

        if ($ec2Region !== 'unknown' && $s3Region !== 'unknown') {
            if ($ec2Region !== $s3Region) {
                $mismatches[] = "EC2 ({$ec2Region}) ≠ S3 ({$s3Region})";
                $this->error("   ✗ Region mismatch: EC2 và S3 ở khác region!");
            } else {
                $this->line("   ✓ EC2 và S3 cùng region");
            }
        }

        if ($configRegion !== $s3Region) {
            $mismatches[] = "Config ({$configRegion}) ≠ S3 ({$s3Region})";
            $this->warn("   ⚠️  Config region khác S3 region");
        } else {
            $this->line("   ✓ Config và S3 cùng region");
        }

        if (empty($mismatches)) {
            $this->info("   ✓ Tất cả regions đều khớp!");
        } else {
            $this->error("   Có " . count($mismatches) . " mismatch:");
            foreach ($mismatches as $mismatch) {
                $this->error("     - {$mismatch}");
            }
        }
    }

    /**
     * Test performance
     */
    private function testPerformance(string $ec2Region, string $s3Region)
    {
        $this->info("\n5. Test Performance:");

        if ($ec2Region === 'unknown' || $s3Region === 'unknown') {
            $this->warn("   Không thể test performance do không xác định được region");
            return;
        }

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
            ]);

            $bucket = config('filesystems.disks.s3.bucket');

            // Test 1: List objects
            $startTime = microtime(true);
            $result = $s3Client->listObjectsV2([
                'Bucket' => $bucket,
                'MaxKeys' => 1
            ]);
            $endTime = microtime(true);

            $responseTime = round(($endTime - $startTime) * 1000, 2);

            if ($ec2Region === $s3Region) {
                if ($responseTime < 100) {
                    $this->line("   ✓ Fast response: {$responseTime}ms (cùng region)");
                } else {
                    $this->warn("   ⚠️  Slow response: {$responseTime}ms (cùng region)");
                }
            } else {
                if ($responseTime > 500) {
                    $this->error("   ✗ Very slow response: {$responseTime}ms (khác region)");
                } else {
                    $this->warn("   ⚠️  Moderate response: {$responseTime}ms (khác region)");
                }
            }

            // Test 2: Upload small file
            $testContent = str_repeat('A', 1024); // 1KB
            $testKey = 'test/region_test_' . time() . '.txt';

            $startTime = microtime(true);
            $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $testKey,
                'Body' => $testContent
            ]);
            $endTime = microtime(true);

            $uploadTime = round(($endTime - $startTime) * 1000, 2);

            if ($ec2Region === $s3Region) {
                if ($uploadTime < 200) {
                    $this->line("   ✓ Fast upload: {$uploadTime}ms (cùng region)");
                } else {
                    $this->warn("   ⚠️  Slow upload: {$uploadTime}ms (cùng region)");
                }
            } else {
                if ($uploadTime > 1000) {
                    $this->error("   ✗ Very slow upload: {$uploadTime}ms (khác region)");
                } else {
                    $this->warn("   ⚠️  Moderate upload: {$uploadTime}ms (khác region)");
                }
            }

            // Cleanup
            $s3Client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $testKey
            ]);
        } catch (\Exception $e) {
            $this->error("   Performance test failed: " . $e->getMessage());
        }
    }

    /**
     * Đề xuất giải pháp
     */
    private function suggestSolutions(string $ec2Region, string $s3Region, string $configRegion)
    {
        $this->info("\n6. Đề xuất Giải pháp:");

        if ($ec2Region === $s3Region && $configRegion === $s3Region) {
            $this->line("   ✓ Regions đã được cấu hình đúng!");
            return;
        }

        $solutions = [];

        if ($ec2Region !== 'unknown' && $s3Region !== 'unknown' && $ec2Region !== $s3Region) {
            $solutions[] = "Di chuyển S3 bucket từ {$s3Region} sang {$ec2Region}";
            $solutions[] = "Hoặc tạo S3 bucket mới ở {$ec2Region}";
            $solutions[] = "Hoặc di chuyển EC2 instance sang {$s3Region}";
        }

        if ($configRegion !== $s3Region) {
            $solutions[] = "Cập nhật AWS_DEFAULT_REGION trong .env thành {$s3Region}";
        }

        if (empty($solutions)) {
            $solutions[] = "Kiểm tra lại cấu hình AWS credentials và permissions";
        }

        foreach ($solutions as $solution) {
            $this->line("   • {$solution}");
        }

        // Hiển thị lệnh cụ thể
        if ($configRegion !== $s3Region) {
            $this->info("\n   Lệnh cập nhật .env:");
            $this->line("   sed -i 's/AWS_DEFAULT_REGION={$configRegion}/AWS_DEFAULT_REGION={$s3Region}/' .env");
        }

        // Hiển thị ước tính chi phí
        if ($ec2Region !== $s3Region) {
            $this->info("\n   ⚠️  Lưu ý về chi phí:");
            $this->line("   - Cross-region data transfer: ~$0.02/GB");
            $this->line("   - Latency: Tăng 50-200ms cho mỗi request");
            $this->line("   - Bandwidth: Bị giới hạn bởi internet connection");
        }
    }
}
