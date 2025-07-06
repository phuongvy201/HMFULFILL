<?php

/**
 * Test script cho API thống kê đơn hàng
 * Chạy file này để test các endpoint API thống kê
 */

require_once 'vendor/autoload.php';

// Khởi tạo Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ExcelOrder;
use App\Models\ExcelOrderItem;
use App\Models\User;
use Carbon\Carbon;

class OrderStatisticsTest
{
    private $baseUrl = 'http://localhost:8000/api';
    private $apiToken = 'your_api_token_here'; // Thay bằng token thực tế

    public function runTests()
    {
        echo "=== BẮT ĐẦU TEST API THỐNG KÊ ĐƠN HÀNG ===\n\n";

        // Test 1: Dashboard stats
        $this->testDashboardStats();

        // Test 2: Status statistics
        $this->testStatusStats();

        // Test 3: Warehouse statistics
        $this->testWarehouseStats();

        // Test 4: Revenue statistics
        $this->testRevenueStats();

        // Test 5: Top products
        $this->testTopProducts();

        // Test 6: Brand statistics
        $this->testBrandStats();

        echo "\n=== HOÀN THÀNH TEST ===\n";
    }

    private function testDashboardStats()
    {
        echo "1. Test Dashboard Stats...\n";

        $url = $this->baseUrl . '/statistics/dashboard?period=month';
        $response = $this->makeRequest($url);

        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Dashboard stats OK\n";
            echo "   - Total orders: " . $response['data']['overview']['total_orders'] . "\n";
            echo "   - Total revenue: $" . number_format($response['data']['overview']['total_revenue'], 2) . "\n";
            echo "   - Total items: " . $response['data']['overview']['total_items'] . "\n";
        } else {
            echo "❌ Dashboard stats FAILED\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
        echo "\n";
    }

    private function testStatusStats()
    {
        echo "2. Test Status Statistics...\n";

        $url = $this->baseUrl . '/statistics/status?period=month';
        $response = $this->makeRequest($url);

        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Status statistics OK\n";
            foreach ($response['data'] as $status => $count) {
                echo "   - $status: $count orders\n";
            }
        } else {
            echo "❌ Status statistics FAILED\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
        echo "\n";
    }

    private function testWarehouseStats()
    {
        echo "3. Test Warehouse Statistics...\n";

        $url = $this->baseUrl . '/statistics/warehouse?period=month';
        $response = $this->makeRequest($url);

        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Warehouse statistics OK\n";
            foreach ($response['data'] as $warehouse => $count) {
                echo "   - $warehouse: $count orders\n";
            }
        } else {
            echo "❌ Warehouse statistics FAILED\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
        echo "\n";
    }

    private function testRevenueStats()
    {
        echo "4. Test Revenue Statistics...\n";

        $url = $this->baseUrl . '/statistics/revenue?period=month';
        $response = $this->makeRequest($url);

        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Revenue statistics OK\n";
            echo "   - Data points: " . count($response['data']) . "\n";
            if (!empty($response['data'])) {
                $totalRevenue = array_sum(array_column($response['data'], 'revenue'));
                echo "   - Total revenue: $" . number_format($totalRevenue, 2) . "\n";
            }
        } else {
            echo "❌ Revenue statistics FAILED\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
        echo "\n";
    }

    private function testTopProducts()
    {
        echo "5. Test Top Products...\n";

        $url = $this->baseUrl . '/statistics/top-products?period=month&limit=5';
        $response = $this->makeRequest($url);

        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Top products OK\n";
            echo "   - Products returned: " . count($response['data']) . "\n";
            if (!empty($response['data'])) {
                echo "   - Top product: " . $response['data'][0]['title'] . " (" . $response['data'][0]['total_quantity'] . " units)\n";
            }
        } else {
            echo "❌ Top products FAILED\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
        echo "\n";
    }

    private function testBrandStats()
    {
        echo "6. Test Brand Statistics...\n";

        $url = $this->baseUrl . '/statistics/brands?period=month';
        $response = $this->makeRequest($url);

        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Brand statistics OK\n";
            echo "   - Brands returned: " . count($response['data']) . "\n";
            if (!empty($response['data'])) {
                echo "   - Top brand: " . $response['data'][0]['brand'] . " (" . $response['data'][0]['order_count'] . " orders)\n";
            }
        } else {
            echo "❌ Brand statistics FAILED\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
        echo "\n";
    }

    private function makeRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            echo "   HTTP Error: $httpCode\n";
            return null;
        }
    }

    /**
     * Tạo dữ liệu test
     */
    public function createTestData()
    {
        echo "Tạo dữ liệu test...\n";

        // Tạo user test nếu chưa có
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]
        );

        // Tạo một số đơn hàng test
        $warehouses = ['US', 'UK', 'VN'];
        $statuses = ['pending', 'processed', 'failed', 'cancelled', 'on hold'];
        $brands = ['Brand A', 'Brand B', 'Brand C'];

        for ($i = 1; $i <= 20; $i++) {
            $order = ExcelOrder::create([
                'external_id' => 'TEST-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'brand' => $brands[array_rand($brands)],
                'channel' => 'api',
                'buyer_email' => 'customer' . $i . '@example.com',
                'first_name' => 'Customer',
                'last_name' => $i,
                'company' => 'Test Company',
                'address1' => '123 Test Street',
                'city' => 'Test City',
                'post_code' => '12345',
                'country' => 'US',
                'phone1' => '+1234567890',
                'status' => $statuses[array_rand($statuses)],
                'warehouse' => $warehouses[array_rand($warehouses)],
                'created_by' => $user->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30))
            ]);

            // Tạo items cho đơn hàng
            $itemCount = rand(1, 3);
            for ($j = 1; $j <= $itemCount; $j++) {
                ExcelOrderItem::create([
                    'excel_order_id' => $order->id,
                    'part_number' => 'PROD-' . str_pad($j, 3, '0', STR_PAD_LEFT),
                    'title' => 'Test Product ' . $j,
                    'quantity' => rand(1, 5),
                    'print_price' => rand(10, 100),
                    'description' => 'Test product description'
                ]);
            }
        }

        echo "✅ Đã tạo 20 đơn hàng test\n";
    }
}

// Chạy test
$test = new OrderStatisticsTest();

// Uncomment dòng dưới để tạo dữ liệu test (chỉ chạy 1 lần)
// $test->createTestData();

$test->runTests();
