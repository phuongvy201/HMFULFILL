<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Models\VariantAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOldVariants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'variants:cleanup 
                            {--product-id= : Cleanup variants for specific product ID}
                            {--sku= : Cleanup specific variant by SKU}
                            {--older-than= : Cleanup variants older than X days}
                            {--unused : Only cleanup variants that are not used in any orders}
                            {--dry-run : Preview without deleting}
                            {--force : Force delete without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old/unused product variants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->option('product-id');
        $sku = $this->option('sku');
        $olderThan = $this->option('older-than');
        $unused = $this->option('unused');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Scanning for variants to cleanup...');

        // Build query
        $query = ProductVariant::query();

        if ($productId) {
            $query->where('product_id', $productId);
            $this->info("Filtering by product ID: {$productId}");
        }

        if ($sku) {
            $query->where('sku', $sku);
            $this->info("Filtering by SKU: {$sku}");
        }

        if ($olderThan) {
            $date = now()->subDays($olderThan);
            $query->where('created_at', '<', $date);
            $this->info("Filtering variants older than {$olderThan} days");
        }

        if ($unused) {
            // Chỉ xóa variant không có shipping prices và không được sử dụng trong orders
            $query->whereDoesntHave('shippingPrices');
            $this->info("Filtering unused variants (no shipping prices)");
        }

        $variants = $query->with(['product', 'attributes', 'shippingPrices'])->get();

        if ($variants->isEmpty()) {
            $this->info('✅ No variants found matching the criteria.');
            return 0;
        }

        $this->info("Found {$variants->count()} variants to cleanup");

        // Hiển thị thông tin chi tiết
        $this->displayVariantsInfo($variants);

        if ($dryRun) {
            $this->info('🔍 DRY RUN - No variants will be deleted');
            return 0;
        }

        // Xác nhận xóa
        if (!$force) {
            if (!$this->confirm('Are you sure you want to delete these variants? This action cannot be undone.')) {
                $this->info('❌ Operation cancelled.');
                return 0;
            }
        }

        // Thực hiện xóa
        $deletedCount = $this->deleteVariants($variants);

        $this->info("✅ Successfully deleted {$deletedCount} variants");

        return 0;
    }

    /**
     * Hiển thị thông tin variants
     */
    private function displayVariantsInfo($variants)
    {
        $headers = ['ID', 'SKU', 'Product', 'Attributes', 'Shipping Prices', 'Created'];
        $rows = [];

        foreach ($variants as $variant) {
            $attributes = $variant->attributes->map(function ($attr) {
                return "{$attr->name}: {$attr->value}";
            })->implode(', ');

            $shippingPricesCount = $variant->shippingPrices->count();

            $rows[] = [
                $variant->id,
                $variant->sku,
                $variant->product->name ?? 'N/A',
                $attributes ?: 'None',
                $shippingPricesCount,
                $variant->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Xóa variants và các dữ liệu liên quan
     */
    private function deleteVariants($variants): int
    {
        $deletedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($variants as $variant) {
                $this->info("Deleting variant: {$variant->sku} (ID: {$variant->id})");

                // Xóa shipping prices
                $shippingPricesCount = $variant->shippingPrices()->count();
                if ($shippingPricesCount > 0) {
                    $variant->shippingPrices()->delete();
                    $this->info("  - Deleted {$shippingPricesCount} shipping prices");
                }

                // Xóa variant attributes
                $attributesCount = $variant->attributes()->count();
                if ($attributesCount > 0) {
                    $variant->attributes()->delete();
                    $this->info("  - Deleted {$attributesCount} attributes");
                }

                // Xóa variant
                $variant->delete();
                $deletedCount++;

                Log::info("Variant deleted", [
                    'variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'product_id' => $variant->product_id,
                    'shipping_prices_deleted' => $shippingPricesCount,
                    'attributes_deleted' => $attributesCount
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error during deletion: " . $e->getMessage());
            Log::error("Error deleting variants", ['error' => $e->getMessage()]);
            throw $e;
        }

        return $deletedCount;
    }

    /**
     * Tìm variants không được sử dụng
     */
    public function findUnusedVariants()
    {
        return ProductVariant::whereDoesntHave('shippingPrices')
            ->whereDoesntHave('attributes')
            ->get();
    }

    /**
     * Tìm variants cũ theo ngày
     */
    public function findOldVariants($days)
    {
        $date = now()->subDays($days);
        return ProductVariant::where('created_at', '<', $date)->get();
    }

    /**
     * Tìm variants theo product
     */
    public function findVariantsByProduct($productId)
    {
        return ProductVariant::where('product_id', $productId)->get();
    }
}
