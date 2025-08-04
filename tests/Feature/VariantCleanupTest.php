<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttribute;
use App\Models\ShippingPrice;
use App\Services\VariantCleanupService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VariantCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_analyze_variants()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $variant1 = ProductVariant::factory()->create(['product_id' => $product->id]);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product->id]);

        // Tạo shipping price cho variant1
        ShippingPrice::factory()->create(['variant_id' => $variant1->id]);

        // Chạy command analyze
        $this->artisan('variants:analyze')
            ->expectsOutput('📊 Analyzing product variants...')
            ->assertExitCode(0);
    }

    public function test_can_find_unused_variants()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $usedVariant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $unusedVariant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // Tạo shipping price cho used variant
        ShippingPrice::factory()->create(['variant_id' => $usedVariant->id]);

        // Chạy command analyze unused
        $this->artisan('variants:analyze', ['--unused' => true])
            ->expectsOutput('📊 Analyzing product variants...')
            ->assertExitCode(0);
    }

    public function test_can_cleanup_unused_variants_with_dry_run()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $unusedVariant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // Chạy command cleanup với dry-run
        $this->artisan('variants:cleanup', ['--unused' => true, '--dry-run' => true])
            ->expectsOutput('🔍 Scanning for variants to cleanup...')
            ->expectsOutput('🔍 DRY RUN - No variants will be deleted')
            ->assertExitCode(0);

        // Kiểm tra variant vẫn còn tồn tại
        $this->assertDatabaseHas('product_variants', ['id' => $unusedVariant->id]);
    }

    public function test_can_cleanup_unused_variants()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $unusedVariant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // Chạy command cleanup
        $this->artisan('variants:cleanup', ['--unused' => true, '--force' => true])
            ->expectsOutput('🔍 Scanning for variants to cleanup...')
            ->assertExitCode(0);

        // Kiểm tra variant đã bị xóa
        $this->assertDatabaseMissing('product_variants', ['id' => $unusedVariant->id]);
    }

    public function test_cannot_cleanup_variants_with_shipping_prices()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $usedVariant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // Tạo shipping price
        ShippingPrice::factory()->create(['variant_id' => $usedVariant->id]);

        // Chạy command cleanup
        $this->artisan('variants:cleanup', ['--unused' => true, '--force' => true])
            ->expectsOutput('🔍 Scanning for variants to cleanup...')
            ->expectsOutput('✅ No variants found matching the criteria.')
            ->assertExitCode(0);

        // Kiểm tra variant vẫn còn tồn tại
        $this->assertDatabaseHas('product_variants', ['id' => $usedVariant->id]);
    }

    public function test_can_cleanup_variants_by_product_id()
    {
        // Tạo test data
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id]);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id]);

        // Chạy command cleanup cho product1
        $this->artisan('variants:cleanup', [
            '--product-id' => $product1->id,
            '--force' => true
        ])->assertExitCode(0);

        // Kiểm tra variant1 đã bị xóa, variant2 vẫn còn
        $this->assertDatabaseMissing('product_variants', ['id' => $variant1->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant2->id]);
    }

    public function test_can_cleanup_variants_by_sku()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TEST-SKU-123'
        ]);

        // Chạy command cleanup theo SKU
        $this->artisan('variants:cleanup', [
            '--sku' => 'TEST-SKU-123',
            '--force' => true
        ])->assertExitCode(0);

        // Kiểm tra variant đã bị xóa
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    public function test_can_cleanup_old_variants()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $oldVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'created_at' => now()->subDays(40)
        ]);
        $newVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'created_at' => now()->subDays(10)
        ]);

        // Chạy command cleanup variants cũ hơn 30 ngày
        $this->artisan('variants:cleanup', [
            '--older-than' => 30,
            '--force' => true
        ])->assertExitCode(0);

        // Kiểm tra oldVariant đã bị xóa, newVariant vẫn còn
        $this->assertDatabaseMissing('product_variants', ['id' => $oldVariant->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $newVariant->id]);
    }

    public function test_variant_cleanup_service_can_delete_variant()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // Tạo attributes và shipping prices
        VariantAttribute::factory()->create(['variant_id' => $variant->id]);
        ShippingPrice::factory()->create(['variant_id' => $variant->id]);

        // Xóa variant bằng service
        $result = VariantCleanupService::deleteVariant($variant);

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertEquals($variant->id, $result['variant_id']);
        $this->assertEquals(1, $result['deleted_data']['attributes']);
        $this->assertEquals(1, $result['deleted_data']['shipping_prices']);

        // Kiểm tra variant đã bị xóa
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
        $this->assertDatabaseMissing('variant_attributes', ['variant_id' => $variant->id]);
        $this->assertDatabaseMissing('shipping_prices', ['variant_id' => $variant->id]);
    }

    public function test_variant_cleanup_service_can_find_safe_variants()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $safeVariant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $unsafeVariant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // Tạo shipping price cho unsafe variant
        ShippingPrice::factory()->create(['variant_id' => $unsafeVariant->id]);

        // Tìm variants an toàn để xóa
        $result = VariantCleanupService::findSafeToDeleteVariants(['unused_only' => true]);

        // Kiểm tra kết quả
        $this->assertEquals(1, $result['count']);
        $this->assertEquals($safeVariant->id, $result['variants']->first()->id);
    }

    public function test_variant_cleanup_service_can_backup_variant()
    {
        // Tạo test data
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        VariantAttribute::factory()->create(['variant_id' => $variant->id]);
        ShippingPrice::factory()->create(['variant_id' => $variant->id]);

        // Backup variant
        $result = VariantCleanupService::backupVariant($variant);

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertFileExists($result['path']);

        // Kiểm tra nội dung backup
        $backupContent = json_decode(file_get_contents($result['path']), true);
        $this->assertEquals($variant->id, $backupContent['variant']['id']);
        $this->assertEquals($variant->sku, $backupContent['variant']['sku']);
        $this->assertCount(1, $backupContent['attributes']);
        $this->assertCount(1, $backupContent['shipping_prices']);
    }

    public function test_variant_cleanup_service_can_restore_variant()
    {
        // Tạo test data và backup
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        VariantAttribute::factory()->create(['variant_id' => $variant->id]);
        ShippingPrice::factory()->create(['variant_id' => $variant->id]);

        $backupResult = VariantCleanupService::backupVariant($variant);
        $filename = basename($backupResult['path']);

        // Xóa variant gốc
        $variant->delete();

        // Restore variant
        $result = VariantCleanupService::restoreVariant($filename);

        // Kiểm tra kết quả
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['restored_data']['attributes']);
        $this->assertEquals(1, $result['restored_data']['shipping_prices']);

        // Kiểm tra variant đã được restore
        $this->assertDatabaseHas('product_variants', ['sku' => $variant->sku]);
        $this->assertDatabaseHas('variant_attributes', ['variant_id' => $result['variant_id']]);
        $this->assertDatabaseHas('shipping_prices', ['variant_id' => $result['variant_id']]);
    }
}
