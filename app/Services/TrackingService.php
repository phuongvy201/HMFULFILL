<?php

namespace App\Services;

use App\Models\ExcelOrder;
use App\Models\OrderMapping;
use Illuminate\Support\Facades\Log;

class TrackingService
{
    protected $twofifteenService;
    protected $dtfService;

    public function __construct(TwofifteenService $twofifteenService, DtfService $dtfService)
    {
        $this->twofifteenService = $twofifteenService;
        $this->dtfService = $dtfService;
    }

    /**
     * Lấy tracking number cho tất cả đơn hàng cần thiết
     */
    public function updateAllTrackingNumbers()
    {
        Log::info('Bắt đầu cập nhật tracking numbers cho tất cả đơn hàng');

        // Cập nhật tracking cho đơn hàng UK (Twofifteen)
        $this->updateTwofifteenTracking();

        // Cập nhật tracking cho đơn hàng US (DTF)
        $this->updateDtfTracking();

        Log::info('Hoàn tất cập nhật tracking numbers');
    }

    /**
     * Cập nhật tracking cho đơn hàng UK (Twofifteen)
     */
    public function updateTwofifteenTracking()
    {
        Log::info('Bắt đầu cập nhật tracking cho đơn hàng UK (Twofifteen)');

        // Lấy các đơn hàng UK đã processed nhưng chưa có tracking number
        $orders = ExcelOrder::where('warehouse', 'UK')
            ->where('status', ExcelOrder::STATUS_PROCESSED)
            ->whereNull('tracking_number')
            ->whereHas('orderMapping', function ($query) {
                $query->where('factory', 'twofifteen');
            })
            ->with('orderMapping')
            ->get();

        if ($orders->isEmpty()) {
            Log::info('Không có đơn hàng UK nào cần cập nhật tracking');
            return;
        }

        Log::info("Tìm thấy {$orders->count()} đơn hàng UK cần cập nhật tracking");

        // Nhóm theo internal_id để gọi API
        $internalIds = $orders->pluck('orderMapping.internal_id')->filter()->toArray();

        if (empty($internalIds)) {
            Log::warning('Không tìm thấy internal_ids cho đơn hàng UK');
            return;
        }

        // Chia thành các lô 100 để gọi API
        $batches = array_chunk($internalIds, 100);

        foreach ($batches as $batchIndex => $batchInternalIds) {
            try {
                $apiOrders = $this->twofifteenService->getOrdersByInternalIds($batchInternalIds);

                if (empty($apiOrders)) {
                    Log::warning("Không có dữ liệu từ Twofifteen cho lô " . ($batchIndex + 1));
                    continue;
                }

                $this->processTwofifteenApiResponse($apiOrders, $orders);
            } catch (\Exception $e) {
                Log::error("Lỗi khi xử lý lô UK " . ($batchIndex + 1) . ": " . $e->getMessage());
            }
        }
    }

    /**
     * Cập nhật tracking cho đơn hàng US (DTF)
     */
    public function updateDtfTracking()
    {
        Log::info('Bắt đầu cập nhật tracking cho đơn hàng US (DTF)');

        // Lấy các đơn hàng US đã processed nhưng chưa có tracking number
        $orders = ExcelOrder::where('warehouse', 'US')
            ->where('status', ExcelOrder::STATUS_PROCESSED)
            ->whereNull('tracking_number')
            ->whereHas('orderMapping', function ($query) {
                $query->where('factory', 'dtf');
            })
            ->with('orderMapping')
            ->get();

        if ($orders->isEmpty()) {
            Log::info('Không có đơn hàng US nào cần cập nhật tracking');
            return;
        }

        Log::info("Tìm thấy {$orders->count()} đơn hàng US cần cập nhật tracking");

        // Chia thành các lô 100 để gọi API
        $batches = $orders->chunk(100);

        foreach ($batches as $batchIndex => $batchOrders) {
            try {
                $apiOrders = $this->dtfService->getOrdersTracking($batchOrders);

                if (empty($apiOrders)) {
                    Log::warning("Không có dữ liệu từ DTF cho lô " . ($batchIndex + 1));
                    continue;
                }

                $this->processDtfApiResponse($apiOrders);
            } catch (\Exception $e) {
                Log::error("Lỗi khi xử lý lô US " . ($batchIndex + 1) . ": " . $e->getMessage());
            }
        }
    }

