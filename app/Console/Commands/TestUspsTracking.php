<?php

namespace App\Console\Commands;

use App\Services\UspsTrackingService;
use Illuminate\Console\Command;

class TestUspsTracking extends Command
{
    protected $signature = 'usps:test-tracking {tracking_number}';
    protected $description = 'Test USPS tracking service với tracking number';

    public function handle()
    {
        $trackingNumber = $this->argument('tracking_number');

        if (empty($trackingNumber)) {
            $this->error('Vui lòng cung cấp tracking number');
            return 1;
        }

        $this->info("🔍 Đang kiểm tra tracking number: {$trackingNumber}");

        $uspsService = app(UspsTrackingService::class);

        try {
            // Test track single package
            $this->info('📦 Testing single package tracking...');
            $result = $uspsService->trackSinglePackage($trackingNumber);

            if ($result['success']) {
                $this->info('✅ Tracking thành công!');
                $this->displayTrackingResult($result);
            } else {
                $this->error('❌ Tracking thất bại: ' . ($result['error'] ?? 'Unknown error'));
            }

            // Test delivery status check
            $this->info('🚚 Testing delivery status check...');
            $statusResult = $uspsService->checkDeliveryStatus($trackingNumber);

            if ($statusResult['success']) {
                $this->info('✅ Status check thành công!');
                $this->displayStatusResult($statusResult);
            } else {
                $this->error('❌ Status check thất bại: ' . ($statusResult['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->error('❌ Lỗi: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function displayTrackingResult($result)
    {
        if (empty($result['packages'])) {
            $this->warn('⚠️ Không có thông tin package');
            return;
        }

        foreach ($result['packages'] as $package) {
            $this->info('📋 Thông tin Package:');
            $this->line('   Tracking Number: ' . $package['tracking_number']);
            $this->line('   Track Summary: ' . ($package['track_summary'] ?? 'N/A'));
            $this->line('   Expected Delivery: ' . ($package['expected_delivery_date'] ?? 'N/A'));
            $this->line('   Expected Time: ' . ($package['expected_delivery_time'] ?? 'N/A'));
            $this->line('   Guaranteed Delivery: ' . ($package['guaranteed_delivery_date'] ?? 'N/A'));

            if (!empty($package['track_details'])) {
                $this->info('   📍 Track Details:');
                foreach ($package['track_details'] as $detail) {
                    $this->line('      - ' . $detail);
                }
            }
        }
    }

    private function displayStatusResult($result)
    {
        $this->info('📊 Status Summary:');
        $this->line('   Status: ' . $result['status']);
        $this->line('   Is Delivered: ' . ($result['is_delivered'] ? 'Yes' : 'No'));
        $this->line('   Track Summary: ' . ($result['track_summary'] ?? 'N/A'));
        $this->line('   Expected Delivery: ' . ($result['expected_delivery_date'] ?? 'N/A'));
    }
}
