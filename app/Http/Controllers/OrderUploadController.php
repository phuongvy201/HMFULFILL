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
use App\Models\User;

class OrderUploadController extends Controller
{
    // Xóa property này nếu không còn sử dụng
    // private $brickApiService;

    protected $apiServices = [
        'dtf' => [
            'apiUrl' => '',
            'bearerToken' => '',
        ],
        'twofifteen' => [
            'apiUrl' => '',
            'appId' => '',
            'secretKey' => '',
        ],
    ];

    public function __construct()
    {
        $this->apiServices = [
            'dtf' => [
                'apiUrl' => config('services.dtf.api_url'),
                'bearerToken' => config('services.dtf.bearer_token'),
            ],
            'twofifteen' => [
                'apiUrl' => config('services.twofifteen.api_url'),
                'appId' => config('services.twofifteen.app_id'),
                'secretKey' => config('services.twofifteen.secret_key'),
            ],
        ];
    }

    private function buildOrderData($order)
    {
        // Check for empty order items
        if ($order->items->isEmpty()) {
            Log::warning("Order {$order->external_id} has no items");
            throw new \Exception("Order {$order->external_id} has no items");
        }

        // Validate items
        foreach ($order->items as $item) {
            if (!$item->quantity || $item->mockups->isEmpty() || $item->designs->isEmpty()) {
                Log::warning("Invalid item in order {$order->external_id}", [
                    'quantity' => $item->quantity,
                    'mockups_count' => $item->mockups->count(),
                    'designs_count' => $item->designs->count(),
                ]);
                throw new \Exception("Invalid item in order {$order->external_id}");
            }

            // Validate mockup URLs
            foreach ($item->mockups as $mockup) {
                if (!filter_var($mockup->url, FILTER_VALIDATE_URL)) {
                    Log::warning("Invalid mockup URL in order {$order->external_id}", ['url' => $mockup->url]);
                    throw new \Exception("Invalid mockup URL in order {$order->external_id}");
                }
            }
            foreach ($item->designs as $design) {
                if (!filter_var($design->url, FILTER_VALIDATE_URL)) {
                    Log::warning("Invalid design URL in order {$order->external_id}", ['url' => $design->url]);
                    throw new \Exception("Invalid design URL in order {$order->external_id}");
                }
            }
        }

        // Determine factory based on warehouse
        $factory = strtoupper($order->warehouse) === 'US' ? 'dtf' : 'twofifteen';

        if ($factory === 'dtf') {
            $orderData = [
                'external_id' => $order->external_id,
                'brand' => !empty($order->brand) ? $order->brand : 'HM Fulfill',
                'channel' => !empty($order->channel) ? $order->channel : 'tiktok',
                'buyer_email' => !empty($order->buyer_email) ? $order->buyer_email : 'customer@example.com',
                'shipping_address' => [
                    'firstName' => $order->first_name,
                    'lastName' => !empty($order->last_name) ? $order->last_name : '',
                    'company' => !empty($order->company) ? $order->company : '',
                    'address1' => !empty($order->address1) ? $order->address1 : '',
                    'address2' => !empty($order->address2) ? $order->address2 : '',
                    'city' => !empty($order->city) ? $order->city : '',
                    'state' => !empty($order->county) ? $order->county : '', // Use county as state
                    'postcode' => !empty($order->post_code) ? $order->post_code : '',
                    'country' => !empty($order->country) ? $order->country : '',
                    'phone1' => !empty($order->phone1) ? $order->phone1 : '',
                    'phone2' => !empty($order->phone2) ? $order->phone2 : ''
                ],
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->part_number,
                        'quantity' => (int) $item->quantity,
                        'description' => $item->description,
                        'mockups' => $item->mockups->map(function ($mockup) {
                            return [
                                'title' => $mockup->title,
                                'src' => $mockup->url
                            ];
                        })->values()->toArray(),
                        'designs' => $item->designs->map(function ($design) {
                            return [
                                'title' => $design->title,
                                'src' => $design->url
                            ];
                        })->values()->toArray()
                    ];
                })->values()->toArray(),
                'shipping' => [
                    'shippingMethod' => !empty($order->shipping_method) ? $order->shipping_method : 'Standard',
                ],
                'label_url' => !empty($order->comment) ? $order->comment : '',
                'comments' => ""
            ];
        } else {
            // Twofifteen
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
        }

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
        if (empty($config['apiUrl'])) {
            Log::error("Incomplete API config for factory {$factory}", $config);
            throw new \Exception("Cấu hình API không đầy đủ cho factory {$factory}");
        }

        $jsonBody = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("JSON encoding failed for factory {$factory}: " . json_last_error_msg());
            throw new \Exception("Lỗi mã hóa JSON: " . json_last_error_msg());
        }

        // Cấu hình khác nhau cho từng factory
        if ($factory === 'dtf') {
            if (empty($config['bearerToken'])) {
                Log::error("Missing bearer token for DTF API");
                throw new \Exception("Thiếu bearer token cho DTF API");
            }
            return [
                'config' => $config,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $config['bearerToken']
                ],
                'parameters' => []
            ];
        } else {
            // Twofifteen
            if (empty($config['appId']) || empty($config['secretKey'])) {
                Log::error("Missing appId or secretKey for Twofifteen API");
                throw new \Exception("Thiếu appId hoặc secretKey cho Twofifteen API");
            }
            $signature = sha1($jsonBody . $config['secretKey']);
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

            if (empty($orderIds)) {
                Log::warning('No order IDs provided in request');
                return response()->json([
                    'success' => false,
                    'message' => 'Không có đơn hàng nào được chọn'
                ], 400);
            }

            Log::info('Processing orders', ['order_ids' => $orderIds]);

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
                'orders_summary' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'external_id' => $order->external_id,
                        'warehouse' => $order->warehouse,
                        'items_count' => $order->items->count(),
                        'items_loaded' => $order->relationLoaded('items'),
                        'mockups_designs_loaded' => $order->items->every(function ($item) {
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
            $dtfOrders = [];
            $twofifteenOrders = [];

            // Phân loại đơn hàng theo warehouse
            foreach ($orders as $order) {
                try {
                    $orderData = $this->buildOrderData($order);
                    if (strtoupper($order->warehouse) === 'US') {
                        $dtfOrders[] = $orderData;
                    } else {
                        $twofifteenOrders[] = [
                            'order' => $order,
                            'data' => $orderData
                        ];
                    }
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

            // Xử lý đơn hàng DTF
            if (!empty($dtfOrders)) {
                try {
                    $apiConfig = $this->buildApiConfig('dtf', $dtfOrders);
                    $response = Http::withHeaders($apiConfig['headers'])
                        ->withQueryParameters($apiConfig['parameters'])
                        ->post($apiConfig['config']['apiUrl'] . '/api/orders/batch', $dtfOrders);

                    if ($response->successful()) {
                        $apiResponse = $response->json();

                        // Log cấu trúc response để debug
                        Log::info('DTF API Response structure:', [
                            'response_keys' => array_keys($apiResponse),
                            'orders_count' => isset($apiResponse['orders']) ? count($apiResponse['orders']) : 'no orders key',
                            'first_order_keys' => isset($apiResponse['orders'][0]) ? array_keys($apiResponse['orders'][0]) : 'no first order'
                        ]);

                        foreach ($apiResponse['orders'] as $orderResponse) {
                            $order = $orders->firstWhere('external_id', $orderResponse['external_id']);
                            if ($order) {
                                // Kiểm tra và lấy internal_id an toàn - DTF batch response sử dụng 'order_id'
                                $internalId = $orderResponse['order_id'] ?? $orderResponse['id'] ?? $orderResponse['internal_id'] ?? null;

                                if (!$internalId) {
                                    Log::error('No internal_id found in DTF response:', [
                                        'external_id' => $order->external_id,
                                        'order_response_keys' => array_keys($orderResponse),
                                        'order_response' => $orderResponse
                                    ]);

                                    $results[] = [
                                        'order_id' => $order->id,
                                        'external_id' => $order->external_id,
                                        'success' => false,
                                        'message' => 'Không tìm thấy internal_id trong response DTF'
                                    ];
                                    continue;
                                }

                                $results[] = [
                                    'order_id' => $order->id,
                                    'external_id' => $order->external_id,
                                    'internal_id' => $internalId,
                                    'factory' => 'dtf',
                                    'success' => true,
                                    'message' => 'Tải lên thành công'
                                ];

                                // Cập nhật trạng thái đơn hàng
                                $order->markAsProcessed($apiResponse, $internalId, 'dtf');

                                // Lưu mapping vào OrderMapping
                                OrderMapping::createOrUpdate(
                                    $order->external_id,
                                    $internalId,
                                    'dtf',
                                    $apiResponse
                                );

                                Log::info('Order mapping created:', [
                                    'external_id' => $order->external_id,
                                    'internal_id' => $internalId,
                                    'factory' => 'dtf'
                                ]);
                            }
                        }
                    } else {
                        foreach ($dtfOrders as $orderData) {
                            $order = $orders->firstWhere('external_id', $orderData['external_id']);
                            if ($order) {
                                $errorResponse = $response->json();
                                $errorMessage = '';

                                // Xử lý lỗi validation từ API
                                if (isset($errorResponse['detail']) && is_array($errorResponse['detail'])) {
                                    $errorMessages = [];
                                    foreach ($errorResponse['detail'] as $error) {
                                        $field = implode('.', $error['loc']);
                                        $errorMessages[] = "{$field}: {$error['msg']}";
                                    }
                                    $errorMessage = implode(', ', $errorMessages);
                                } else {
                                    $errorMessage = $errorResponse['error'] ?? 'Lỗi không xác định';
                                }

                                $errorData = [
                                    'success' => false,
                                    'error' => $errorMessage,
                                    'status_code' => $response->status(),
                                    'timestamp' => now()->toISOString(),
                                    'api_response' => $errorResponse
                                ];

                                $order->markAsFailed($errorMessage, $errorData);

                                $results[] = [
                                    'order_id' => $order->id,
                                    'external_id' => $order->external_id,
                                    'success' => false,
                                    'message' => "Lỗi khi gửi đơn hàng đến DTF: " . $errorMessage
                                ];

                                Log::error('DTF API Error:', [
                                    'order_id' => $order->id,
                                    'external_id' => $order->external_id,
                                    'error_response' => $errorResponse
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing DTF batch:", [
                        'message' => $e->getMessage()
                    ]);
                    foreach ($dtfOrders as $orderData) {
                        $order = $orders->firstWhere('external_id', $orderData['external_id']);
                        if ($order) {
                            $errorResponse = [
                                'success' => false,
                                'error' => $e->getMessage(),
                                'status_code' => null,
                                'timestamp' => now()->toISOString()
                            ];

                            $order->markAsFailed($e->getMessage(), $errorResponse);

                            $results[] = [
                                'order_id' => $order->id,
                                'external_id' => $order->external_id,
                                'success' => false,
                                'message' => "Lỗi xử lý đơn hàng {$order->external_id}: " . $e->getMessage()
                            ];
                        }
                    }
                }
            }

            // Xử lý đơn hàng Twofifteen
            foreach ($twofifteenOrders as $orderInfo) {
                try {
                    $order = $orderInfo['order'];
                    $orderData = $orderInfo['data'];
                    $apiConfig = $this->buildApiConfig('twofifteen', $orderData);

                    $response = Http::withHeaders($apiConfig['headers'])
                        ->withQueryParameters($apiConfig['parameters'])
                        ->post($apiConfig['config']['apiUrl'] . '/orders.php', $orderData);

                    $results[] = $this->processOrderResponse($order, $response, 'twofifteen');
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

    /**
     * Cập nhật đơn hàng DTF
     * 
     * @param Request $request
     * @param string $orderId UUID của đơn hàng DTF
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDtfOrder(Request $request, $orderId)
    {
        try {
            // 1. Tìm đơn hàng trong OrderMapping
            $orderMapping = OrderMapping::where('internal_id', $orderId)
                ->where('factory', 'dtf')
                ->first();

            if (!$orderMapping) {
                Log::error('DTF order not found:', ['order_id' => $orderId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng DTF'
                ], 404);
            }

            // 2. Lấy dữ liệu cập nhật từ request
            $updateData = $request->all();
            if (empty($updateData)) {
                Log::warning('No update data provided:', ['order_id' => $orderId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không có dữ liệu cập nhật'
                ], 400);
            }

            // Log dữ liệu cập nhật
            Log::info('Updating DTF order:', [
                'order_id' => $orderId,
                'external_id' => $orderMapping->external_id,
                'update_data' => $updateData
            ]);

            // 3. Gọi API DTF để cập nhật đơn hàng
            $apiConfig = $this->buildApiConfig('dtf', $updateData);

            // Log cấu hình API
            Log::info('DTF API config:', [
                'url' => $apiConfig['config']['apiUrl'] . '/api/orders/' . $orderId,
                'headers' => $apiConfig['headers']
            ]);

            $response = Http::withHeaders($apiConfig['headers'])
                ->withQueryParameters($apiConfig['parameters'])
                ->put($apiConfig['config']['apiUrl'] . '/api/orders/' . $orderId, $updateData);

            // Log response từ API
            Log::info('DTF API response:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $apiResponse = $response->json();

                // Cập nhật OrderMapping với response mới
                $orderMapping->update([
                    'api_response' => $apiResponse
                ]);

                Log::info('DTF order updated successfully:', [
                    'order_id' => $orderId,
                    'external_id' => $orderMapping->external_id,
                    'update_data' => $updateData
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật đơn hàng thành công',
                    'data' => $apiResponse
                ]);
            }

            // Xử lý lỗi từ API
            $errorResponse = $response->json();
            $errorMessage = '';

            if (isset($errorResponse['detail']) && is_array($errorResponse['detail'])) {
                $errorMessages = [];
                foreach ($errorResponse['detail'] as $error) {
                    $field = implode('.', $error['loc']);
                    $errorMessages[] = "{$field}: {$error['msg']}";
                }
                $errorMessage = implode(', ', $errorMessages);
            } else {
                $errorMessage = $errorResponse['error'] ?? $errorResponse['message'] ?? 'Lỗi không xác định từ API DTF';
            }

            Log::error('DTF API Error:', [
                'order_id' => $orderId,
                'external_id' => $orderMapping->external_id,
                'status_code' => $response->status(),
                'error_response' => $errorResponse,
                'error_message' => $errorMessage,
                'request_data' => $updateData
            ]);

            return response()->json([
                'success' => false,
                'message' => "Lỗi cập nhật đơn hàng: " . $errorMessage,
                'error' => $errorResponse
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Error updating DTF order:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }
}