    /**
     * Xử lý response từ API Twofifteen
     */
    private function processTwofifteenApiResponse($apiOrders, $orders)
    {
        foreach ($apiOrders as $apiOrder) {
            if (!$apiOrder['internal_id'] || !$apiOrder['trackingNumber']) {
                continue;
            }

            // Tìm order tương ứng
            $order = $orders->first(function ($order) use ($apiOrder) {
                return $order->orderMapping && $order->orderMapping->internal_id == $apiOrder['internal_id'];
            });

            if ($order) {
                try {
                    $order->updateTrackingAndStatus($apiOrder['trackingNumber'], $order->status);

                    Log::info("Đã cập nhật tracking number cho đơn hàng UK", [
                        'external_id' => $order->external_id,
                        'tracking_number' => $apiOrder['trackingNumber'],
                        'internal_id' => $apiOrder['internal_id']
                    ]);
                } catch (\Exception $e) {
                    Log::error("Lỗi khi cập nhật tracking cho đơn hàng UK {$order->external_id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Xử lý response từ API DTF
     */
    private function processDtfApiResponse($apiOrders)
    {
        foreach ($apiOrders as $apiOrder) {
            if (!$apiOrder['external_id'] || !$apiOrder['tracking_number']) {
                continue;
            }

            try {
                $order = ExcelOrder::where('external_id', $apiOrder['external_id'])->first();

                if ($order) {
                    $order->updateTrackingAndStatus($apiOrder['tracking_number'], $order->status);

                    Log::info("Đã cập nhật tracking number cho đơn hàng US", [
                        'external_id' => $order->external_id,
                        'tracking_number' => $apiOrder['tracking_number'],
                        'internal_id' => $apiOrder['internal_id']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Lỗi khi cập nhật tracking cho đơn hàng US {$apiOrder['external_id']}: " . $e->getMessage());
            }
        }
    }

    /**
     * Lấy tracking number cho một đơn hàng cụ thể
     */
    public function updateTrackingForOrder($externalId)
    {
        $order = ExcelOrder::where('external_id', $externalId)->with('orderMapping')->first();

        if (!$order) {
            Log::warning("Không tìm thấy đơn hàng với external_id: {$externalId}");
            return false;
        }

        if (!$order->orderMapping) {
            Log::warning("Không tìm thấy mapping cho đơn hàng: {$externalId}");
            return false;
        }

        try {
            if ($order->warehouse === 'UK' && $order->orderMapping->factory === 'twofifteen') {
                return $this->updateSingleTwofifteenOrder($order);
            } elseif ($order->warehouse === 'US' && $order->orderMapping->factory === 'dtf') {
                return $this->updateSingleDtfOrder($order);
            } else {
                Log::warning("Không hỗ trợ factory cho đơn hàng: {$externalId}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật tracking cho đơn hàng {$externalId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật tracking cho một đơn hàng Twofifteen
     */
    private function updateSingleTwofifteenOrder($order)
    {
        $apiOrders = $this->twofifteenService->getOrdersByInternalIds([$order->orderMapping->internal_id]);

        if (empty($apiOrders)) {
            Log::warning("Không có dữ liệu từ Twofifteen cho đơn hàng: {$order->external_id}");
            return false;
        }

        $apiOrder = $apiOrders[0] ?? null;
        if ($apiOrder && $apiOrder['trackingNumber']) {
            $order->updateTrackingAndStatus($apiOrder['trackingNumber'], $order->status);

            Log::info("Đã cập nhật tracking number cho đơn hàng UK", [
                'external_id' => $order->external_id,
                'tracking_number' => $apiOrder['trackingNumber']
            ]);
            return true;
        }

        return false;
    }

    /**
     * Cập nhật tracking cho một đơn hàng DTF
     */
    private function updateSingleDtfOrder($order)
    {
        $apiOrders = $this->dtfService->getOrdersTracking(collect([$order]));

        if (empty($apiOrders)) {
            Log::warning("Không có dữ liệu từ DTF cho đơn hàng: {$order->external_id}");
            return false;
        }

        $apiOrder = $apiOrders[0] ?? null;
        if ($apiOrder && $apiOrder['tracking_number']) {
            $order->updateTrackingAndStatus($apiOrder['tracking_number'], $order->status);

            Log::info("Đã cập nhật tracking number cho đơn hàng US", [
                'external_id' => $order->external_id,
                'tracking_number' => $apiOrder['tracking_number']
            ]);
            return true;
        }

        return false;
    }
}
