<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DtfService
{
    public function getOrdersStatus($orders)
    {
        $config = [
            'apiUrl' => config('services.dtf.api_url'),
            'bearerToken' => config('services.dtf.bearer_token'),
        ];

        if (empty($config['apiUrl']) || empty($config['bearerToken'])) {
            Log::error('Missing DTF API configuration');
            throw new \Exception('Cấu hình API DTF không đầy đủ');
        }

        // Lấy danh sách internal_id (order_id của DTF) từ orders
        $internalIds = $orders->pluck('internal_id')->filter()->toArray();

        if (empty($internalIds)) {
            Log::warning('No internal_ids found for DTF orders');
            return [];
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $config['bearerToken']
        ];

        $response = Http::withHeaders($headers)
            ->post($config['apiUrl'] . '/api/orders/status', $internalIds);

        Log::info('DTF API status response', [
            'url' => $config['apiUrl'] . '/api/orders/status',
            'internal_ids_count' => count($internalIds),
            'internal_ids' => $internalIds,
            'response_status' => $response->status(),
            'response' => $response->json()
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $apiOrders = data_get($data, 'orders', []);

            // Tạo mapping từ internal_id sang external_id để dễ xử lý
            $orderMapping = $orders->pluck('external_id', 'internal_id')->toArray();

            return array_map(function ($orderData) use ($orderMapping) {
                $internalId = $orderData['order_id'] ?? null;
                $externalId = $orderMapping[$internalId] ?? null;

                return [
                    'external_id' => $externalId,
                    'internal_id' => $internalId,
                    'tracking_number' => null, // API status không trả về tracking number
                    'status' => $orderData['status'] ?? null,
                ];
            }, $apiOrders);
        }

        Log::warning('Không thể lấy dữ liệu từ DTF API status', [
            'url' => $config['apiUrl'] . '/api/orders/status',
            'status' => $response->status(),
            'response' => $response->json()
        ]);
        return [];
    }

    /**
     * Lấy tracking number cho đơn hàng DTF
     */
    public function getOrdersTracking($orders)
    {
        $config = [
            'apiUrl' => config('services.dtf.api_url'),
            'bearerToken' => config('services.dtf.bearer_token'),
        ];

        if (empty($config['apiUrl']) || empty($config['bearerToken'])) {
            Log::error('Missing DTF API configuration');
            throw new \Exception('Cấu hình API DTF không đầy đủ');
        }

        // Lấy danh sách internal_id từ orders
        $internalIds = $orders->pluck('internal_id')->filter()->toArray();

        if (empty($internalIds)) {
            Log::warning('No internal_ids found for DTF orders tracking');
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

        Log::info('DTF API tracking response', [
            'url' => $url,
            'internal_ids_count' => count($internalIds),
            'internal_ids' => $internalIds,
            'response_status' => $response->status(),
            'response' => $response->json()
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $apiOrders = data_get($data, 'orders', []);

            // Tạo mapping từ internal_id sang external_id để dễ xử lý
            $orderMapping = $orders->pluck('external_id', 'internal_id')->toArray();

            return array_map(function ($orderData) use ($orderMapping) {
                $internalId = $orderData['id'] ?? null;
                $externalId = $orderMapping[$internalId] ?? null;

                return [
                    'external_id' => $externalId,
                    'internal_id' => $internalId,
                    'tracking_number' => data_get($orderData, 'tracking_number', data_get($orderData, 'shipping.tracking_number', null)),
                    'status' => data_get($orderData, 'status', null),
                ];
            }, $apiOrders);
        }

        Log::warning('Không thể lấy tracking data từ DTF API', [
            'url' => $url,
            'status' => $response->status(),
            'response' => $response->json()
        ]);
        return [];
    }
}
