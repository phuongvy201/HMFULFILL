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
            $this->info('Không có đơn hàng UK nào cần cập nhật mã vận đơn hoặc trạng thái.');
            Log::info('Không tìm thấy đơn hàng UK nào thiếu mã vận đơn hoặc có phương thức tiktok_label với trạng thái processing, warehouse=UK, và có ánh xạ Twofifteen trong orders_mapping');
            return;
        }

        $this->info("Tìm thấy {$orders->count()} đơn hàng UK cần cập nhật. Bắt đầu xử lý...");
        Log::info("Tìm thấy {$orders->count()} đơn hàng UK để xử lý", ['internal_ids' => $orders->values()->toArray()]);

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
                            // Chỉ cập nhật trạng thái thành 'Đã giao hàng' nếu API trả về 'Shipped'
                            $status = ($apiOrder['status'] === 'Shipped') ? 'Shipped' : $order->status;
                            $trackingNumber = $apiOrder['trackingNumber'] ?? null;

                            // Cập nhật mã vận đơn nếu có, hoặc chỉ cập nhật trạng thái nếu là tiktok_label
                            if ($trackingNumber || $order->shipping_method === 'tiktok_label') {
                                $order->updateTrackingAndStatus($trackingNumber, $status);

                                if ($trackingNumber) {
                                    $this->info("Cập nhật mã vận đơn {$trackingNumber} cho đơn hàng UK {$externalId}");
                                    Log::info("Cập nhật mã vận đơn cho đơn hàng UK {$externalId}", [
                                        'tracking_number' => $trackingNumber,
                                        'status' => $status
                                    ]);
                                } else {
                                    $this->info("Không có mã vận đơn, chỉ cập nhật trạng thái cho đơn hàng UK {$externalId} (tiktok_label)");
                                    Log::info("Không có mã vận đơn, cập nhật trạng thái cho đơn hàng UK {$externalId} (tiktok_label)", [
                                        'status' => $status
                                    ]);
                                }

                                if ($status === 'Shipped') {
                                    $this->info("Cập nhật trạng thái Đã giao hàng cho đơn hàng UK {$externalId}");
                                }
                            } else {
                                $this->warn("Không có mã vận đơn và không phải tiktok_label cho đơn hàng UK {$externalId}");
                                Log::warning("Không có mã vận đơn và không phải tiktok_label cho đơn hàng UK {$externalId}");
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

        // Lấy các đơn hàng US thiếu mã vận đơn hoặc có trạng thái processed và có ánh xạ trong orders_mapping
        $orders = ExcelOrder::query()
            ->where('warehouse', 'US')
            ->where(function ($query) {
                $query->whereNull('tracking_number')
                    ->orWhere('status', ExcelOrder::STATUS_PROCESSED);
            })
            ->where('status', ExcelOrder::STATUS_PROCESSED)
            ->join('orders_mapping', 'excel_orders.external_id', '=', 'orders_mapping.external_id')
            ->where('orders_mapping.factory', 'dtf') // Chỉ lấy ánh xạ của DTF
            ->select('excel_orders.id', 'excel_orders.external_id', 'orders_mapping.internal_id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Không có đơn hàng US nào cần cập nhật mã vận đơn hoặc trạng thái.');
            Log::info('Không tìm thấy đơn hàng US nào thiếu mã vận đơn hoặc có trạng thái processed, warehouse=US, và có ánh xạ DTF trong orders_mapping');
            return;
        }

        $this->info("Tìm thấy {$orders->count()} đơn hàng US cần cập nhật. Bắt đầu xử lý...");
        Log::info("Tìm thấy {$orders->count()} đơn hàng US để xử lý", ['orders' => $orders->toArray()]);

        $dtfService = app(DtfService::class);

        // Chia danh sách orders thành các lô 100 đơn
        $batches = $orders->chunk(100);
        $this->info("Chia thành " . count($batches) . " lô, mỗi lô tối đa 100 đơn.");

        foreach ($batches as $batchIndex => $batchOrders) {
            $this->info("Xử lý lô US " . ($batchIndex + 1) . "...");
            try {
                // Lấy trạng thái từ API DTF
                $statusOrders = $dtfService->getOrdersStatus($batchOrders);

                // Lấy tracking number từ API DTF
                $trackingOrders = $dtfService->getOrdersTracking($batchOrders);

                // Kết hợp dữ liệu từ cả hai API
                $combinedOrders = [];

                // Tạo mapping từ internal_id sang dữ liệu tracking
                $trackingMap = collect($trackingOrders)->keyBy('internal_id')->toArray();

                foreach ($statusOrders as $statusOrder) {
                    $internalId = $statusOrder['internal_id'];
                    $trackingData = $trackingMap[$internalId] ?? [];

                    $combinedOrders[] = [
                        'internal_id' => $internalId,
                        'external_id' => $statusOrder['external_id'],
                        'status' => $statusOrder['status'],
                        'tracking_number' => $trackingData['tracking_number'] ?? null,
                    ];
                }

                if (empty($combinedOrders)) {
                    $this->warn("Không có dữ liệu từ DTF cho lô " . ($batchIndex + 1));
                    Log::warning("Không có dữ liệu từ API DTF cho lô " . ($batchIndex + 1));
                    continue;
                }

                // Cập nhật bảng excel_orders
                foreach ($combinedOrders as $apiOrder) {
                    if (!$apiOrder['internal_id']) {
                        continue;
                    }

                    try {
                        $order = ExcelOrder::where('external_id', $apiOrder['external_id'])->first();
                        if ($order) {
                            $orderId = $order->id;
                            $externalId = $apiOrder['external_id'];

                            // Chỉ cập nhật trạng thái thành 'Shipped' nếu API trả về 'completed'
                            $status = ($apiOrder['status'] === 'completed') ? 'Shipped' : $order->status;
                            $trackingNumber = $apiOrder['tracking_number'];

                            // Cập nhật mã vận đơn nếu có, hoặc chỉ cập nhật trạng thái
                            if ($trackingNumber && $trackingNumber !== 'No shipment') {
                                $order->updateTrackingAndStatus($trackingNumber, $status);

                                $this->info("Cập nhật mã vận đơn {$trackingNumber} cho đơn hàng US {$externalId}");
                                Log::info("Cập nhật mã vận đơn cho đơn hàng US {$externalId}", [
                                    'tracking_number' => $trackingNumber,
                                    'status' => $status
                                ]);
                            } else {
                                // Chỉ cập nhật trạng thái nếu không có tracking number
                                $order->updateTrackingAndStatus(null, $status);

                                $this->info("Không có mã vận đơn, chỉ cập nhật trạng thái cho đơn hàng US {$externalId}");
                                Log::info("Không có mã vận đơn, cập nhật trạng thái cho đơn hàng US {$externalId}", [
                                    'status' => $status
                                ]);
                            }

                            if ($status === 'Shipped') {
                                $this->info("Cập nhật trạng thái Đã giao hàng cho đơn hàng US {$externalId}");
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
