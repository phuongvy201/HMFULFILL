<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

            // Log chi tiết lỗi từ API
            Log::error('Brick API Error Response:', [
                'order_id' => $orderId,
                'status_code' => $response->status(),
                'error_response' => $response->json() ?? $response->body(),
                'request_data' => [
                    'url' => $this->apiUrl . '/orders.php',
                    'headers' => $headers,
                    'parameters' => $parameters,
                    'body' => $orderData
                ]
            ]);

            // Cập nhật status thành 'failed' và lưu thông tin lỗi
            \App\Models\ExcelOrder::where('id', $orderId)
                ->update([
                    'status' => 'failed',
                    'api_response' => $response->json() ?? $response->body()
                ]);

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Unknown error'
            ];
        } catch (\Exception $e) {
            // Log chi tiết lỗi exception
            Log::error('Brick API Exception:', [
                'order_id' => $orderId,
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ],
                'request_data' => $orderData
            ]);

            // Cập nhật status thành 'failed' và lưu thông tin lỗi
            \App\Models\ExcelOrder::where('id', $orderId)
                ->update([
                    'status' => 'failed',
                    'api_response' => $e->getMessage()
                ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_details' => [
                    'exception' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function getOrders(array $params = []): array
    {
        try {
            // Xác thực tham số đầu vào và thêm sắp xếp theo thời gian gần nhất
            $createdAtMax = Carbon::now()->toIso8601String();
            $createdAtMin = Carbon::now()->subDays(5)->toIso8601String();

            $queryParams = array_merge([
                'AppId' => $this->appId,
                'page' => max(1, (int)($params['page'] ?? 1)),
                'limit' => min(1000, max(1, (int)($params['limit'] ?? 1000))),
                'format' => 'JSON',
                'sort' => 'created_at',
                'order' => 'desc',
                'created_at_min' => $createdAtMin,
                'created_at_max' => $createdAtMax
            ], $params);

            // Tạo chuỗi truy vấn không bao gồm Signature
            $queryString = http_build_query($queryParams);
            $queryParams['Signature'] = sha1($queryString . $this->secretKey);

            // Ghi log yêu cầu (log tối thiểu trong môi trường sản xuất)
            Log::debug('Yêu cầu API Brick:', [
                'url' => $this->apiUrl . '/orders.php',
                'query_params' => array_diff_key($queryParams, ['Signature' => ''])
            ]);

            $response = Http::get($this->apiUrl . '/orders.php?' . http_build_query($queryParams));

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Lỗi API không xác định'
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi gọi API getOrders:', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getOrderDetails($orderId)
    {
        try {
            $queryParams = [
                'id' => $orderId,
                'AppId' => $this->appId
            ];

            // Tạo query string không bao gồm Signature
            $queryString = http_build_query($queryParams);

            // Tính signature: sha1(query string + secret key)
            $signature = sha1($queryString . $this->secretKey);

            // Thêm signature vào query parameters
            $queryParams['Signature'] = $signature;

            // Log request details
            Log::info('Brick API getOrderDetails Request:', [
                'url' => $this->apiUrl . '/order.php',
                'query_params' => $queryParams
            ]);

            $response = Http::get($this->apiUrl . '/order.php?' . http_build_query($queryParams));

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
            Log::error('Brick API getOrderDetails error:', [
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
