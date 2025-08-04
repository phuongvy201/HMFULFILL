<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\ShippingPrice;
use Illuminate\Support\Facades\DB;

class AnalyzeVariants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'variants:analyze 
                            {--product-id= : Analyze variants for specific product ID}
                            {--unused : Show only unused variants}
                            {--orphaned : Show orphaned variants (no product)}
                            {--duplicates : Show duplicate SKUs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze product variants and provide cleanup recommendations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->option('product-id');
        $unused = $this->option('unused');
        $orphaned = $this->option('orphaned');
        $duplicates = $this->option('duplicates');

        $this->info('📊 Analyzing product variants...');

        if ($duplicates) {
            $this->analyzeDuplicates();
            return 0;
        }

        if ($orphaned) {
            $this->analyzeOrphaned();
            return 0;
        }

        if ($unused) {
            $this->analyzeUnused($productId);
            return 0;
        }

        // Phân tích tổng quan
        $this->analyzeOverview($productId);

        return 0;
    }

    /**
     * Phân tích tổng quan
     */
    private function analyzeOverview($productId = null)
    {
        $this->info('📈 Overall Statistics:');
        $this->info('');

        // Tổng số variants
        $totalVariants = ProductVariant::count();
        $this->info("Total variants: {$totalVariants}");

        // Variants theo product
        $variantsByProduct = ProductVariant::select('product_id', DB::raw('count(*) as count'))
            ->groupBy('product_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $this->info('');
        $this->info('Top products by variant count:');
        foreach ($variantsByProduct as $item) {
            $product = Product::find($item->product_id);
            $productName = $product ? $product->name : "Product ID {$item->product_id}";
            $this->info("  - {$productName}: {$item->count} variants");
        }

        // Variants không được sử dụng
        $unusedVariants = ProductVariant::whereDoesntHave('shippingPrices')->count();
        $this->info('');
        $this->info("Unused variants (no shipping prices): {$unusedVariants}");

        // Variants cũ (hơn 30 ngày)
        $oldVariants = ProductVariant::where('created_at', '<', now()->subDays(30))->count();
        $this->info("Old variants (>30 days): {$oldVariants}");

        // Variants không có attributes
        $noAttributes = ProductVariant::whereDoesntHave('attributes')->count();
        $this->info("Variants without attributes: {$noAttributes}");

        // Recommendations
        $this->info('');
        $this->info('💡 Recommendations:');

        if ($unusedVariants > 0) {
            $this->info("  - Run 'php artisan variants:cleanup --unused --dry-run' to see unused variants");
        }

        if ($oldVariants > 0) {
            $this->info("  - Run 'php artisan variants:cleanup --older-than=30 --dry-run' to see old variants");
        }

        if ($noAttributes > 0) {
            $this->info("  - Consider adding attributes to variants without attributes");
        }
    }

    /**
     * Phân tích variants không được sử dụng
     */
    private function analyzeUnused($productId = null)
    {
        $query = ProductVariant::whereDoesntHave('shippingPrices');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $unusedVariants = $query->with(['product', 'attributes'])->get();

        if ($unusedVariants->isEmpty()) {
            $this->info('✅ No unused variants found.');
            return;
        }

        $this->info("Found {$unusedVariants->count()} unused variants:");
        $this->info('');

        $headers = ['ID', 'SKU', 'Product', 'Attributes', 'Created'];
        $rows = [];

        foreach ($unusedVariants as $variant) {
            $attributes = $variant->attributes->map(function ($attr) {
                return "{$attr->name}: {$attr->value}";
            })->implode(', ');

            $rows[] = [
                $variant->id,
                $variant->sku,
                $variant->product->name ?? 'N/A',
                $attributes ?: 'None',
                $variant->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);

        $this->info('');
        $this->info('To delete these variants, run:');
        $this->info('  php artisan variants:cleanup --unused' . ($productId ? " --product-id={$productId}" : ''));
    }

    /**
     * Phân tích variants orphaned (không có product)
     */
    private function analyzeOrphaned()
    {
        $orphanedVariants = ProductVariant::whereDoesntHave('product')->get();

        if ($orphanedVariants->isEmpty()) {
            $this->info('✅ No orphaned variants found.');
            return;
        }

        $this->info("Found {$orphanedVariants->count()} orphaned variants:");
        $this->info('');

        $headers = ['ID', 'SKU', 'Product ID', 'Created'];
        $rows = [];

        foreach ($orphanedVariants as $variant) {
            $rows[] = [
                $variant->id,
                $variant->sku,
                $variant->product_id,
                $variant->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);

        $this->info('');
        $this->info('These variants have no associated product and should be cleaned up.');
    }

    /**
     * Phân tích SKU trùng lặp
     */
    private function analyzeDuplicates()
    {
        $duplicates = ProductVariant::select('sku', DB::raw('count(*) as count'))
            ->groupBy('sku')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicate SKUs found.');
            return;
        }

        $this->info("Found " . $duplicates->count() . " duplicate SKUs:");
        $this->info('');

        foreach ($duplicates as $duplicate) {
            $this->info("SKU '{$duplicate->sku}' appears {$duplicate->count} times:");

            $variants = ProductVariant::where('sku', $duplicate->sku)
                ->with(['product', 'attributes'])
                ->get();

            foreach ($variants as $variant) {
                $attributes = $variant->attributes->map(function ($attr) {
                    return "{$attr->name}: {$attr->value}";
                })->implode(', ');

                $this->info("  - ID: {$variant->id}, Product: " . ($variant->product->name ?? 'N/A') .
                    ", Attributes: " . ($attributes ?: 'None'));
            }
            $this->info('');
        }

        $this->info('💡 Recommendation: Review and merge duplicate variants or update SKUs.');
    }
}
