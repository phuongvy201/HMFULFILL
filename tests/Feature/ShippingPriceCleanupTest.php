<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Services\ShippingPriceCleanupService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingPriceCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_cleanup_shipping_prices_by_user_id_with_dry_run()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 18]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => 18,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Chạy command cleanup với dry-run
        $this->artisan('shipping:cleanup', ['--user-id' => 18, '--dry-run' => true])
            ->expectsOutput('🔍 Scanning for shipping prices to cleanup...')
            ->expectsOutput('🔍 DRY RUN - No shipping prices will be deleted')
            ->assertExitCode(0);

        // Kiểm tra shipping price vẫn còn tồn tại
        $this->assertDatabaseHas('shipping_prices', ['user_id' => 18]);
    }

    public function test_can_cleanup_shipping_prices_by_user_id()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 18]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => 18,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Chạy command cleanup
        $this->artisan('shipping:cleanup', ['--user-id' => 18, '--force' => true])
            ->expectsOutput('🔍 Scanning for shipping prices to cleanup...')
            ->assertExitCode(0);

        // Kiểm tra shipping price đã bị xóa
        $this->assertDatabaseMissing('shipping_prices', ['user_id' => 18]);
    }

    public function test_can_cleanup_shipping_prices_by_variant_id()
    {
        // Tạo test data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Chạy command cleanup theo variant_id
        $this->artisan('shipping:cleanup', ['--variant-id' => $variant->id, '--force' => true])
            ->assertExitCode(0);

        // Kiểm tra shipping price đã bị xóa
        $this->assertDatabaseMissing('shipping_prices', ['variant_id' => $variant->id]);
    }

    public function test_can_cleanup_shipping_prices_by_method()
    {
        // Tạo test data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Chạy command cleanup theo method
        $this->artisan('shipping:cleanup', ['--method' => 'tiktok_1st', '--force' => true])
            ->assertExitCode(0);

        // Kiểm tra shipping price đã bị xóa
        $this->assertDatabaseMissing('shipping_prices', ['method' => 'tiktok_1st']);
    }

    public function test_can_cleanup_old_shipping_prices()
    {
        // Tạo test data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // Tạo shipping price cũ
        ShippingPrice::factory()->create([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'created_at' => now()->subDays(40)
        ]);

        // Tạo shipping price mới
        ShippingPrice::factory()->create([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 15.00,
            'created_at' => now()->subDays(10)
        ]);

        // Chạy command cleanup shipping prices cũ hơn 30 ngày
        $this->artisan('shipping:cleanup', ['--older-than' => 30, '--force' => true])
            ->assertExitCode(0);

        // Kiểm tra shipping price cũ đã bị xóa, mới vẫn còn
        $this->assertDatabaseMissing('shipping_prices', [
            'price' => 10.00,
            'created_at' => now()->subDays(40)
        ]);
        $this->assertDatabaseHas('shipping_prices', [
            'price' => 15.00,
            'created_at' => now()->subDays(10)
        ]);
    }

    public function test_shipping_price_cleanup_service_can_delete_by_user_id()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 18]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => 18,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Xóa shipping prices bằng service
        $result = ShippingPriceCleanupService::deleteByUserId(18);

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['deleted_count']);
        $this->assertEquals(1, $result['total_found']);

        // Kiểm tra shipping price đã bị xóa
        $this->assertDatabaseMissing('shipping_prices', ['user_id' => 18]);
    }

    public function test_shipping_price_cleanup_service_can_delete_by_variant_id()
    {
        // Tạo test data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Xóa shipping prices bằng service
        $result = ShippingPriceCleanupService::deleteByVariantId($variant->id);

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['deleted_count']);

        // Kiểm tra shipping price đã bị xóa
        $this->assertDatabaseMissing('shipping_prices', ['variant_id' => $variant->id]);
    }

    public function test_shipping_price_cleanup_service_can_delete_by_method()
    {
        // Tạo test data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Xóa shipping prices bằng service
        $result = ShippingPriceCleanupService::deleteByMethod('tiktok_1st');

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['deleted_count']);

        // Kiểm tra shipping price đã bị xóa
        $this->assertDatabaseMissing('shipping_prices', ['method' => 'tiktok_1st']);
    }

    public function test_shipping_price_cleanup_service_can_delete_with_criteria()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 18]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => 18,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);

        // Xóa shipping prices với criteria
        $criteria = [
            'user_id' => 18,
            'method' => 'tiktok_1st',
            'currency' => 'USD'
        ];

        $result = ShippingPriceCleanupService::deleteWithCriteria($criteria);

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['deleted_count']);

        // Kiểm tra shipping price đã bị xóa
        $this->assertDatabaseMissing('shipping_prices', [
            'user_id' => 18,
            'method' => 'tiktok_1st',
            'currency' => 'USD'
        ]);
    }

    public function test_shipping_price_cleanup_service_can_find_shipping_prices()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 18]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => 18,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Tìm shipping prices
        $result = ShippingPriceCleanupService::findShippingPrices(['user_id' => 18]);

        // Kiểm tra kết quả
        $this->assertEquals(1, $result['count']);
        $this->assertEquals(18, $result['shipping_prices']->first()->user_id);
    }

    public function test_shipping_price_cleanup_service_can_backup_shipping_prices()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 18]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $shippingPrice = ShippingPrice::factory()->create([
            'user_id' => 18,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00
        ]);

        // Backup shipping prices
        $result = ShippingPriceCleanupService::backupShippingPrices(collect([$shippingPrice]));

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertFileExists($result['path']);
        $this->assertEquals(1, $result['count']);

        // Kiểm tra nội dung backup
        $backupContent = json_decode(file_get_contents($result['path']), true);
        $this->assertEquals(1, $backupContent['total_count']);
        $this->assertEquals(18, $backupContent['shipping_prices'][0]['user_id']);
    }

    public function test_shipping_price_cleanup_service_can_get_statistics()
    {
        // Tạo test data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        ShippingPrice::factory()->create([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);

        // Lấy thống kê
        $stats = ShippingPriceCleanupService::getStatistics();

        // Kiểm tra kết quả
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('user_specific', $stats);
        $this->assertArrayHasKey('by_method', $stats);
        $this->assertArrayHasKey('by_currency', $stats);
        $this->assertEquals(1, $stats['total']);
        $this->assertEquals(1, $stats['user_specific']);
    }

    public function test_shipping_price_cleanup_service_handles_user_not_found()
    {
        // Xóa shipping prices của user không tồn tại
        $result = ShippingPriceCleanupService::deleteByUserId(999);

        // Kiểm tra kết quả
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['error']);
    }

    public function test_shipping_price_cleanup_service_handles_no_shipping_prices()
    {
        // Tạo user nhưng không có shipping prices
        $user = User::factory()->create(['id' => 18]);

        // Xóa shipping prices
        $result = ShippingPriceCleanupService::deleteByUserId(18);

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['deleted_count']);
        $this->assertEquals(0, $result['total_found']);
    }
}
