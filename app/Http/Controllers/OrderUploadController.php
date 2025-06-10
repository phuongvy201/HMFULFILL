<?php

namespace App\Http\Controllers;

use App\Helpers\GoogleDriveHelper;
use App\Models\ExcelOrder;
use App\Services\BrickApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\UrlHelper;
use App\Models\OrderMapping;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class OrderUploadController extends Controller
{
    // Xóa property này nếu không còn sử dụng
    // private $brickApiService;

    protected $apiServices = [
        'twofifteen' => [
            'apiUrl' => '',
            'appId' => '',
            'secretKey' => '',
        ],
        'prinful' => [
            'apiUrl' => '',
            'appId' => '',
            'secretKey' => '',
        ],
        'dtg' => [
            'apiUrl' => '',
            'appId' => '',
            'secretKey' => '',
        ],
        'lenful' => [
            'apiUrl' => '',
            'appId' => '',
            'secretKey' => '',
        ],
    ];

    public function __construct()
    {
        // Xóa phần khởi tạo BrickApiService nếu có
        $this->apiServices = [
            'twofifteen' => [
                'apiUrl' => config('services.twofifteen.api_url'),
                'appId' => config('services.twofifteen.app_id'),
                'secretKey' => config('services.twofifteen.secret_key'),
            ],
            'prinful' => [
                'apiUrl' => config('services.prinful.api_url'),
                'appId' => config('services.prinful.app_id'),
                'secretKey' => config('services.prinful.secret_key'),
            ],
            'dtg' => [
                'apiUrl' => config('services.dtg.api_url'),
                'appId' => config('services.dtg.app_id'),
                'secretKey' => config('services.dtg.secret_key'),
            ],
            'lenful' => [
                'apiUrl' => config('services.lenful.api_url'),
                'appId' => config('services.lenful.app_id'),
                'secretKey' => config('services.lenful.secret_key'),
            ],
        ];
    }

    private function buildOrderData($order)
    {
        // Kiểm tra dữ liệu đơn hàng
        if ($order->items->isEmpty()) {
            Log::warning("Order {$order->external_id} has no items");
            throw new \Exception("Đơn hàng {$order->external_id} không có items");
        }

        // Kiểm tra tính hợp lệ của items
        foreach ($order->items as $item) {
            if (!$item->quantity || $item->mockups->isEmpty() || $item->designs->isEmpty()) {
                Log::warning("Invalid item in order {$order->external_id}", [
                    'quantity' => $item->quantity,
                    'mockups_count' => $item->mockups->count(),
                    'designs_count' => $item->designs->count(),
                ]);
                throw new \Exception("Item không hợp lệ trong đơn hàng {$order->external_id}");
            }

            // Kiểm tra URL hợp lệ
            foreach ($item->mockups as $mockup) {
                if (!filter_var($mockup->url, FILTER_VALIDATE_URL)) {
                    Log::warning("Invalid mockup URL in order {$order->external_id}", ['url' => $mockup->url]);
                    throw new \Exception("URL mockup không hợp lệ trong đơn hàng {$order->external_id}");
                }
            }
            foreach ($item->designs as $design) {
                if (!filter_var($design->url, FILTER_VALIDATE_URL)) {
                    Log::warning("Invalid design URL in order {$order->external_id}", ['url' => $design->url]);
                    throw new \Exception("URL design không hợp lệ trong đơn hàng {$order->external_id}");
                }
            }
        }

        $orderData = [
            'external_id' => $order->external_id,
            'brand' => $order->brand,
            'channel' => $order->channel,
            'buyer_email' => $order->buyer_email,
            'shipping_address' => [
                'firstName' => $order->first_name,
                'lastName' => $order->last_name,
                'company' => $order->company,
                'address1' => $order->address1,
                'address2' => $order->address2,
                'city' => $order->city,
                'county' => $order->county,
                'postcode' => $order->post_code,
                'country' => $order->country,
                'phone1' => $order->phone1,
                'phone2' => $order->phone2
            ],
            'shipping' => [
                'shippingMethod' => $order->shipping_method ?? null,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'pn' => $item->part_number,
                    'quantity' => (int) $item->quantity,
                    'description' => $item->description,
                    'mockups' => $item->mockups->map(function ($mockup) {
                        return [
                            'title' => $mockup->title,
                            'src' => $mockup->url
                        ];
                    })->toArray(),
                    'designs' => $item->designs->map(function ($design) {
                        return [
                            'title' => $design->title,
                            'src' => $design->url
                        ];
                    })->toArray()
                ];
            })->toArray(),
            'comment' => $order->comment
        ];

        Log::debug("Order Data for {$order->external_id}:", $orderData);
        return $orderData;
    }

    private function buildApiConfig($factory, $data)
    {
        $config = $this->apiServices[$factory] ?? null;
        if (!$config) {
            Log::error("Invalid factory: {$factory}");
            throw new \Exception("Invalid factory: {$factory}");
        }

        // Kiểm tra cấu hình
        if (empty($config['apiUrl']) || empty($config['appId']) || empty($config['secretKey'])) {
            Log::error("Incomplete API config for factory {$factory}", $config);
            throw new \Exception("Cấu hình API không đầy đủ cho factory {$factory}");
        }

        $jsonBody = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("JSON encoding failed for factory {$factory}: " . json_last_error_msg());
            throw new \Exception("Lỗi mã hóa JSON: " . json_last_error_msg());
        }

        $signature = sha1($jsonBody . $config['secretKey']);
        Log::debug("API Config for {$factory}:", [
            'apiUrl' => $config['apiUrl'],
            'appId' => $config['appId'],
            'signature' => $signature
        ]);

        return [
            'config' => $config,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'parameters' => [
                'AppId' => $config['appId'],
                'Signature' => $signature
            ]
        ];
    }

    private function processOrderResponse($order, $response, $factory = null)
    {
        if ($response && $response->successful()) {
            $apiResponse = $response->json();
            $internalId = $apiResponse['order']['id'] ?? $apiResponse['id'] ?? null;

            Log::debug("API Response for order {$order->external_id}:", $apiResponse);

            // Cập nhật trạng thái đơn hàng với full response
            $order->markAsProcessed($apiResponse, $internalId, $factory);

            // Lưu mapping vào OrderMapping
            if ($internalId && $factory) {
                OrderMapping::createOrUpdate(
                    $order->external_id,
                    $internalId,
                    $factory,
                    $apiResponse
                );

                Log::info('Order mapping created:', [
                    'external_id' => $order->external_id,
                    'internal_id' => $internalId,
                    'factory' => $factory
                ]);
            }

            return [
                'order_id' => $order->id,
                'external_id' => $order->external_id,
                'internal_id' => $internalId,
                'factory' => $factory,
                'success' => true,
                'message' => 'Tải lên thành công'
            ];
        } else {
            $errorMessage = $response ? ($response->json()['error'] ?? 'Lỗi không xác định') : 'Yêu cầu thất bại';
            $fullApiResponse = $response ? $response->json() : null;

            // Chỉ lưu thông tin lỗi, không lưu toàn bộ response
            $errorResponse = [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $response ? $response->status() : null,
                'timestamp' => now()->toISOString()
            ];

            Log::error('API Error:', [
                'order_id' => $order->id,
                'external_id' => $order->external_id,
                'factory' => $factory,
                'status_code' => $response ? $response->status() : null,
                'error_response' => $fullApiResponse
            ]);

            // Lưu chỉ error response, không lưu full response
            $order->markAsFailed($errorMessage, $errorResponse);

            return [
                'order_id' => $order->id,
                'external_id' => $order->external_id,
                'success' => false,
                'message' => $errorMessage
            ];
        }
    }

    public function upload(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids');
            $factory = $request->input('factory', 'twofifteen');

            if (empty($orderIds)) {
                Log::warning('No order IDs provided in request');
                return response()->json([
                    'success' => false,
                    'message' => 'Không có đơn hàng nào được chọn'
                ], 400);
            }

            // Thêm log để kiểm tra order IDs
            Log::info('Processing orders', ['order_ids' => $orderIds, 'factory' => $factory]);

            $orders = ExcelOrder::with(['items.mockups', 'items.designs'])
                ->whereIn('id', $orderIds)
                ->get();

            if ($orders->isEmpty()) {
                Log::warning('No valid orders found for IDs:', $orderIds);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng hợp lệ'
                ], 400);
            }

            // Log chi tiết về các đơn hàng tìm thấy
            Log::info('Found orders for processing', [
                'orders_count' => $orders->count(),
                'orders_summary' => $orders->map(function($order) {
                    return [
                        'id' => $order->id,
                        'external_id' => $order->external_id,
                        'items_count' => $order->items->count(),
                        'items_loaded' => $order->relationLoaded('items'),
                        'mockups_designs_loaded' => $order->items->every(function($item) {
                            return $item->relationLoaded('mockups') && $item->relationLoaded('designs');
                        })
                    ];
                })->toArray()
            ]);

            // Kiểm tra dữ liệu quan hệ
            foreach ($orders as $order) {
                if (!$order->relationLoaded('items') || !$order->items->every->relationLoaded('mockups') || !$order->items->every->relationLoaded('designs')) {
                    Log::warning("Incomplete data for order {$order->external_id}");
                    return response()->json([
                        'success' => false,
                        'message' => "Dữ liệu không đầy đủ cho đơn hàng {$order->external_id}"
                    ], 400);
                }
            }

            $results = [];
            foreach ($orders as $order) {
                try {
                    $orderData = $this->buildOrderData($order);
                    $apiConfig = $this->buildApiConfig($factory, $orderData);

                    $response = Http::withHeaders($apiConfig['headers'])
                        ->withQueryParameters($apiConfig['parameters'])
                        ->post($apiConfig['config']['apiUrl'] . '/orders.php', $orderData);

                    $results[] = $this->processOrderResponse($order, $response, $factory);
                } catch (\Exception $e) {
                    Log::error("Error processing order {$order->external_id}:", [
                        'message' => $e->getMessage()
                    ]);
                    $results[] = [
                        'order_id' => $order->id,
                        'external_id' => $order->external_id,
                        'success' => false,
                        'message' => "Lỗi xử lý đơn hàng {$order->external_id}: " . $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Xử lý đơn hàng thành công',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Order upload error:', [
                'message' => $e->getMessage(),
                'order_ids' => $request->input('order_ids')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xử lý đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    // public function testOrder()
    // {
    //     try {
    //         $testData = [
    //             "external_id" => "TEST-002",
    //             "brand" => "HM Fulfill",
    //             "channel" => "site",
    //             "buyer_email" => "test@example.com",
    //             "shipping_address" => [
    //                 "firstName" => "John",
    //                 "lastName" => "Doe",
    //                 "company" => "Test Company",
    //                 "address1" => "123 Test St",
    //                 "address2" => "Suite 1",
    //                 "city" => "London",
    //                 "county" => "Greater London",
    //                 "postcode" => "SW1A 1AA",
    //                 "country" => "UK",
    //                 "phone1" => "02012345678",
    //                 "phone2" => ""
    //             ],
    //             "items" => [
    //                 [
    //                     "pn" => "BY003-WH-S",

    //                     "quantity" => 1,
    //                     "description" => "Test product description",

    //                     "mockups" => [
    //                         [
    //                             "title" => "Printing Front Side",
    //                             "src" => "https://www.twofifteen.co.uk/images/svg/mockup-5d3cf2a60e21468f6b5bfbcedeef1e8a.png?v=fd41c3f2"
    //                         ],
    //                         [
    //                             "title" => "Printing Back Side",
    //                             "src" => "https://www.twofifteen.co.uk/images/svg/mockup-5d3cf2a60e21468f6b5bfbcedeef1e8a.png?v=fd41c3f2"
    //                         ]
    //                     ],
    //                     "designs" => [
    //                         [
    //                             "title" => "Printing Front Side",
    //                             "src" => "https://www.twofifteen.co.uk/images/svg/mockup-5d3cf2a60e21468f6b5bfbcedeef1e8a.png?v=fd41c3f2"
    //                         ],
    //                         [
    //                             "title" => "Printing Back Side",
    //                             "src" => "https://www.twofifteen.co.uk/images/svg/mockup-5d3cf2a60e21468f6b5bfbcedeef1e8a.png?v=fd41c3f2"
    //                         ]
    //                     ]
    //                 ]
    //             ],
    //             "comments" => "Test order"
    //         ];

    //         $result = $this->brickApiService->sendOrder($testData, 1);
    //         return response()->json($result);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function index(Request $request)
    {
        try {
            // --- Lấy config cho factory (ví dụ mặc định là twofifteen) ---
            $factory = $request->input('factory', 'twofifteen');
            $config = $this->apiServices[$factory] ?? null;
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factory không hợp lệ'
                ], 400);
            }

            // --- Xử lý tham số truy vấn ---
            $createdAtMax = Carbon::now()->toIso8601String();
            $createdAtMin = Carbon::now()->subDays(4)->toIso8601String();

            $params = [
                'AppId' => $config['appId'],
                'page' => max(1, (int)($request->input('page', 1))),
                'limit' => min(1000, max(1, (int)($request->input('limit', 1000)))),
                'format' => 'JSON',
                'sort' => 'created_at',
                'order' => 'desc',
                'created_at_min' => $request->input('created_at_min', $createdAtMin),
                'created_at_max' => $request->input('created_at_max', $createdAtMax),
            ];

            if ($request->has('ids')) {
                $params['ids'] = $request->input('ids');
            }
            if ($request->has('since_id')) {
                $params['since_id'] = $request->input('since_id');
            }
            if ($request->has('status')) {
                $params['status'] = $request->input('status');
            }

            // --- Tạo signature ---
            $queryString = http_build_query($params);
            $params['Signature'] = sha1($queryString . $config['secretKey']);

            // --- Gọi API ---
            $url = $config['apiUrl'] . '/orders.php?' . http_build_query($params);
            Log::debug('Yêu cầu API Brick:', [
                'url' => $url,
                'query_params' => $params
            ]);
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                $orders = collect($data['orders'])->map(function ($item) {
                    $order = $item['order'];
                    return [
                        'id' => $order['id'],
                        'external_id' => $order['external_id'],
                        'created_at' => Carbon::parse($order['created_at'])->format('Y-m-d H:i:s'),
                        'status' => $order['status'],
                        'brand' => $order['brand'],
                        'channel' => $order['channel'],
                        'buyer_email' => $order['buyer_email'],
                        'shipping_address' => $order['shipping_address'],
                        'items' => collect($order['items'])->map(function ($item) {
                            return [
                                'id' => $item['id'],
                                'pn' => $item['pn'],
                                'title' => $item['title'],
                                'quantity' => $item['quantity'],
                                'mockups' => $item['mockups'],
                                'designs' => $item['designs']
                            ];
                        })->toArray(),
                        'summary' => $order['summary'],
                        'shipping' => $order['shipping'],
                        'payment' => $order['payment'],
                        'fulfillments' => $order['fulfillments']
                    ];
                })->sortByDesc('created_at')->values();

                // --- Phân trang ---
                $perPage = $params['limit'];
                $currentPage = $params['page'];
                $pagedData = $orders->slice(($currentPage - 1) * $perPage, $perPage)->all();
                $paginatedOrders = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, $orders->count(), $perPage, $currentPage, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]);
                Log::info('Paginated orders:', ['orders' => $paginatedOrders]);
                return view('admin.orders.submitted-order-list', [
                    'orders' => $paginatedOrders
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['error'] ?? 'Lỗi API không xác định'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Get orders error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách đơn hàng'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $order = ExcelOrder::findOrFail($id);

            if ($order->delete()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the order'
            ], 500);
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids');

            if (empty($orderIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No order IDs provided'
                ], 400);
            }

            $deletedCount = ExcelOrder::whereIn('id', $orderIds)->delete();

            if ($deletedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Orders deleted successfully',
                    'deleted_count' => $deletedCount
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete orders'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error deleting orders: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the orders'
            ], 500);
        }
    }

    public function getOrderDetails(Request $request)
    {
        try {
            $orderId = $request->query('id');

            Log::info('Received order ID:', ['orderId' => $orderId]);

            if (empty($orderId)) {
                Log::warning('Missing order ID in request');
                return response()->json([
                    'success' => false,
                    'message' => 'Missing order ID'
                ], 400);
            }

            // Lấy config cho TwoFifteen
            $config = $this->apiServices['twofifteen'];

            $queryParams = [
                'id' => $orderId,
                'AppId' => $config['appId']
            ];

            // Tạo query string không bao gồm Signature
            $queryString = http_build_query($queryParams);

            // Tính signature: sha1(query string + secret key)
            $signature = sha1($queryString . $config['secretKey']);

            // Thêm signature vào query parameters
            $queryParams['Signature'] = $signature;

            // Log request details
            Log::info('TwoFifteen API getOrderDetails Request:', [
                'url' => $config['apiUrl'] . '/order.php',
                'query_params' => $queryParams
            ]);

            $response = Http::get($config['apiUrl'] . '/order.php?' . http_build_query($queryParams));

            Log::info('TwoFifteen API getOrderDetails Response:', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                $orderData = $response->json();

                Log::info('Order details retrieved successfully:', $orderData);

                return view('admin.orders.submitted-order-detail', [
                    'order' => $orderData
                ]);
            }

            Log::error('Failed to fetch order details:', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return response()->json([
                'success' => false,
                'message' => $response->json()['error'] ?? 'Unknown error'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error fetching order details:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching order details'
            ], 500);
        }
    }
}
