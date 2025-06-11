<?php

namespace App\Console\Commands;

use App\Models\ExcelOrder;
use App\Services\TwofifteenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateTrackingNumbers extends Command
{
    protected $signature = 'orders:update-tracking-numbers';
    protected $description = 'Lấy mã vận đơn và cập nhật trạng thái Đã giao hàng từ API Twofifteen bằng internal_id vào bảng excel_orders';

    public function handle()
    {
        // Lấy các đơn hàng thiếu mã vận đơn hoặc có phương thức vận chuyển là tiktok_label với trạng thái processing và có ánh xạ trong orders_mapping
        $orders = ExcelOrder::query()
            ->where('warehouse', 'UK')
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
            ->where('orders_mapping.factory', 'twofifteen') // Chỉ lấy ánh xạ của Twofifteen
            ->select('excel_orders.id', 'excel_orders.external_id', 'orders_mapping.internal_id')
            ->pluck('internal_id', 'excel_orders.id');

        if ($orders->isEmpty()) {
            $this->info('Không có đơn hàng nào cần cập nhật mã vận đơn hoặc trạng thái.');
            Log::info('Không tìm thấy đơn hàng nào thiếu mã vận đơn hoặc có phương thức tiktok_label với trạng thái processing, warehouse=UK, và có ánh xạ Twofifteen trong orders_mapping');
            return;
        }

        $this->info("Tìm thấy {$orders->count()} đơn hàng cần cập nhật. Bắt đầu xử lý...");
        Log::info("Tìm thấy {$orders->count()} đơn hàng để xử lý", ['internal_ids' => $orders->values()->toArray()]);

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
            $this->info("Xử lý lô " . ($batchIndex + 1) . "...");
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
                            // Chỉ cập nhật trạng thái thành 'Đã giao hàng' nếu API trả về 'Shipped'
                            $status = ($apiOrder['status'] === 'Shipped') ? 'Shipped' : $order->status;
                            $trackingNumber = $apiOrder['trackingNumber'] ?? null;

                            // Cập nhật mã vận đơn nếu có, hoặc chỉ cập nhật trạng thái nếu là tiktok_label
                            if ($trackingNumber || $order->shipping_method === 'tiktok_label') {
                                $order->updateTrackingAndStatus($trackingNumber, $status);

                                if ($trackingNumber) {
                                    $this->info("Cập nhật mã vận đơn {$trackingNumber} cho đơn hàng {$externalId}");
                                    Log::info("Cập nhật mã vận đơn cho đơn hàng {$externalId}", [
                                        'tracking_number' => $trackingNumber,
                                        'status' => $status
                                    ]);
                                } else {
                                    $this->info("Không có mã vận đơn, chỉ cập nhật trạng thái cho đơn hàng {$externalId} (tiktok_label)");
                                    Log::info("Không có mã vận đơn, cập nhật trạng thái cho đơn hàng {$externalId} (tiktok_label)", [
                                        'status' => $status
                                    ]);
                                }

                                if ($status === 'Shipped') {
                                    $this->info("Cập nhật trạng thái Đã giao hàng cho đơn hàng {$externalId}");
                                }
                            } else {
                                $this->warn("Không có mã vận đơn và không phải tiktok_label cho đơn hàng {$externalId}");
                                Log::warning("Không có mã vận đơn và không phải tiktok_label cho đơn hàng {$externalId}");
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Lỗi khi cập nhật đơn hàng {$externalId}: " . $e->getMessage());
                        $this->error("Lỗi khi xử lý đơn hàng {$externalId}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Lỗi khi xử lý lô " . ($batchIndex + 1) . ": " . $e->getMessage());
                $this->error("Lỗi khi xử lý lô " . ($batchIndex + 1));
            }
        }

        $this->info('Hoàn tất cập nhật mã vận đơn và trạng thái.');
    }
}
