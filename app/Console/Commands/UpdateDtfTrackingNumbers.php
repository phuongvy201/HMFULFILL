<?php

namespace App\Console\Commands;

use App\Models\ExcelOrder;
use App\Models\OrderMapping;
use App\Services\DtfService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateDtfTrackingNumbers extends Command
{
    protected $signature = 'orders:update-dtf-tracking-numbers';
    protected $description = 'Lấy mã vận đơn và cập nhật trạng thái từ API DTF bằng internal_id';

    public function handle()
    {
        // Lấy các đơn hàng DTF thiếu mã vận đơn hoặc có phương thức vận chuyển là tiktok_label với trạng thái processing
        $orders = ExcelOrder::query()
            ->where('warehouse', 'US')
            ->where(function ($query) {
                $query->whereNull('tracking_number')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('shipping_method', 'tiktok_label')
                            ->where('status', ExcelOrder::STATUS_PROCESSED);
                    });
            })
            ->where(function ($query) {
                $query->where('status', ExcelOrder::STATUS_PROCESSED)
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('shipping_method', 'tiktok_label')
                            ->where('status', ExcelOrder::STATUS_PROCESSED);
                    });
            })
            ->join('orders_mapping', 'excel_orders.external_id', '=', 'orders_mapping.external_id')
            ->where('orders_mapping.factory', 'dtf')
            ->select('excel_orders.*', 'orders_mapping.internal_id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Không có đơn hàng DTF nào cần cập nhật mã vận đơn hoặc trạng thái.');
            Log::info('Không tìm thấy đơn hàng DTF nào thiếu mã vận đơn hoặc có phương thức tiktok_label với trạng thái processing');
            return;
        }

        $this->info("Tìm thấy {$orders->count()} đơn hàng DTF cần cập nhật. Bắt đầu xử lý...");
        Log::info("Tìm thấy {$orders->count()} đơn hàng DTF để xử lý", ['order_ids' => $orders->pluck('external_id')->toArray()]);

        // Chia danh sách đơn hàng thành các lô 100 đơn (giới hạn của DTF API)
        $batches = $orders->chunk(100);
        $this->info("Chia thành " . $batches->count() . " lô, mỗi lô tối đa 100 đơn.");

        $dtfService = app(DtfService::class);

        foreach ($batches as $batchIndex => $batchOrders) {
            $this->info("Xử lý lô " . ($batchIndex + 1) . "...");
            try {
                // Lấy tracking number từ API tracking
                $trackingOrders = $dtfService->getOrdersTracking($batchOrders);

                // Lấy status từ API status
                $statusOrders = $dtfService->getOrdersStatus($batchOrders);

                // Kết hợp dữ liệu từ cả hai API
                $combinedOrders = $this->combineTrackingAndStatus($trackingOrders, $statusOrders);

                if (empty($combinedOrders)) {
                    $this->warn("Không có dữ liệu từ DTF cho lô " . ($batchIndex + 1));
                    Log::warning("Không có dữ liệu từ API DTF cho lô " . ($batchIndex + 1));
                    continue;
                }

                // Cập nhật bảng excel_orders
                foreach ($combinedOrders as $apiOrder) {
                    $order = $batchOrders->firstWhere('external_id', $apiOrder['external_id']);
                    if (!$order) {
                        continue;
                    }

                    try {
                        // Chỉ cập nhật trạng thái thành 'Shipped' nếu API trả về 'completed' hoặc 'shipped'
                        $status = ($apiOrder['status'] === 'completed' || $apiOrder['status'] === 'shipped') ? 'Shipped' : $order->status;
                        $trackingNumber = $apiOrder['tracking_number'] ?? null;

                        // Cập nhật mã vận đơn nếu có, hoặc chỉ cập nhật trạng thái nếu là tiktok_label
                        if ($trackingNumber || $order->shipping_method === 'tiktok_label' || $status === 'Shipped') {
                            $order->updateTrackingAndStatus($trackingNumber, $status);

                            if ($trackingNumber) {
                                $this->info("Cập nhật mã vận đơn {$trackingNumber} cho đơn hàng {$order->external_id}");
                                Log::info("Cập nhật mã vận đơn cho đơn hàng {$order->external_id}", [
                                    'tracking_number' => $trackingNumber,
                                    'status' => $status
                                ]);
                            } else {
                                $this->info("Không có mã vận đơn, chỉ cập nhật trạng thái cho đơn hàng {$order->external_id}");
                                Log::info("Không có mã vận đơn, cập nhật trạng thái cho đơn hàng {$order->external_id}", [
                                    'status' => $status
                                ]);
                            }

                            if ($status === 'Shipped') {
                                $this->info("Cập nhật trạng thái Đã giao hàng cho đơn hàng {$order->external_id}");
                            }
                        } else {
                            $this->warn("Không cập nhật đơn hàng {$order->external_id} - status: {$apiOrder['status']}, tracking: {$trackingNumber}");
                            Log::info("Không cập nhật đơn hàng {$order->external_id}", [
                                'api_status' => $apiOrder['status'],
                                'tracking_number' => $trackingNumber,
                                'current_status' => $order->status
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error("Lỗi khi cập nhật đơn hàng {$order->external_id}: " . $e->getMessage());
                        $this->error("Lỗi khi xử lý đơn hàng {$order->external_id}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Lỗi khi xử lý lô " . ($batchIndex + 1) . ": " . $e->getMessage());
                $this->error("Lỗi khi xử lý lô " . ($batchIndex + 1));
            }
        }

        $this->info('Hoàn tất cập nhật mã vận đơn và trạng thái cho DTF.');
    }

    /**
     * Kết hợp dữ liệu từ API tracking và status
     */
    private function combineTrackingAndStatus($trackingOrders, $statusOrders)
    {
        $combined = [];

        // Tạo mapping từ external_id sang tracking data
        $trackingMap = collect($trackingOrders)->keyBy('external_id')->toArray();

        // Tạo mapping từ external_id sang status data
        $statusMap = collect($statusOrders)->keyBy('external_id')->toArray();

        // Kết hợp dữ liệu
        $allExternalIds = array_unique(array_merge(
            array_keys($trackingMap),
            array_keys($statusMap)
        ));

        foreach ($allExternalIds as $externalId) {
            $trackingData = $trackingMap[$externalId] ?? [];
            $statusData = $statusMap[$externalId] ?? [];

            $combined[] = [
                'external_id' => $externalId,
                'internal_id' => $trackingData['internal_id'] ?? $statusData['internal_id'] ?? null,
                'tracking_number' => $trackingData['tracking_number'] ?? null,
                'status' => $statusData['status'] ?? $trackingData['status'] ?? null,
            ];
        }

        return $combined;
    }
}
