<?php

namespace App\Console\Commands;

use App\Models\ExcelOrder;
use App\Services\TwofifteenService;
use App\Services\DtfService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateTrackingNumbers extends Command
{
    protected $signature = 'orders:update-tracking-numbers';
    protected $description = 'Lấy mã vận đơn và cập nhật trạng thái Đã giao hàng từ API Twofifteen (UK) và DTF (US) bằng internal_id vào bảng excel_orders';

    public function handle()
    {
        // Xử lý đơn hàng UK (Twofifteen)
        $this->processTwofifteenOrders();

        // Xử lý đơn hàng US (DTF)
        $this->processDtfOrders();

        $this->info('Hoàn tất cập nhật mã vận đơn và trạng thái cho cả UK và US.');
    }

    private function processTwofifteenOrders()
    {
        $this->info('Bắt đầu xử lý đơn hàng UK (Twofifteen)...');

        // Lấy đơn hàng UK từ 1 tuần trở lại đây, có trạng thái processed và tracking_number là null
        $query = ExcelOrder::query()
            ->where('warehouse', 'UK')
            ->where('status', ExcelOrder::STATUS_PROCESSED)
            ->whereNull('tracking_number')
            ->where('excel_orders.created_at', '>=', now()->subWeek()) // Chỉ định rõ bảng cho created_at
            ->join('orders_mapping', 'excel_orders.external_id', '=', 'orders_mapping.external_id')
            ->where('orders_mapping.factory', 'twofifteen') // Chỉ lấy ánh xạ của Twofifteen
            ->select('excel_orders.id', 'excel_orders.external_id', 'orders_mapping.internal_id');

        $orders = $query->pluck('internal_id', 'excel_orders.id');

        if ($orders->isEmpty()) {
            $this->info('Không có đơn hàng UK nào cần cập nhật mã vận đơn.');
            Log::info('Không tìm thấy đơn hàng UK nào có trạng thái processed, tracking_number null, từ 1 tuần trở lại đây, warehouse=UK, và có ánh xạ Twofifteen trong orders_mapping');
            return;
        }

        $this->info("Tìm thấy {$orders->count()} đơn hàng UK cần cập nhật tracking number. Bắt đầu xử lý...");
        Log::info("Tìm thấy {$orders->count()} đơn hàng UK để cập nhật tracking number", ['internal_ids' => $orders->values()->toArray()]);

        $twofifteenService = app(TwofifteenService::class);
        $internalIds = $orders->values()->toArray();

        // Chia danh sách internal_id thành các lô 100 ID
        $batches = array_chunk($internalIds, 100);
        $this->info("Chia thành " . count($batches) . " lô, mỗi lô tối đa 100 đơn.");

        // Ánh xạ internal_id sang order_id và external_id
        $orderMap = ExcelOrder::whereIn('id', $orders->keys()->toArray())
            ->pluck('external_id', 'id')
            ->mapWithKeys(function ($externalId, $orderId) use ($orders) {
                return [$orders[$orderId] => ['order_id' => $orderId, 'external_id' => $externalId]];
            })->toArray();

        foreach ($batches as $batchIndex => $batchInternalIds) {
            $this->info("Xử lý lô UK " . ($batchIndex + 1) . "...");
            try {
                $apiOrders = $twofifteenService->getOrdersByInternalIds($batchInternalIds);
                if (empty($apiOrders)) {
                    $this->warn("Không có dữ liệu từ Twofifteen cho lô " . ($batchIndex + 1));
                    Log::warning("Không có dữ liệu từ API Twofifteen cho lô " . ($batchIndex + 1));
                    continue;
                }

                // Cập nhật bảng excel_orders
                foreach ($apiOrders as $apiOrder) {
                    if (!$apiOrder['internal_id'] || !isset($orderMap[$apiOrder['internal_id']])) {
                        continue;
                    }

                    $orderInfo = $orderMap[$apiOrder['internal_id']];
                    $orderId = $orderInfo['order_id'];
                    $externalId = $orderInfo['external_id'];

                    try {
                        $order = ExcelOrder::find($orderId);
                        if ($order) {
                            $trackingNumber = $apiOrder['trackingNumber'] ?? null;
                            $apiStatus = $apiOrder['status'] ?? null;

                            // Xác định status mới dựa trên API response
                            $newStatus = $order->status; // Mặc định giữ nguyên status hiện tại

                            // Chỉ cập nhật status khi API trả về "Shipped"
                            if ($apiStatus === 'Shipped') {
                                $newStatus = 'Shipped';
                            }

                            // Cập nhật tracking number và status
                            if ($trackingNumber) {
                                $order->updateTrackingAndStatus($trackingNumber, $newStatus);

                                $statusMessage = $newStatus !== $order->status ? " và cập nhật status thành '{$newStatus}'" : "";
                                $this->info("📦 Cập nhật tracking number: '{$trackingNumber}' cho đơn hàng UK {$externalId}{$statusMessage}");
                                Log::info("Cập nhật tracking number cho đơn hàng UK {$externalId}", [
                                    'tracking_number' => $trackingNumber,
                                    'old_status' => $order->status,
                                    'new_status' => $newStatus,
                                    'api_status' => $apiStatus
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Lỗi khi cập nhật đơn hàng UK {$externalId}: " . $e->getMessage());
                        $this->error("Lỗi khi xử lý đơn hàng UK {$externalId}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Lỗi khi xử lý lô UK " . ($batchIndex + 1) . ": " . $e->getMessage());
                $this->error("Lỗi khi xử lý lô UK " . ($batchIndex + 1));
            }
        }
    }

    private function processDtfOrders()
    {
        $this->info('Bắt đầu xử lý đơn hàng US (DTF)...');

        // Lấy đơn hàng US từ 1 tuần trở lại đây, có trạng thái processed và tracking_number là null
        $orders = ExcelOrder::query()
            ->where('warehouse', 'US')
            ->where('status', ExcelOrder::STATUS_PROCESSED)
            ->whereNull('tracking_number')
            ->where('excel_orders.created_at', '>=', now()->subWeek()) // Chỉ định rõ bảng cho created_at
            ->join('orders_mapping', 'excel_orders.external_id', '=', 'orders_mapping.external_id')
            ->where('orders_mapping.factory', 'dtf')
            ->select('excel_orders.id', 'excel_orders.external_id', 'orders_mapping.internal_id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Không có đơn hàng US nào cần cập nhật mã vận đơn.');
            Log::info('Không tìm thấy đơn hàng US nào có trạng thái processed, tracking_number null, từ 1 tuần trở lại đây, warehouse=US, và có ánh xạ DTF trong orders_mapping');
            return;
        }

        $this->info("Tìm thấy {$orders->count()} đơn hàng US cần cập nhật tracking number. Bắt đầu xử lý...");
        Log::info("Tìm thấy {$orders->count()} đơn hàng US để cập nhật tracking number", ['orders' => $orders->toArray()]);

        $dtfService = app(DtfService::class);

        // Chia danh sách orders thành các lô 100 đơn
        $batches = $orders->chunk(100);
        $this->info("Chia thành " . count($batches) . " lô, mỗi lô tối đa 100 đơn.");

        foreach ($batches as $batchIndex => $batchOrders) {
            $this->info("Xử lý lô US " . ($batchIndex + 1) . "...");
            try {
                // Lấy tracking number và status từ API DTF
                $apiOrders = $dtfService->getOrdersTracking($batchOrders);

                if (empty($apiOrders)) {
                    $this->warn("Không có dữ liệu từ DTF cho lô " . ($batchIndex + 1));
                    Log::warning("Không có dữ liệu từ API DTF cho lô " . ($batchIndex + 1));
                    continue;
                }

                // Cập nhật bảng excel_orders
                foreach ($apiOrders as $apiOrder) {
                    if (!$apiOrder['internal_id']) {
                        continue;
                    }

                    try {
                        $order = ExcelOrder::where('external_id', $apiOrder['external_id'])->first();
                        if ($order) {
                            $externalId = $apiOrder['external_id'];
                            $trackingNumber = $apiOrder['tracking_number'] ?? null;
                            $apiStatus = $apiOrder['status'] ?? null;

                            // Xác định status mới dựa trên API response
                            $newStatus = $order->status; // Mặc định giữ nguyên status hiện tại

                            // Nếu API trả về "complete" thì đổi thành "Shipped"
                            if ($apiStatus === 'complete') {
                                $newStatus = 'Shipped';
                            }

                            // Cập nhật tracking number và status
                            if ($trackingNumber) {
                                $order->updateTrackingAndStatus($trackingNumber, $newStatus);

                                $statusMessage = $newStatus !== $order->status ? " và cập nhật status thành '{$newStatus}'" : "";
                                $this->info("📦 Cập nhật tracking number: '{$trackingNumber}' cho đơn hàng US {$externalId}{$statusMessage}");
                                Log::info("Cập nhật tracking number cho đơn hàng US {$externalId}", [
                                    'tracking_number' => $trackingNumber,
                                    'old_status' => $order->status,
                                    'new_status' => $newStatus,
                                    'api_status' => $apiStatus
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Lỗi khi cập nhật đơn hàng US {$apiOrder['external_id']}: " . $e->getMessage());
                        $this->error("Lỗi khi xử lý đơn hàng US {$apiOrder['external_id']}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Lỗi khi xử lý lô US " . ($batchIndex + 1) . ": " . $e->getMessage());
                $this->error("Lỗi khi xử lý lô US " . ($batchIndex + 1));
            }
        }
    }
}
