<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DtfService
{
    /**
     * Lấy tracking number và status cho đơn hàng DTF
     */
    public function getOrdersTracking($orders)
    {
        $config = [
            'apiUrl' => config('services.dtf.api_url'),
            'bearerToken' => config('services.dtf.bearer_token'),
        ];

        if (empty($config['apiUrl']) || empty($config['bearerToken'])) {
            Log::error('Thiếu cấu hình API DTF');
            throw new \Exception('Cấu hình API DTF không đầy đủ');
        }

        // Lấy danh sách internal_id từ orders
        $internalIds = $orders->pluck('internal_id')->filter()->toArray();

        if (empty($internalIds)) {
            Log::warning('Không tìm thấy internal_ids cho đơn hàng DTF');
            return [];
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $config['bearerToken']
        ];

        // Tạo query string với ids
        $idsParam = implode(',', $internalIds);
        $url = $config['apiUrl'] . '/api/orders?ids=' . $idsParam;

        $response = Http::withHeaders($headers)->get($url);

 

        if ($response->successful()) {
            $data = $response->json();
            $apiOrders = $data; // Dữ liệu trả về là mảng đơn hàng trực tiếp

            // Tạo mapping từ internal_id sang external_id
            $orderMapping = $orders->pluck('external_id', 'internal_id')->toArray();

            return array_map(function ($orderData) use ($orderMapping) {
                $internalId = $orderData['id'] ?? null;
                $externalId = $orderMapping[$internalId] ?? null;

                return [
                    'external_id' => $externalId,
                    'internal_id' => $internalId,
                    'tracking_number' => $orderData['tracking_number'] ?? null,
                    'status' => $orderData['status'] ?? null,
                ];
            }, $apiOrders);
        }

        Log::warning('Không thể lấy dữ liệu từ API DTF', [
            'url' => $url,
            'status' => $response->status(),
            'response' => $response->json()
        ]);
        return [];
    }
}
