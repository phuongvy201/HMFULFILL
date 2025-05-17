<?php

namespace App\Http\Controllers;

use App\Helpers\GoogleDriveHelper;
use App\Models\ExcelOrder;
use App\Services\BrickApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\UrlHelper;
use Illuminate\Support\Facades\Http;

class OrderUploadController extends Controller
{
    private $brickApiService;

    public function __construct(BrickApiService $brickApiService)
    {
        $this->brickApiService = $brickApiService;
    }

    // public function upload(Request $request)
    // {
    //     try {
    //         $orderIds = $request->input('order_ids');

    //         if (empty($orderIds)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Don\'t have any order selected'
    //             ]);
    //         }

    //         // Thêm logging để debug
    //         Log::info('Selected order IDs:', ['order_ids' => $orderIds]);

    //         $orders = ExcelOrder::with(['items'])
    //             ->whereIn('id', $orderIds)
    //             ->get();

    //         // Kiểm tra xem có lấy được orders không
    //         Log::info('Found orders:', ['count' => $orders->count()]);

    //         if ($orders->isEmpty()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Don\'t have any order selected'
    //             ]);
    //         }

    //         $results = [];
    //         foreach ($orders as $order) {
    //             // Kiểm tra các relationship
    //             if (!$order->items) {
    //                 Log::warning('Missing relationships for order:', [
    //                     'order_id' => $order->id,
    //                     'has_items' => $order->items ? true : false,
    //                 ]);
    //                 continue;
    //             }

    //             $items = $order->items;
    //             foreach ($items as &$item) {
    //                 if (isset($item['designs'])) {
    //                     foreach ($item['designs'] as &$design) {
    //                         if (strpos($design['src'], 'drive.google.com') !== false) {
    //                             $design['src'] = GoogleDriveHelper::convertToDirectLink($design['src']);
    //                         }
    //                     }
    //                 }
    //             }
    //             $order->items = $items;

    //             $orderData = [
    //                 'external_id' => $order->external_id,
    //                 'brand' => $order->brand,
    //                 'channel' => $order->channel,
    //                 'buyer_email' => $order->buyer_email,
    //                 'comments' => $order->comment,
    //                 'shipping_address' => [
    //                     'firstName' => $order->first_name,
    //                     'lastName' => $order->last_name,
    //                     'company' => $order->company,
    //                     'address1' => $order->address1,
    //                     'address2' => $order->address2,
    //                     'city' => $order->city,
    //                     'county' => $order->county,
    //                     'postcode' => $order->post_code,
    //                     'country' => $order->country,
    //                     'phone1' => $order->phone1,
    //                     'phone2' => $order->phone2
    //                 ],
    //                 'items' => $order->items->map(function ($item) {
    //                     return [
    //                         'id' => $item->id,
    //                         'pn' => $item->part_number,
    //                         'external_id' => $item->external_id,
    //                         'title' => $item->title,
    //                         'retailPrice' => $item->retail_price,
    //                         'retailCurrency' => $item->retail_currency ?? 'GBP',
    //                         'quantity' => $item->quantity,
    //                         'description' => $item->description,
    //                         'label' => [
    //                             'id' => $item->label_id ?? 0,
    //                             'name' => $item->label_name ?? '',
    //                             'type' => $item->label_type ?? 'Printed'
    //                         ],
    //                         'mockups' => $item->mockups->map(function ($mockup) {
    //                             return [
    //                                 'title' => $mockup->title,
    //                                 'src' => $mockup->url
    //                             ];
    //                         })->toArray(),
    //                         'designs' => $item->designs->map(function ($design) {
    //                             return [
    //                                 'title' => $design->title,
    //                                 'src' => $design->url
    //                             ];
    //                         })->toArray()
    //                     ];
    //                 })->toArray(),
    //                 // 'comments' => $order->comment
    //             ];
    //             // Thêm logging để debug
    //             Log::info('Order Data:', [
    //                 'order_id' => $order->id,
    //                 'comment' => $order->comment,
    //                 'comment_type' => gettype($order->comment),
    //                 'raw_order' => $order->toArray()
    //             ]);

    //             // Log thông tin request
    //             Log::info('Brick API Request Details:', [
    //                 'order_id' => $order->id,
    //                 'external_id' => $order->external_id,
    //                 'request_body' => $orderData
    //             ]);

    //             $result = $this->brickApiService->sendOrder($orderData, $order->id);

    //             // Kiểm tra status code 201
    //             if ($result['success']) {
    //                 $results[] = [
    //                     'order_id' => $order->id,
    //                     'external_id' => $order->external_id,
    //                     'success' => true,
    //                     'message' => 'Upload success'
    //                 ];
    //             } else {
    //                 $results[] = [
    //                     'order_id' => $order->id,
    //                     'external_id' => $order->external_id,
    //                     'success' => false,
    //                     'message' => $result['error'] ?? 'Unknown error'
    //                 ];
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Processed orders successfully',
    //             'results' => $results
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Order upload error:', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred while processing orders'
    //         ], 500);
    //     }
    // }
    public function upload(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids');

            if (empty($orderIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Don\'t have any order selected'
                ]);
            }

            // Thêm logging để debug
            Log::info('Selected order IDs:', ['order_ids' => $orderIds]);

            $orders = ExcelOrder::with(['items'])
                ->whereIn('id', $orderIds)
                ->get();

