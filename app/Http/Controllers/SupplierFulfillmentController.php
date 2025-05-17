<?php

namespace App\Http\Controllers;

use App\Models\ImportFile;
use App\Models\SupplierFulfillment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\ExcelOrderImportService;
use Illuminate\Support\Facades\Auth;
use App\Models\ExcelOrder;

class SupplierFulfillmentController extends Controller
{
    private $apiUrl = 'https://www.twofifteen.co.uk/api/orders.php';
    private $appId;
    private $secretKey;

    public function __construct()
    {
        $this->appId = config('services.twofifteen.app_id');
        $this->secretKey = config('services.twofifteen.secret_key');
    }

    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'external_id' => 'required|string',
                'brand' => 'required|string',
                'channel' => 'required|string',
                'buyer_email' => 'required|email',
                'shipping_address' => 'required|array',
                'shipping' => 'nullable|array',
                'items' => 'required|array',
                'comment' => 'nullable|string'
            ]);

            // Prepare request body
            $requestBody = [
                'external_id' => $validated['external_id'],
                'brand' => $validated['brand'],
                'channel' => $validated['channel'],
                'buyer_email' => $validated['buyer_email'],
                'shipping_address' => $validated['shipping_address'],
                'items' => $validated['items'],
                'comment' => $validated['comment'] ?? null,
                'shipping' => $validated['shipping'] ?? null
            ];

            // Calculate signature for POST request
            $signature = sha1(json_encode($requestBody) . $this->secretKey);

            // Make API request with proper headers
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->withQueryParameters([
                'AppId' => $this->appId,
                'Signature' => $signature
            ])->post($this->apiUrl, $requestBody);

            if ($response->successful()) {
                // Create order in database
                $order = SupplierFulfillment::create([
                    'external_id' => $validated['external_id'],
                    'brand' => $validated['brand'],
                    'channel' => $validated['channel'],
                    'buyer_email' => $validated['buyer_email'],
                    'shipping_address' => $validated['shipping_address'],
                    'items' => $validated['items'],
                    'comment' => $validated['comment'] ?? null,
                    'shipping' => $validated['shipping'] ?? null,
                    'status' => 'pending',
                    'api_response' => $response->json() // Lưu response từ API
                ]);

                return response()->json([
                    'message' => 'Order created successfully',
                    'order' => $order
                ], 201);
            }

            return response()->json([
                'error' => 'Failed to create order',
                'details' => $response->json()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error creating order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Phương thức để lấy danh sách đơn hàng (GET request)
    public function index(Request $request)
    {
        try {
            // Prepare query parameters
            $queryParams = $request->all();

            // Calculate signature for GET request
            $queryString = http_build_query($queryParams);
            $signature = sha1($queryString . $this->secretKey);

            // Add signature to query parameters
            $queryParams['Signature'] = $signature;
            $queryParams['AppId'] = $this->appId;

            // Make API request
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->apiUrl, $queryParams);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Failed to fetch orders',
                'details' => $response->json()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function orderFulfillmentDetail($id)
    {
        $order = ImportFile::with(['excelOrders.items.mockups', 'excelOrders.items.designs'])->find($id);
        return view('admin.orders.order-fulfillment-detail', compact('order'));
    }
    public function orderFulfillmentList()
    {
        try {
            // Lấy danh sách file từ model ImportFile với điều kiện role là admin
            $files = ImportFile::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('role', 'admin');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Ghi log để debug
            Log::info('Order Fulfillment Files:', ['files' => $files->toArray()]);

            // Trả về view với biến $files
            return view('admin.orders.order-fulfillment-list', compact('files'));
        } catch (\Exception $e) {
            Log::error('Order Fulfillment List Error: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while fetching the file list: ' . $e->getMessage());
        }
    }
    public function uploadFulfillmentFile(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
            ]);

            $file = $request->file('file');
            $fileName = date('Y-m-d') . '-' . $file->getClientOriginalName();

            // Tạo thư mục nếu chưa tồn tại
            $uploadPath = public_path('uploads/fulfillment');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Lưu file vào thư mục public
            $file->move($uploadPath, $fileName);

            // Đọc file Excel
            $spreadsheet = IOFactory::load($uploadPath . '/' . $fileName);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            if ($highestRow <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'File không có dữ liệu'
                ], 422);
            }

            // Lấy đường dẫn file
            $fileUrl = asset('uploads/fulfillment/' . $fileName);

            // Lấy user_id từ người dùng đang đăng nhập
            $userId = Auth::id();

            // Lưu thông tin file vào database, bao gồm user_id
            $importedFile = ImportFile::create([
                'file_name' => $fileName,
                'file_path' => $fileUrl,
                'status' => 'pending',
                'error_logs' => [],
                'user_id' => $userId
            ]);

            // Chuyển đổi dữ liệu Excel thành mảng
            $rows = [];
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = [];
                foreach ($worksheet->getRowIterator($row)->current()->getCellIterator() as $cell) {
                    $rowData[$cell->getColumn()] = $cell->getValue();
                }
                $rows[] = $rowData;
            }

            // Xử lý dữ liệu sử dụng ExcelOrderImportService
            $excelOrderImportService = new ExcelOrderImportService();
            $excelOrderImportService->process($importedFile, $rows);

            // Cập nhật trạng thái file

            return response()->json([
                'success' => true,
                'message' => 'File downloaded successfully',
                'data' => [
                    'file_id' => $importedFile->id,
                    'file_name' => $fileName,
                    'total_rows' => $highestRow - 1,
                    'file_url' => $fileUrl
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xử lý file: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Nếu có lỗi, cập nhật trạng thái file và lưu log lỗi
            if (isset($importedFile)) {
                $importedFile->update([
                    'status' => 'failed',
                    'error_logs' => [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Have error when process file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            // Log để debug
            Log::info('IDs to delete:', $ids);

            // Lấy thông tin các file trước khi xóa
            $files = ImportFile::whereIn('id', $ids)->get();

            foreach ($files as $file) {
                // Lấy đường dẫn vật lý của file
                $filePath = public_path('uploads/fulfillment/' . $file->file_name);

                // Kiểm tra và xóa file vật lý nếu tồn tại
                if (file_exists($filePath)) {
                    unlink($filePath);
                    Log::info('Deleted physical file: ' . $filePath);
                }

                // Xóa các bản ghi liên quan trong bảng excel_orders
                $file->excelOrders()->delete(); // Xóa các bản ghi liên quan
            }

            // Xóa records trong database
            ImportFile::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Files deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting files: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting files: ' . $e->getMessage()
            ]);
        }
    }

    public function uploadCustomerFulfillmentFile(Request $request)
    {
        $importedFile = null;
        try {
            // Validate request
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
                'warehouse' => 'required|in:US,UK', // Validate giá trị warehouse
            ]);

            $file = $request->file('file');
            // Giữ lại tên file gốc và thêm timestamp hoặc chuỗi ngẫu nhiên để tránh trùng lặp
            $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $timestamp = now()->format('Ymd_His');
            $randomString = Str::random(8); // Chuỗi ngẫu nhiên 8 ký tự
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = $originalFileName . '_' . $timestamp . '_' . $randomString . '.' . $fileExtension;

            // Tạo thư mục nếu chưa tồn tại
            $uploadPath = public_path('uploads/customer_fulfillment');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Lưu file vào thư mục public
            $file->move($uploadPath, $fileName);

            // Đọc file Excel
            $spreadsheet = IOFactory::load($uploadPath . '/' . $fileName);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            if ($highestRow <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'File has no data'
                ], 422);
            }

            // Lấy đường dẫn file
            $fileUrl = asset('uploads/customer_fulfillment/' . $fileName);

            // Lấy user_id từ người dùng đang đăng nhập
            $userId = Auth::id();

            // Lưu thông tin file vào database, bao gồm user_id và warehouse
            $importedFile = ImportFile::create([
                'file_name' => $fileName,
                'file_path' => $fileUrl,
                'status' => 'pending',
                'error_logs' => [],
                'user_id' => $userId,
                'warehouse' => $request->input('warehouse') // Lưu giá trị warehouse vào database
            ]);

            // Chuyển đổi dữ liệu Excel thành mảng
            $rows = [];
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = [];
                foreach ($worksheet->getRowIterator($row)->current()->getCellIterator() as $cell) {
                    $rowData[$cell->getColumn()] = $cell->getValue();
                }
                $rows[] = $rowData;
            }

            // Xử lý dữ liệu sử dụng ExcelOrderImportService
            $excelOrderImportService = new ExcelOrderImportService();
            $result = $excelOrderImportService->processCustomer($importedFile, $rows, $request->input('warehouse'));

            // Nếu result là false, nghĩa là đã có lỗi và status đã được cập nhật trong service
            if ($result === false) {
                return back()->with('error', 'Have error in file. Click to see detail.');
            }

            // Cập nhật trạng thái file thành công nếu không có lỗi
            $importedFile->update([
                'status' => 'processed',
                'total_rows' => $highestRow - 1,
                'processed_rows' => $highestRow - 1
            ]);

            return back()->with('success', 'File uploaded successfully');
        } catch (\Exception $e) {
            Log::error('Error processing customer file: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Nếu có lỗi, cập nhật trạng thái file và lưu log lỗi
            if ($importedFile) {
                $importedFile->update([
                    'status' => 'failed',
                    'error_logs' => [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]
                ]);
            }

            return back()->with('error', 'An error occurred while processing the file: ' . $e->getMessage());
        }
    }

    public function getCustomerUploadedFiles(Request $request)
    {
        try {
            // Lấy user_id của khách hàng đang đăng nhập
            $userId = Auth::id();

            // Lấy danh sách các file mà khách hàng này đã tải lên
            $files = ImportFile::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(10); // Phân trang, mỗi trang 10 file

            // Trả về view với danh sách file
            return view('customer.orders.order-list', compact('files'));
        } catch (\Exception $e) {
            Log::error('Have error when get file list: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Have error when get file list: ' . $e->getMessage());
        }
    }

    public function deleteFiles(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one file to delete.'
                ], 400);
            }

            // Lấy thông tin các file trước khi xóa
            $files = ImportFile::whereIn('id', $ids)->get();

            foreach ($files as $file) {
                // Lấy đường dẫn vật lý của file
                $filePath = public_path('uploads/customer_fulfillment/' . basename($file->file_name));

                // Kiểm tra và xóa file vật lý nếu tồn tại
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                // Xóa các bản ghi liên quan
                $file->excelOrders()->delete();
            }

            // Xóa records trong database
            ImportFile::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Files deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting files: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCustomerOrders(Request $request)
    {
        try {
            $userId = Auth::id();

            // Thêm relationship fulfillment vào eager loading
            $orders = ImportFile::with(['excelOrders.items', 'excelOrders.fulfillment'])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            // Tính toán thống kê
            $statistics = [
                'total_orders' => $orders->sum(function ($file) {
                    return $file->excelOrders->count();
                }),
                'total_items' => $orders->sum(function ($file) {
                    return $file->excelOrders->sum(function ($order) {
                        return $order->items->sum('quantity');
                    });
                }),
                'pending_orders' => $orders->sum(function ($file) {
                    return $file->excelOrders->where('status', 'pending')->count();
                }),
                'completed_orders' => $orders->sum(function ($file) {
                    return $file->excelOrders->where('status', 'completed')->count();
                })
            ];
            Log::info('Customer Orders Statistics:', $orders->toArray());
            return view('customer.orders.order-customer', compact('orders', 'statistics'));
        } catch (\Exception $e) {
            Log::error('Error fetching customer orders: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while fetching the order list: ' . $e->getMessage());
        }
    }

    public function getCustomerOrderDetail($id)
    {
        try {

            // Lấy chi tiết đơn hàng cụ thể với các relationships cần thiết
            $order = ExcelOrder::with([
                'items.mockups',
                'items.designs',

                'fulfillment',
                'importFile'
            ])

                ->findOrFail($id);

            // Tính toán thống kê cho đơn hàng này
            $orderStatistics = [
                'total_items' => $order->items->sum('quantity'),
                'total_price' => $order->fulfillment->total_price ?? 0,
                'status' => $order->status
            ];

            return view('customer.orders.order-uploaded-detail', compact('order', 'orderStatistics'));
        } catch (\Exception $e) {
            Log::error('Error fetching order detail: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $id
            ]);

            return back()->with('error', 'An error occurred while fetching the order detail: ' . $e->getMessage());
        }
    }

    public function customerUploadedFilesList()
    {
        try {
            // Lấy danh sách file từ model ImportFile với điều kiện role là customer
            $files = ImportFile::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('role', 'customer');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            // Ghi log để debug
            Log::info('Customer Uploaded Files:', ['files' => $files->toArray()]);

            // Trả về view với biến $files
            return view('admin.orders.order-customer-received', compact('files'));
        } catch (\Exception $e) {
            Log::error('Customer Uploaded Files List Error: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while fetching the file list: ' . $e->getMessage());
        }
    }
}
