<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Services\UserSpecificPricingImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserSpecificPricingImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_multiple_emails_in_one_row()
    {
        // Tạo test users
        $user1 = User::factory()->create(['email' => 'user1@test.com']);
        $user2 = User::factory()->create(['email' => 'user2@test.com']);
        $user3 = User::factory()->create(['email' => 'user3@test.com']);

        // Tạo test product và variant
        $product = Product::factory()->create(['id' => 1, 'name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => 1,
            'sku' => 'TEST-SKU-001'
        ]);

        // Test data với nhiều email
        $testData = [
            [
                'user_email' => 'user1@test.com,user2@test.com,user3@test.com',
                'product_id' => 1,
                'product_name' => 'Test Product',
                'variant_sku' => 'TEST-SKU-001',
                'tiktok_1st' => '10.00',
                'tiktok_next' => '5.00',
                'seller_1st' => '8.00',
                'seller_next' => '4.00',
                'currency' => 'USD'
            ]
        ];

        // Import data
        $results = UserSpecificPricingImportService::importFromData($testData);

        // Kiểm tra kết quả
        $this->assertEquals(1, $results['success']);
        $this->assertEquals(0, $results['failed']);

        // Kiểm tra records được tạo cho từng user
        $this->assertDatabaseHas('shipping_prices', [
            'user_id' => $user1->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);

        $this->assertDatabaseHas('shipping_prices', [
            'user_id' => $user2->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);

        $this->assertDatabaseHas('shipping_prices', [
            'user_id' => $user3->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);
    }

    public function test_can_import_multiple_emails_with_semicolon_separator()
    {
        // Tạo test users
        $user1 = User::factory()->create(['email' => 'user1@test.com']);
        $user2 = User::factory()->create(['email' => 'user2@test.com']);

        // Tạo test product và variant
        $product = Product::factory()->create(['id' => 1, 'name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => 1,
            'sku' => 'TEST-SKU-001'
        ]);

        // Test data với semicolon separator
        $testData = [
            [
                'user_email' => 'user1@test.com;user2@test.com',
                'product_id' => 1,
                'product_name' => 'Test Product',
                'variant_sku' => 'TEST-SKU-001',
                'tiktok_1st' => '15.00',
                'currency' => 'USD'
            ]
        ];

        // Import data
        $results = UserSpecificPricingImportService::importFromData($testData);

        // Kiểm tra kết quả
        $this->assertEquals(1, $results['success']);
        $this->assertEquals(0, $results['failed']);

        // Kiểm tra records được tạo
        $this->assertDatabaseHas('shipping_prices', [
            'user_id' => $user1->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 15.00,
            'currency' => 'USD'
        ]);

        $this->assertDatabaseHas('shipping_prices', [
            'user_id' => $user2->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 15.00,
            'currency' => 'USD'
        ]);
    }

    public function test_handles_invalid_emails_correctly()
    {
        // Tạo test user
        $user = User::factory()->create(['email' => 'valid@test.com']);

        // Tạo test product và variant
        $product = Product::factory()->create(['id' => 1, 'name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => 1,
            'sku' => 'TEST-SKU-001'
        ]);

        // Test data với email không hợp lệ
        $testData = [
            [
                'user_email' => 'valid@test.com,invalid-email,another@test.com',
                'product_id' => 1,
                'product_name' => 'Test Product',
                'variant_sku' => 'TEST-SKU-001',
                'tiktok_1st' => '10.00',
                'currency' => 'USD'
            ]
        ];

        // Import data
        $results = UserSpecificPricingImportService::importFromData($testData);

        // Kiểm tra kết quả - nên fail vì có email không hợp lệ
        $this->assertEquals(0, $results['success']);
        $this->assertEquals(1, $results['failed']);
        $this->assertNotEmpty($results['errors']);
    }

    public function test_handles_non_existent_emails_correctly()
    {
        // Tạo test user
        $user = User::factory()->create(['email' => 'valid@test.com']);

        // Tạo test product và variant
        $product = Product::factory()->create(['id' => 1, 'name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => 1,
            'sku' => 'TEST-SKU-001'
        ]);

        // Test data với email không tồn tại
        $testData = [
            [
                'user_email' => 'valid@test.com,nonexistent@test.com',
                'product_id' => 1,
                'product_name' => 'Test Product',
                'variant_sku' => 'TEST-SKU-001',
                'tiktok_1st' => '10.00',
                'currency' => 'USD'
            ]
        ];

        // Import data
        $results = UserSpecificPricingImportService::importFromData($testData);

        // Kiểm tra kết quả - nên fail vì có email không tồn tại
        $this->assertEquals(0, $results['success']);
        $this->assertEquals(1, $results['failed']);
        $this->assertNotEmpty($results['errors']);
    }

    public function test_backward_compatibility_with_single_email()
    {
        // Tạo test user
        $user = User::factory()->create(['email' => 'user@test.com']);

        // Tạo test product và variant
        $product = Product::factory()->create(['id' => 1, 'name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => 1,
            'sku' => 'TEST-SKU-001'
        ]);

        // Test data với một email (backward compatibility)
        $testData = [
            [
                'user_email' => 'user@test.com',
                'product_id' => 1,
                'product_name' => 'Test Product',
                'variant_sku' => 'TEST-SKU-001',
                'tiktok_1st' => '10.00',
                'currency' => 'USD'
            ]
        ];

        // Import data
        $results = UserSpecificPricingImportService::importFromData($testData);

        // Kiểm tra kết quả
        $this->assertEquals(1, $results['success']);
        $this->assertEquals(0, $results['failed']);

        // Kiểm tra record được tạo
        $this->assertDatabaseHas('shipping_prices', [
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);
    }
}