            // Kiểm tra xem có lấy được orders không
            Log::info('Found orders:', ['count' => $orders->count()]);

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Don\'t have any order selected'
                ]);
            }

            $results = [];
            foreach ($orders as $order) {
                // Kiểm tra các relationship
                if (!$order->items) {
                    Log::warning('Missing relationships for order:', [
                        'order_id' => $order->id,
                        'has_items' => $order->items ? true : false,
                    ]);
                    continue;
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

                // Log thông tin request
                Log::info('Brick API Request Details:', [
                    'order_id' => $order->id,
                    'external_id' => $order->external_id,
                    'request_body' => $orderData
                ]);

                $result = $this->brickApiService->sendOrder($orderData, $order->id);

                // Cập nhật trạng thái đơn hàng dựa trên kết quả API
                if ($result['success']) {
                    $order->update([
                        'status' => 'processed'
                    ]);
                    $results[] = [
                        'order_id' => $order->id,
                        'external_id' => $order->external_id,
                        'success' => true,
                        'message' => 'Upload success'
                    ];
                } else {
                    $order->update([
                        'status' => 'failed',
                        'error_message' => $result['error'] ?? 'Unknown error'
                    ]);
                    $results[] = [
                        'order_id' => $order->id,
                        'external_id' => $order->external_id,
                        'success' => false,
                        'message' => $result['error'] ?? 'Unknown error'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Processed orders successfully',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Order upload error:', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing orders'
            ], 500);
        }
    }
    public function testOrder()
    {
        try {
            $testData = [
                "external_id" => "TEST-002",
                "brand" => "HM Fulfill",
                "channel" => "site",
                "buyer_email" => "test@example.com",
                "shipping_address" => [
                    "firstName" => "John",
                    "lastName" => "Doe",
                    "company" => "Test Company",
                    "address1" => "123 Test St",
                    "address2" => "Suite 1",
                    "city" => "London",
                    "county" => "Greater London",
                    "postcode" => "SW1A 1AA",
                    "country" => "UK",
                    "phone1" => "02012345678",
                    "phone2" => ""
                ],
                "items" => [
                    [
                        "pn" => "BY003-WH-S",

                        "quantity" => 1,
                        "description" => "Test product description",

                        "mockups" => [
                            [
                                "title" => "Printing Front Side",
                                "src" => "https://www.twofifteen.co.uk/images/svg/mockup-5d3cf2a60e21468f6b5bfbcedeef1e8a.png?v=fd41c3f2"
                            ],
                            [
                                "title" => "Printing Back Side",
                                "src" => "https://www.twofifteen.co.uk/images/svg/mockup-5d3cf2a60e21468f6b5bfbcedeef1e8a.png?v=fd41c3f2"
                            ]
                        ],
                        "designs" => [
                            [
                                "title" => "Printing Front Side",
                                "src" => "https://www.twofifteen.co.uk/images/svg/mockup-5d3cf2a60e21468f6b5bfbcedeef1e8a.png?v=fd41c3f2"
                            ],
                            [
                                "title" => "Printing Back Side",
                                "src" => "https://www.twofifteen.co.uk/images/svg/mockup-5d3cf2a60e21468f6b5bfbcedeef1e8a.png?v=fd41c3f2"
                            ]
                        ]
                    ]
                ],
                "comments" => "Test order"
            ];

            $result = $this->brickApiService->sendOrder($testData, 1);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $params = [];

            // Thêm các tham số tùy chọn nếu có
            if ($request->has('ids')) {
                $params['ids'] = $request->input('ids');
            }
            if ($request->has('since_id')) {
                $params['since_id'] = $request->input('since_id');
            }
            if ($request->has('created_at_min')) {
                $params['created_at_min'] = $request->input('created_at_min');
            }
            if ($request->has('created_at_max')) {
                $params['created_at_max'] = $request->input('created_at_max');
            }
            if ($request->has('status')) {
                $params['status'] = $request->input('status');
            }
            if ($request->has('page')) {
                $params['page'] = 1;
            }
            if ($request->has('limit')) {
                $params['limit'] = $request->input('limit');
            } else {
                $params['limit'] = 1000; // Số lượng đơn hàng mỗi trang
            }

            $result = $this->brickApiService->getOrders($params);

            if ($result['success']) {
                // Format và sắp xếp dữ liệu trước khi gửi đến view
                $orders = collect($result['data']['orders'])->map(function ($item) {
                    $order = $item['order'];
                    return [
                        'id' => $order['id'],
                        'external_id' => $order['external_id'],
                        'created_at' => \Carbon\Carbon::parse($order['created_at'])->format('Y-m-d H:i:s'),
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

                // Sử dụng phân trang
                $perPage = $params['limit'];
                $currentPage = $params['page'] ?? 1;
                $pagedData = $orders->slice(($currentPage - 1) * $perPage, $perPage)->all();
                $paginatedOrders = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, $orders->count(), $perPage, $currentPage, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]);

                return view('admin.orders.submitted-order-list', [
                    'orders' => $paginatedOrders
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 400);
        } catch (\Exception $e) {
            Log::error('Get orders error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching orders'
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

            Log::info('Received order ID:', ['orderId' => $orderId]); // 👉 Ghi log order ID

            if (empty($orderId)) {
                Log::warning('Missing order ID in request');
                return response()->json([
                    'success' => false,
                    'message' => 'Missing order ID'
                ], 400);
            }

            $result = $this->brickApiService->getOrderDetails($orderId);

            // 👉 Ghi log kết quả từ API
            Log::info('Order details API result:', $result);

            if ($result['success']) {
                // Log chi tiết đơn hàng nếu cần
                Log::info('Order details:', $result['data']);

                // Trả về dữ liệu dưới dạng JSON
                return view('admin.orders.submitted-order-detail', [
                    'order' => $result['data']
                ]);
            }

            Log::error('Failed to fetch order details:', ['error' => $result['error']]);

            return response()->json([
                'success' => false,
                'message' => $result['error']
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
