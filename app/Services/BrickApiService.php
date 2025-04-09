<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrickApiService
{
    private $apiUrl;
    private $appId;
    private $secretKey;

    public function __construct()
    {
        $this->apiUrl = config('services.twofifteen.api_url');
        $this->appId = config('services.twofifteen.app_id');
        $this->secretKey = config('services.twofifteen.secret_key');
    }

    public function sendOrder($orderData, $orderId)
    {
        try {
            // Tính signature đúng cách: sha1(request body + secret key)
            $jsonBody = json_encode($orderData);
            $signature = sha1($jsonBody . $this->secretKey);

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];

            $parameters = [
                'AppId' => $this->appId,
                'Signature' => $signature
            ];

            // Log request details để debug
            Log::info('Brick API Request:', [
                'url' => $this->apiUrl . '/orders.php',
                'headers' => $headers,
                'parameters' => $parameters,
                'body' => $orderData
            ]);

            // Gửi request với query parameters
            $response = Http::withHeaders($headers)
                ->withQueryParameters($parameters)
                ->post($this->apiUrl . '/orders.php', $orderData);

            // Log response để debug
            Log::info('Brick API Response:', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'json' => $response->json()
            ]);

            if ($response->successful()) {
                // Cập nhật status thành 'processed' nếu thành công
                \App\Models\ExcelOrder::where('id', $orderId)
                    ->update(['status' => 'processed']);

                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            // Cập nhật status thành 'failed' nếu thất bại
            \App\Models\ExcelOrder::where('id', $orderId)
                ->update(['status' => 'failed']);

            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            // Cập nhật status thành 'failed' nếu có lỗi
            \App\Models\ExcelOrder::where('id', $orderId)
                ->update(['status' => 'failed']);

            Log::error('Brick API Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getOrders(array $params = [])
    {
        try {
            $queryParams = array_merge([
                'AppId' => $this->appId,
                'page' => 1,
                'limit' => 50,
                'format' => 'JSON'
            ], $params);

            // Tạo query string không bao gồm Signature
            $queryString = http_build_query($queryParams);

            // Tính signature: sha1(query string + secret key)
            $signature = sha1($queryString . $this->secretKey);

            // Thêm signature vào query parameters
            $queryParams['Signature'] = $signature;

            // Log request details
            Log::info('Brick API Request:', [
                'url' => $this->apiUrl . '/orders.php',
                'query_params' => $queryParams
            ]);

            $response = Http::get($this->apiUrl . '/orders.php?' . http_build_query($queryParams));

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Unknown error'
            ];
        } catch (\Exception $e) {
            Log::error('Brick API getOrders error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
