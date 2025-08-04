<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShippingPrice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupShippingPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping:cleanup 
                            {--user-id= : Cleanup shipping prices for specific user ID}
                            {--variant-id= : Cleanup shipping prices for specific variant ID}
                            {--method= : Cleanup shipping prices for specific method}
                            {--older-than= : Cleanup shipping prices older than X days}
                            {--dry-run : Preview without deleting}
                            {--force : Force delete without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup shipping prices by various criteria';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $variantId = $this->option('variant-id');
        $method = $this->option('method');
        $olderThan = $this->option('older-than');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Scanning for shipping prices to cleanup...');

        // Build query
        $query = ShippingPrice::query();

        if ($userId) {
            $query->where('user_id', $userId);
            $user = User::find($userId);
            $userInfo = $user ? "({$user->email})" : "(User not found)";
            $this->info("Filtering by user ID: {$userId} {$userInfo}");
        }

        if ($variantId) {
            $query->where('variant_id', $variantId);
            $this->info("Filtering by variant ID: {$variantId}");
        }

        if ($method) {
            $query->where('method', $method);
            $this->info("Filtering by method: {$method}");
        }

        if ($olderThan) {
            $date = now()->subDays($olderThan);
            $query->where('created_at', '<', $date);
            $this->info("Filtering shipping prices older than {$olderThan} days");
        }

        $shippingPrices = $query->with(['user', 'variant.product'])->get();

        if ($shippingPrices->isEmpty()) {
            $this->info('✅ No shipping prices found matching the criteria.');
            return 0;
        }

        $this->info("Found {$shippingPrices->count()} shipping prices to cleanup");

        // Hiển thị thông tin chi tiết
        $this->displayShippingPricesInfo($shippingPrices);

        if ($dryRun) {
            $this->info('🔍 DRY RUN - No shipping prices will be deleted');
            return 0;
        }

        // Xác nhận xóa
        if (!$force) {
            if (!$this->confirm('Are you sure you want to delete these shipping prices? This action cannot be undone.')) {
                $this->info('❌ Operation cancelled.');
                return 0;
            }
        }

        // Thực hiện xóa
        $deletedCount = $this->deleteShippingPrices($shippingPrices);

        $this->info("✅ Successfully deleted {$deletedCount} shipping prices");

        return 0;
    }

    /**
     * Hiển thị thông tin shipping prices
     */
    private function displayShippingPricesInfo($shippingPrices)
    {
        $headers = ['ID', 'User', 'Variant', 'Method', 'Price', 'Currency', 'Created'];
        $rows = [];

        foreach ($shippingPrices as $price) {
            $userInfo = $price->user ? $price->user->email : 'N/A';
            $variantInfo = $price->variant ? $price->variant->sku : 'N/A';

            $rows[] = [
                $price->id,
                $userInfo,
                $variantInfo,
                $price->method,
                $price->price,
                $price->currency,
                $price->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Xóa shipping prices
     */
    private function deleteShippingPrices($shippingPrices): int
    {
        $deletedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($shippingPrices as $price) {
                $this->info("Deleting shipping price: ID {$price->id} - User: {$price->user_id} - Method: {$price->method}");

                // Xóa shipping price
                $price->delete();
                $deletedCount++;

                Log::info("Shipping price deleted", [
                    'price_id' => $price->id,
                    'user_id' => $price->user_id,
                    'variant_id' => $price->variant_id,
                    'method' => $price->method,
                    'price' => $price->price,
                    'currency' => $price->currency
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error during deletion: " . $e->getMessage());
            Log::error("Error deleting shipping prices", ['error' => $e->getMessage()]);
            throw $e;
        }

        return $deletedCount;
    }
}
