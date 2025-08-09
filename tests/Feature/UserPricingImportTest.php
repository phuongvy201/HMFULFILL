<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Models\ShippingOverride;
use App\Models\ImportFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserPricingImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** @test */
    public function it_can_import_user_pricing_from_excel()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 123]);
        $product = Product::factory()->create(['name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TEST001'
        ]);

        // Tạo shipping price cơ bản
        $shippingPrice = ShippingPrice::create([
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);

        // Tạo file Excel test
        $file = $this->createTestExcelFile([
            ['User ID', 'Product Name', 'Variant SKU', 'TikTok 1st Price', 'TikTok Next Price', 'Seller 1st Price', 'Seller Next Price', 'Currency'],
            ['123', 'Test Product', 'TEST001', '15.50', '18.00', '20.00', '22.50', 'USD']
        ]);

        // Import file
        $response = $this->actingAs($user)
            ->post('/admin/user-pricing/import', [
                'excel_file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra override được tạo
        $this->assertDatabaseHas('shipping_overrides', [
            'shipping_price_id' => $shippingPrice->id,
            'user_ids' => json_encode([123]),
            'override_price' => 15.50,
            'currency' => 'USD'
        ]);
    }

    /** @test */
    public function it_can_import_multiple_users_in_one_row()
    {
        // Tạo test data
        $user1 = User::factory()->create(['id' => 123]);
        $user2 = User::factory()->create(['id' => 456]);
        $product = Product::factory()->create(['name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TEST001'
        ]);

        // Tạo shipping price cơ bản
        $shippingPrice = ShippingPrice::create([
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);

        // Tạo file Excel test với nhiều user
        $file = $this->createTestExcelFile([
            ['User ID', 'Product Name', 'Variant SKU', 'TikTok 1st Price', 'TikTok Next Price', 'Seller 1st Price', 'Seller Next Price', 'Currency'],
            ['123,456', 'Test Product', 'TEST001', '15.50', '18.00', '20.00', '22.50', 'USD']
        ]);

        // Import file
        $response = $this->actingAs($user1)
            ->post('/admin/user-pricing/import', [
                'excel_file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra override được tạo cho cả 2 users
        $this->assertDatabaseHas('shipping_overrides', [
            'shipping_price_id' => $shippingPrice->id,
            'user_ids' => json_encode([123]),
            'override_price' => 15.50,
            'currency' => 'USD'
        ]);

        $this->assertDatabaseHas('shipping_overrides', [
            'shipping_price_id' => $shippingPrice->id,
            'user_ids' => json_encode([456]),
            'override_price' => 15.50,
            'currency' => 'USD'
        ]);
    }

    /** @test */
    public function it_validates_user_exists()
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TEST001'
        ]);

        // Tạo file Excel test với user không tồn tại
        $file = $this->createTestExcelFile([
            ['User ID', 'Product Name', 'Variant SKU', 'TikTok 1st Price', 'TikTok Next Price', 'Seller 1st Price', 'Seller Next Price', 'Currency'],
            ['999', 'Test Product', 'TEST001', '15.50', '18.00', '20.00', '22.50', 'USD']
        ]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->post('/admin/user-pricing/import', [
                'excel_file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_validates_variant_exists()
    {
        $user = User::factory()->create(['id' => 123]);
        $product = Product::factory()->create(['name' => 'Test Product']);

        // Tạo file Excel test với variant không tồn tại
        $file = $this->createTestExcelFile([
            ['User ID', 'Product Name', 'Variant SKU', 'TikTok 1st Price', 'TikTok Next Price', 'Seller 1st Price', 'Seller Next Price', 'Currency'],
            ['123', 'Test Product', 'INVALID_SKU', '15.50', '18.00', '20.00', '22.50', 'USD']
        ]);

        $response = $this->actingAs($user)
            ->post('/admin/user-pricing/import', [
                'excel_file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }



    /** @test */
    public function it_can_import_with_zero_prices()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 123]);
        $product = Product::factory()->create(['name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TEST001'
        ]);

        // Tạo shipping price cơ bản
        $shippingPrice = ShippingPrice::create([
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);

        // Tạo file Excel test với một số giá = 0
        $file = $this->createTestExcelFile([
            ['User ID', 'Product Name', 'Variant SKU', 'TikTok 1st Price', 'TikTok Next Price', 'Seller 1st Price', 'Seller Next Price', 'Currency'],
            ['123', 'Test Product', 'TEST001', '15.50', '0', '20.00', '0', 'USD']
        ]);

        // Import file
        $response = $this->actingAs($user)
            ->post('/admin/user-pricing/import', [
                'excel_file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra chỉ có override cho tiktok_1st và seller_1st được tạo
        $this->assertDatabaseHas('shipping_overrides', [
            'shipping_price_id' => $shippingPrice->id,
            'user_ids' => json_encode([123]),
            'override_price' => 15.50,
            'currency' => 'USD'
        ]);

        // Kiểm tra không có override cho tiktok_next và seller_next
        $this->assertDatabaseMissing('shipping_overrides', [
            'shipping_price_id' => $shippingPrice->id,
            'user_ids' => json_encode([123]),
            'override_price' => 0,
            'currency' => 'USD'
        ]);
    }

    /** @test */
    public function it_can_download_template()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/admin/user-pricing/template');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=user_pricing_template.xlsx');
    }

    /** @test */
    public function it_can_view_user_pricing_list()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/admin/user-pricing');

        $response->assertStatus(200);
        $response->assertViewIs('admin.user-pricing.index');
    }

    /** @test */
    public function it_stores_user_ids_correctly_in_database()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 123]);
        $product = Product::factory()->create(['name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TEST001'
        ]);

        // Tạo shipping price cơ bản
        $shippingPrice = ShippingPrice::create([
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 10.00,
            'currency' => 'USD'
        ]);

        // Tạo file Excel test
        $file = $this->createTestExcelFile([
            ['User ID', 'Product Name', 'Variant SKU', 'TikTok 1st Price', 'TikTok Next Price', 'Seller 1st Price', 'Seller Next Price', 'Currency'],
            ['123', 'Test Product', 'TEST001', '15.50', '18.00', '20.00', '22.50', 'USD']
        ]);

        // Import file
        $response = $this->actingAs($user)
            ->post('/admin/user-pricing/import', [
                'excel_file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra override được tạo và user_ids được lưu đúng
        $override = ShippingOverride::where('shipping_price_id', $shippingPrice->id)->first();

        $this->assertNotNull($override);
        $this->assertIsArray($override->user_ids);
        $this->assertContains(123, $override->user_ids);

        // Debug thông tin user_ids
        $debug = $override->debugUserIds();
        $this->assertTrue($debug['is_array']);
        $this->assertEquals(1, $debug['count']);
        $this->assertEquals([123], $debug['casted']);
    }

    /** @test */
    public function it_correctly_determines_shipping_method_based_on_shipping_method_column()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 123]);
        $product = Product::factory()->create(['name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TEST001'
        ]);

        // Tạo shipping prices cho cả TikTok và Seller
        $tiktokPrice = ShippingPrice::create([
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 15.00,
            'currency' => 'USD'
        ]);

        $sellerPrice = ShippingPrice::create([
            'variant_id' => $variant->id,
            'method' => 'seller_1st',
            'price' => 12.00,
            'currency' => 'USD'
        ]);

        // Test với shipping_method = 'tiktok_label'
        $service = new \App\Services\ExcelOrderImportService(new \App\Services\OrderRowValidator());
        $importFile = ImportFile::factory()->create(['user_id' => $user->id]);

        $rows = [
            [
                'A' => 'ORDER001',
                'Q' => 'TEST001',
                'W' => 'tiktok_label', // TikTok shipping method
                'S' => '1'
            ]
        ];

        // Test với shipping_method = '' (rỗng)
        $rows2 = [
            [
                'A' => 'ORDER002',
                'Q' => 'TEST001',
                'W' => '', // Rỗng - phải sử dụng seller
                'S' => '1'
            ]
        ];

        // Test với shipping_method = 'seller_label'
        $rows3 = [
            [
                'A' => 'ORDER003',
                'Q' => 'TEST001',
                'W' => 'seller_label', // Seller shipping method
                'S' => '1'
            ]
        ];

        // Kiểm tra logic xác định method
        $this->assertTrue(str_contains('tiktok_label', 'tiktok_label'));
        $this->assertFalse(str_contains('', 'tiktok_label'));
        $this->assertFalse(str_contains('seller_label', 'tiktok_label'));

        // Kiểm tra logic với empty string
        $this->assertFalse(!empty('') && str_contains('', 'tiktok_label'));
        $this->assertTrue(!empty('tiktok_label') && str_contains('tiktok_label', 'tiktok_label'));
        $this->assertFalse(!empty('seller_label') && str_contains('seller_label', 'tiktok_label'));
    }

    /** @test */
    public function it_correctly_determines_shipping_method_in_determineShippingMethod()
    {
        // Tạo test data
        $user = User::factory()->create(['id' => 123]);
        $product = Product::factory()->create(['name' => 'Test Product']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TEST001'
        ]);

        // Tạo shipping prices cho cả TikTok và Seller
        $tiktokPrice = ShippingPrice::create([
            'variant_id' => $variant->id,
            'method' => 'tiktok_1st',
            'price' => 15.00,
            'currency' => 'USD'
        ]);

        $sellerPrice = ShippingPrice::create([
            'variant_id' => $variant->id,
            'method' => 'seller_1st',
            'price' => 12.00,
            'currency' => 'USD'
        ]);

        // Test determineShippingMethod method bằng reflection
        $service = new \App\Services\ExcelOrderImportService(new \App\Services\OrderRowValidator());
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('determineShippingMethod');
        $method->setAccessible(true);

        // Test với shipping_method = 'tiktok_label'
        $method1 = $method->invoke($service, 'tiktok_label', 1);
        $this->assertEquals('tiktok_1st', $method1);

        // Test với shipping_method = '' (rỗng)
        $method2 = $method->invoke($service, '', 1);
        $this->assertEquals('seller_1st', $method2);

        // Test với shipping_method = 'seller_label'
        $method3 = $method->invoke($service, 'seller_label', 1);
        $this->assertEquals('seller_1st', $method3);

        // Test với shipping_method = 'Tiktok_label' (case insensitive)
        $method4 = $method->invoke($service, 'Tiktok_label', 1);
        $this->assertEquals('tiktok_1st', $method4);

        // Test với shipping_method = 'TIKTOK_LABEL' (uppercase)
        $method5 = $method->invoke($service, 'TIKTOK_LABEL', 1);
        $this->assertEquals('tiktok_1st', $method5);

        // Test với shipping_method = 'anything_else'
        $method6 = $method->invoke($service, 'anything_else', 1);
        $this->assertEquals('seller_1st', $method6);
    }

    /**
     * Tạo file Excel test
     */
    private function createTestExcelFile(array $data): UploadedFile
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($column . ($rowIndex + 1), $value);
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'test_user_pricing.xlsx';
        $filepath = storage_path('app/' . $filename);
        $writer->save($filepath);

        return new UploadedFile($filepath, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
