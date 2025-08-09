<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kiểm tra xem bảng shipping_prices có cột tier_name và user_id không
        $hasTierName = Schema::hasColumn('shipping_prices', 'tier_name');
        $hasUserId = Schema::hasColumn('shipping_prices', 'user_id');

        if (!$hasTierName && !$hasUserId) {
            Log::info("No tier_name or user_id columns found in shipping_prices. Skipping migration.");
            return;
        }

        // Migrate dữ liệu từ shipping_prices cũ sang shipping_overrides
        $query = DB::table('shipping_prices');

        if ($hasTierName && $hasUserId) {
            $query->whereNotNull('tier_name')->orWhereNotNull('user_id');
        } elseif ($hasTierName) {
            $query->whereNotNull('tier_name');
        } elseif ($hasUserId) {
            $query->whereNotNull('user_id');
        }

        $oldShippingPrices = $query->get();

        foreach ($oldShippingPrices as $oldPrice) {
            // Tạo override cho tier
            if ($hasTierName && $oldPrice->tier_name) {
                DB::table('shipping_overrides')->insert([
                    'shipping_price_id' => $oldPrice->id,
                    'tier_name' => $oldPrice->tier_name,
                    'override_price' => $oldPrice->price,
                    'currency' => $oldPrice->currency ?? 'USD',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Tạo override cho user
            if ($hasUserId && $oldPrice->user_id) {
                DB::table('shipping_overrides')->insert([
                    'shipping_price_id' => $oldPrice->id,
                    'user_ids' => json_encode([$oldPrice->user_id]),
                    'override_price' => $oldPrice->price,
                    'currency' => $oldPrice->currency ?? 'USD',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Log số lượng records đã migrate
        $migratedCount = DB::table('shipping_overrides')->count();
        Log::info("Migrated {$migratedCount} shipping override records");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa tất cả overrides đã tạo
        DB::table('shipping_overrides')->truncate();
    }
};
