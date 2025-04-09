<?php

namespace App\Http\Controllers;

use App\Models\ExcelOrder;
use App\Services\BrickApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderUploadController extends Controller
{
    private $brickApiService;

    public function __construct(BrickApiService $brickApiService)
    {
        $this->brickApiService = $brickApiService;
    }

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
                    'comments' => $order->comments
                ];

                // Log thông tin request
                Log::info('Brick API Request Details:', [
                    'order_id' => $order->id,
                    'external_id' => $order->external_id,
                    'request_body' => $orderData
                ]);

                $result = $this->brickApiService->sendOrder($orderData, $order->id);
                $results[] = [
                    'order_id' => $order->id,
                    'external_id' => $order->external_id,
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'Upload success' : ($result['error'] ?? 'Unknown error')
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Processed orders successfully',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Order upload error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
                $params['limit'] = 1000;
            }

            $result = $this->brickApiService->getOrders($params);

            if ($result['success']) {
                // Trả về view với dữ liệu đơn hàng
                return view('admin.orders.submitted-order-list', [
                    'orders' => $result['data']['orders']
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
}
