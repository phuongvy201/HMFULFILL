<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwofifteenService
{
    public function getOrdersByInternalIds(array $internalIds)
    {
        $config = [
            'appId'     => config('services.twofifteen.app_id'),
            'secretKey' => config('services.twofifteen.secret_key'),
            'apiUrl'    => config('services.twofifteen.api_url'),
        ];

        if (empty($config['appId']) || empty($config['secretKey']) || empty($config['apiUrl'])) {
            Log::error('Missing Twofifteen API configuration');
            throw new \Exception('Cấu hình API Twofifteen không đầy đủ');
        }

        $params = [
            'AppId'  => $config['appId'],
            'ids'    => implode(',', $internalIds),
            'format' => 'JSON',
            'sort'   => 'created_at',
            'order'  => 'desc',
            'page'   => 1,
            'limit' => count($internalIds),
        ];
        $params['Signature'] = sha1(http_build_query($params) . $config['secretKey']);
        $url = "{$config['apiUrl']}/orders.php?" . http_build_query($params);

        $response = Http::get($url);
        Log::info('Twofifteen API response', ['url' => $url, 'response' => $response->json()]);

        if ($response->successful()) {
            $data = $response->json();
            $orders = data_get($data, 'orders', []);

            return array_map(function ($orderData) {
                // Response mẫu có dạng {"order": {...}}, lấy dữ liệu từ order
                $order = $orderData['order'] ?? $orderData;
                return [
                    'internal_id' => $order['id'] ?? null,
                    'external_id' => $order['external_id'] ?? null,
                    'trackingNumber' => data_get($order, 'shipping.trackingNumber', data_get($order, 'fulfillments.0.trackingNo', null)),
                    'status' => data_get($order, 'status', null),
                ];
            }, $orders);
        }

        Log::warning('Không thể lấy dữ liệu từ Twofifteen API', ['url' => $url, 'status' => $response->status()]);
        return [];
    }
}
