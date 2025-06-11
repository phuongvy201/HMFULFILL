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
use App\Services\BrickApiService;
use Illuminate\Support\Facades\DB;
use App\Models\OrderMapping;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ExcelOrderItem;
use App\Models\ExcelOrderDesign;
use App\Models\ExcelOrderMockup;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Rules\ValidPartNumber;
use App\Rules\ValidPrintSpace;
use App\Models\Wallet;
use App\Models\Transaction;

/**
 * @OA\Info(
 *     title="UK Fulfillment API",
 *     version="1.0.0",
 *     description="API cho việc quản lý đơn hàng và fulfillment"
 * )
 */

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
    public function orderFulfillmentList(Request $request)
    {
        try {
            // Khởi tạo query với điều kiện role là admin
            $query = ImportFile::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('role', 'admin');
                });

            // Áp dụng các bộ lọc
            if ($request->filled('file_id')) {
                $query->where('id', $request->file_id);
            }

            if ($request->filled('warehouse')) {
                $query->where('warehouse', $request->warehouse);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            // Sắp xếp và phân trang
            $files = $query->orderBy('created_at', 'desc')->paginate(20);

            // Giữ lại các tham số filter trong pagination
            $files->appends($request->query());

            // Ghi log để debug
            Log::info('Order Fulfillment Files with filters:', [
                'filters' => $request->only(['file_id', 'warehouse', 'status', 'date']),
                'count' => $files->count(),
                'total' => $files->total()
            ]);

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
                'user_id' => $userId,
                'warehouse' => 'UK' // Thêm warehouse mặc định là UK
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

            // Kiểm tra warehouse US chưa hỗ trợ
            if (strtoupper($request->input('warehouse')) === 'US') {
                return back()->with('error', 'Warehouse US is not supported yet');
            }

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

            // Lưu thông tin file vào database với status pending
            $importedFile = ImportFile::create([
                'file_name' => $fileName,
                'file_path' => $fileUrl,
                'status' => 'pending', // Luôn set status là pending khi khách hàng upload
                'error_logs' => [],
                'user_id' => $userId,
                'warehouse' => $request->input('warehouse')
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

            // Nếu result là false, nghĩa là đã có lỗi
            if ($result === false) {
                return back()->with('error', 'Have error in file. Click to see detail.');
            }

            // Đảm bảo status vẫn là pending sau khi xử lý thành công
            $importedFile->update([
                'status' => 'pending', // Giữ status là pending cho đến khi admin duyệts
                'total_rows' => $highestRow - 1,
                'processed_rows' => $highestRow - 1
            ]);

            // Log để theo dõi
            Log::info('File imported successfully with pending status', [
                'import_file_id' => $importedFile->id,
                'status' => 'pending',
                'warehouse' => $request->input('warehouse'),
                'user_id' => $userId
            ]);

            return back()->with('success', 'File uploaded successfully. Status is pending and waiting for approval.');
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

            // Lấy danh sách các file mà khách hàng này đã tải lên, kèm theo thông tin người dùng
            $files = ImportFile::with('user') // Eager load thông tin người dùng
                ->where('user_id', $userId)
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

            // 1. Lấy ExcelOrder với items và các relationships cần thiết
            $query = ExcelOrder::with([
                'items' => function ($query) {
                    $query->with(['designs', 'mockups', 'product']);
                }
            ])->where('created_by', $userId);

            // Thêm điều kiện tìm kiếm theo external_id nếu có
            if ($request->filled('external_id')) {
                $searchTerm = trim(preg_replace('/\s+/', '', $request->external_id));
                $query->whereRaw('REPLACE(REPLACE(REPLACE(TRIM(external_id), " ", ""), "\t", ""), "\n", "") LIKE ?', ["%{$searchTerm}%"]);
            }

            // Thêm điều kiện tìm kiếm theo khoảng thời gian
            if ($request->filled('created_at_min')) {
                $startDate = Carbon::parse($request->created_at_min)->startOfDay();
                $query->where('created_at', '>=', $startDate);
            }

            if ($request->filled('created_at_max')) {
                $endDate = Carbon::parse($request->created_at_max)->endOfDay();
                $query->where('created_at', '<=', $endDate);
            }

            // Lấy dữ liệu có phân trang
            $excelOrders = $query->orderBy('created_at', 'desc')
                ->paginate(50)
                ->withQueryString();

            // Tính thống kê
            $statistics = [
                'total_orders'     => $excelOrders->total(),
                'total_items'      => $excelOrders->sum(fn($o) => $o->items->sum('quantity')),
                'pending_orders'   => $excelOrders->where('status', 'pending')->count(),
                'processed_orders' => $excelOrders->where('status', 'processed')->count(),
                'total_amount'     => $excelOrders->sum(fn($o) => $o->items->sum(fn($i) => $i->print_price * $i->quantity)),
            ];

            return view('customer.orders.order-customer', [
                'excelOrders'  => $excelOrders,
                'statistics'   => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCustomerOrders:', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Có lỗi khi tải danh sách đơn hàng: ' . $e->getMessage());
        }
    }



    public function getCustomerOrderDetail($externalId)
    {
        try {
            // Lấy chi tiết đơn hàng cụ thể với các relationships cần thiết
            $order = ExcelOrder::with([
                'items.mockups',
                'items.designs',
                'items.product',
                'importFile'
            ])->where('external_id', $externalId)->firstOrFail();

            // Tính toán thống kê cho đơn hàng này
            $orderStatistics = [
                'total_items' => $order->items->sum('quantity'),
                'total_amount' => round($order->items->sum(function ($item) {
                    return $item->print_price * $item->quantity;
                }), 2),
                'status' => $order->status
            ];

            // Làm tròn giá cho từng item (nếu có)
            foreach ($order->items as $item) {
                $item->print_price = round($item->print_price, 2);
                // Nếu muốn có total_price cho từng item:
                $item->total_price = round($item->print_price * $item->quantity, 2);
            }

            Log::info('Order detail:', ['order' => $order]);
            return view('customer.orders.order-uploaded-detail', compact('order', 'orderStatistics'));
        } catch (\Exception $e) {
            Log::error('Error in getCustomerOrderDetail:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'external_id' => $externalId
            ]);
            return back()->with('error', 'Có lỗi khi tải thông tin đơn hàng: ' . $e->getMessage());
        }
    }

    public function customerUploadedFilesList(Request $request)
    {
        try {
            // Khởi tạo query với điều kiện role là customer
            $query = ImportFile::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('role', 'customer');
                });

            // Áp dụng các bộ lọc
            if ($request->filled('file_id')) {
                $query->where('id', $request->file_id);
            }

            if ($request->filled('customer')) {
                $customerSearch = trim($request->customer);

                $query->join('users', 'import_files.user_id', '=', 'users.id')
                    ->where(function ($q) use ($customerSearch) {
                        $q->where('users.first_name', 'like', '%' . $customerSearch . '%')
                            ->orWhere('users.last_name', 'like', '%' . $customerSearch . '%')
                            ->orWhere('users.email', 'like', '%' . $customerSearch . '%')
                            ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) like ?", ['%' . $customerSearch . '%']);
                    })
                    ->select('import_files.*'); // Chỉ select các cột từ import_files
            }

            if ($request->filled('warehouse')) {
                $query->where('warehouse', $request->warehouse);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            // Sắp xếp và phân trang
            $files = $query->orderBy('created_at', 'desc')->paginate(10);

            // Giữ lại các tham số filter trong pagination
            $files->appends($request->query());

            // Ghi log để debug
            Log::info('Customer Uploaded Files with filters:', [
                'filters' => $request->only(['file_id', 'customer', 'warehouse', 'status', 'date']),
                'count' => $files->count()
            ]);

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
    public function fileDetail($id)
    {
        $order = ImportFile::with(['excelOrders.items.mockups', 'excelOrders.items.designs'])->find($id);
        return view('customer.orders.file-detail', compact('order'));
    }
    public function orderCreate()
    {
        return view('customer.orders.order-create');
    }

    /**
     * Cập nhật status của ImportFile
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            // Validate request
            $request->validate([
                'status' => 'required|string|in:pending,processed,failed'
            ]);

            // Tìm ImportFile theo ID
            $importFile = ImportFile::findOrFail($id);

            // Lưu status cũ để log
            $oldStatus = $importFile->status;
            $newStatus = $request->status;

            // Cập nhật status
            $importFile->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

            // Log thông tin cập nhật
            Log::info('ImportFile status updated', [
                'file_id' => $id,
                'file_name' => $importFile->file_name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_by' => Auth::id(),
                'updated_by_email' => Auth::user()->email ?? 'Unknown'
            ]);

            // Nếu status được chuyển thành 'processed', có thể thực hiện thêm logic khác
            if ($newStatus === 'processed' && $oldStatus !== 'processed') {
                // Có thể thêm logic xử lý khi đơn hàng được duyệt
                // Ví dụ: gửi email thông báo, cập nhật các bảng liên quan, etc.
                $this->handleProcessedStatus($importFile);
            }

            return response()->json([
                'success' => true,
                'message' => "Status đã được cập nhật từ '{$oldStatus}' thành '{$newStatus}' thành công",
                'data' => [
                    'id' => $importFile->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_at' => $importFile->updated_at->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ImportFile not found for status update', [
                'file_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy file với ID: ' . $id
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating ImportFile status', [
                'file_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý logic khi status chuyển thành 'processed'
     */
    private function handleProcessedStatus(ImportFile $importFile)
    {
        try {
            // Cập nhật status của các ExcelOrder liên quan nếu cần
            $importFile->excelOrders()->update([
                'status' => 'processed'
            ]);

            // Có thể thêm logic gửi email thông báo cho khách hàng
            // $this->sendProcessedNotification($importFile);

            Log::info('Processed status logic completed', [
                'file_id' => $importFile->id,
                'excel_orders_updated' => $importFile->excelOrders()->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in handleProcessedStatus', [
                'file_id' => $importFile->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Thay đổi status từ pending sang processed
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            // Validate request
            $request->validate([
                'status' => 'required|string|in:processed'
            ]);

            // Tìm ImportFile theo ID
            $importFile = ImportFile::findOrFail($id);

            // Kiểm tra status hiện tại phải là pending
            if ($importFile->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể đổi status từ pending sang processed. Status hiện tại: ' . $importFile->status
                ], 400);
            }

            // Lưu thông tin cũ để log
            $oldStatus = $importFile->status;
            $newStatus = $request->status;

            // Cập nhật status
            $importFile->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

            // Cập nhật status của các ExcelOrder liên quan
            $updatedOrdersCount = $importFile->excelOrders()->update([
                'status' => 'processed',
                'updated_at' => now()
            ]);

            // Log thông tin cập nhật
            Log::info('ImportFile status changed from pending to processed', [
                'file_id' => $id,
                'file_name' => $importFile->file_name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'excel_orders_updated' => $updatedOrdersCount,
                'user_id' => $importFile->user_id,
                'customer_email' => $importFile->user->email ?? 'Unknown',
                'changed_by_admin_id' => Auth::id(),
                'changed_by_admin_email' => Auth::user()->email ?? 'Unknown'
            ]);

            // Có thể thêm logic gửi thông báo cho khách hàng ở đây
            // $this->notifyCustomerStatusChange($importFile, $oldStatus, $newStatus);

            return response()->json([
                'success' => true,
                'message' => "Status đã được đổi từ '{$oldStatus}' thành '{$newStatus}' thành công",
                'data' => [
                    'id' => $importFile->id,
                    'file_name' => $importFile->file_name,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'excel_orders_affected' => $updatedOrdersCount,
                    'updated_at' => $importFile->updated_at->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ImportFile not found for status change', [
                'file_id' => $id,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy file với ID: ' . $id
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error changing ImportFile status', [
                'file_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đổi status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gửi thông báo cho khách hàng khi status thay đổi (optional)
     */

    /**
     * Lấy title cho vị trí in theo format của ExcelOrderImportService
     */
    private function getPositionTitle(string $position): string
    {
        // Chuẩn hóa input trước
        $position = ucfirst(strtolower(trim($position)));
        if (in_array(strtolower($position), ['left sleeve', 'leftsleeve'])) {
            $position = 'Left sleeve';
        }
        if (in_array(strtolower($position), ['right sleeve', 'rightsleeve'])) {
            $position = 'Right sleeve';
        }

        return match ($position) {
            'Front' => 'Printing Front Side',
            'Back' => 'Printing Back Side',
            'Left sleeve' => 'Printing Left Sleeve Side',
            'Right sleeve' => 'Printing Right Sleeve Side',
            'Hem' => 'Printing Hem Side',
            default => 'Printing ' . $position . ' Side'
        };
    }

    /**
     * Tạo đơn hàng qua API với authentication token
     */

    public function searchCustomerOrders(Request $request)
    {
        try {
            $userId = Auth::id();

            // 1. Lấy ExcelOrder (kèm items, fulfillment) theo user hiện tại
            $query = ExcelOrder::with(['items', 'fulfillment'])
                ->where('created_by', $userId);

            // Thêm điều kiện tìm kiếm theo external_id nếu có
            if ($request->filled('external_id')) {
                $searchTerm = trim(preg_replace('/\s+/', '', $request->external_id));
                $query->whereRaw('REPLACE(REPLACE(REPLACE(TRIM(external_id), " ", ""), "\t", ""), "\n", "") LIKE ?', ["%{$searchTerm}%"]);
            }

            // Thêm điều kiện tìm kiếm theo khoảng thời gian
            if ($request->filled('created_at_min')) {
                $startDate = Carbon::parse($request->created_at_min)->startOfDay();
                $query->where('created_at', '>=', $startDate);
            }

            if ($request->filled('created_at_max')) {
                $endDate = Carbon::parse($request->created_at_max)->endOfDay();
                $query->where('created_at', '<=', $endDate);
            }

            $excelOrders = $query->orderBy('created_at', 'desc')->get();

            // 2. Lấy tất cả external_id từ excelOrders
            $externalIds = $excelOrders->pluck('external_id')->filter()->unique()->values()->all();

            // 3. Lấy mapping từ external_id → internal_id cho factory 'twofifteen'
            $orderMappings = [];
            if (!empty($externalIds)) {
                $orderMappings = OrderMapping::whereIn('external_id', $externalIds)
                    ->where('factory', 'twofifteen')
                    ->get()
                    ->keyBy('internal_id');
            }

            // 4. Gọi API Twofifteen để lấy chi tiết các đơn
            $factoryOrders = [];
            if (!empty($orderMappings)) {
                $internalIds = array_keys($orderMappings->toArray());
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
                ];
                $params['Signature'] = sha1(http_build_query($params) . $config['secretKey']);
                $url = "{$config['apiUrl']}/orders.php?" . http_build_query($params);

                $response = Http::get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $factoryOrders = data_get($data, 'orders', []);

                    $factoryOrders = array_map(function ($order) use ($orderMappings) {
                        $internalId = $order['id'] ?? null;
                        $mapping = $orderMappings[$internalId] ?? null;

                        if (!isset($order['created_at']) || empty($order['created_at'])) {
                            $order['created_at'] = $mapping?->excelOrder?->created_at?->toDateTimeString() ?? now()->toDateTimeString();
                        } else {
                            try {
                                $order['created_at'] = Carbon::parse($order['created_at'])->toDateTimeString();
                            } catch (\Exception $e) {
                                $order['created_at'] = now()->toDateTimeString();
                            }
                        }

                        return $order;
                    }, $factoryOrders);

                    usort($factoryOrders, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
                }
            }

            // 5. Tính thống kê
            $statistics = [
                'total_orders'     => $excelOrders->count(),
                'total_items'      => $excelOrders->sum(fn($o) => $o->items->sum('quantity')),
                'pending_orders'   => $excelOrders->where('status', 'pending')->count(),
                'processed_orders' => $excelOrders->where('status', 'processed')->count(),
            ];

            // 6. Kết hợp ExcelOrder + FactoryOrder
            $combinedOrders = [];
            foreach ($excelOrders as $excelOrder) {
                $mapping = $orderMappings->firstWhere('external_id', $excelOrder->external_id);
                $factoryOrder = null;

                if ($mapping) {
                    $factoryOrder = collect($factoryOrders)->firstWhere('id', $mapping->internal_id);
                }

                $combinedOrders[] = [
                    'excel_order'   => $excelOrder,
                    'factory_order' => $factoryOrder,
                    'created_at'    => $factoryOrder['created_at'] ?? $excelOrder->created_at->toDateTimeString(),
                ];
            }

            usort($combinedOrders, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

            return response()->json([
                'success' => true,
                'data' => [
                    'factoryOrders'  => $factoryOrders,
                    'combinedOrders' => $combinedOrders,
                    'statistics'     => $statistics,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchCustomerOrders:', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi khi tìm kiếm đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Tạo đơn hàng mới",
     *     description="API để tạo một đơn hàng mới với thông tin khách hàng, sản phẩm và thiết kế. Yêu cầu token xác thực Bearer.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu đơn hàng cần gửi trong định dạng JSON",
     *         @OA\JsonContent(
     *             required={"order_number", "customer_name", "customer_email", "address", "city", "postcode", "country", "products"},
     *             @OA\Property(
     *                 property="order_number",
     *                 type="string",
     *                 description="Mã đơn hàng duy nhất từ hệ thống bên ngoài",
     *                 example="ORDER123456",
     *                 maxLength=255
     *             ),
     *             @OA\Property(
     *                 property="store_name",
     *                 type="string",
     *                 description="Tên cửa hàng (tùy chọn)",
     *                 example="My Store",
     *                 maxLength=255,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="channel",
     *                 type="string",
     *                 description="Kênh bán hàng (tùy chọn, ví dụ: web, api, tiktok)",
     *                 example="api",
     *                 maxLength=255,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="customer_name",
     *                 type="string",
     *                 description="Tên đầy đủ của khách hàng",
     *                 example="Nguyen Van A",
     *                 maxLength=255
     *             ),
     *             @OA\Property(
     *                 property="customer_email",
     *                 type="string",
     *                 format="email",
     *                 description="Email của khách hàng",
     *                 example="a@gmail.com",
     *                 maxLength=255
     *             ),
     *             @OA\Property(
     *                 property="customer_phone",
     *                 type="string",
     *                 description="Số điện thoại của khách hàng (tùy chọn)",
     *                 example="0123456789",
     *                 maxLength=20,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 description="Địa chỉ giao hàng chính",
     *                 example="123 Đường ABC",
     *                 maxLength=500
     *             ),
     *             @OA\Property(
     *                 property="address_2",
     *                 type="string",
     *                 description="Địa chỉ bổ sung (tùy chọn)",
     *                 example="Tầng 4, Tòa nhà XYZ",
     *                 maxLength=500,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="house_number",
     *                 type="string",
     *                 description="Số nhà (tùy chọn)",
     *                 example="12A",
     *                 maxLength=50,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="mailbox_number",
     *                 type="string",
     *                 description="Số hộp thư (tùy chọn)",
     *                 example="MB123",
     *                 maxLength=50,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="city",
     *                 type="string",
     *                 description="Thành phố",
     *                 example="Hà Nội",
     *                 maxLength=255
     *             ),
     *             @OA\Property(
     *                 property="state",
     *                 type="string",
     *                 description="Tiểu bang hoặc khu vực (tùy chọn)",
     *                 example="",
     *                 maxLength=255,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="postcode",
     *                 type="string",
     *                 description="Mã bưu điện",
     *                 example="100000",
     *                 maxLength=20
     *             ),
     *             @OA\Property(
     *                 property="country",
     *                 type="string",
     *                 description="Mã quốc gia (2 ký tự, theo chuẩn ISO 3166-1 alpha-2)",
     *                 example="VN",
     *                 maxLength=2
     *             ),
     *             @OA\Property(
     *                 property="shipping_method",
     *                 type="string",
     *                 description="Phương thức vận chuyển (tùy chọn, ví dụ: standard, tiktok_label)",
     *                 example="tiktok_label",
     *                 maxLength=100,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="order_note",
     *                 type="string",
     *                 description="Ghi chú cho đơn hàng (tùy chọn)",
     *                 example="Giao hàng vào buổi sáng",
     *                 maxLength=1000,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="Danh sách sản phẩm trong đơn hàng",
     *                 minItems=1,
     *                 @OA\Items(
     *                     required={"quantity", "part_number", "designs"},
     *                     @OA\Property(
     *                         property="campaign_title",
     *                         type="string",
     *                         description="Tiêu đề chiến dịch (tùy chọn)",
     *                         example="Summer T-Shirt Campaign",
     *                         maxLength=255,
     *                         nullable=true
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="integer",
     *                         description="Số lượng sản phẩm",
     *                         example=2,
     *                         minimum=1
     *                     ),
     *                     @OA\Property(
     *                         property="part_number",
     *                         type="string",
     *                         description="Mã sản phẩm (SKU, twofifteen_sku hoặc flashship_sku)",
     *                         example="SKU123",
     *                         maxLength=255
     *                     ),
     *                     @OA\Property(
     *                         property="designs",
     *                         type="array",
     *                         description="Danh sách thiết kế cho sản phẩm",
     *                         minItems=1,
     *                         @OA\Items(
     *                             required={"file_url", "print_space"},
     *                             @OA\Property(
     *                                 property="file_url",
     *                                 type="string",
     *                                 format="uri",
     *                                 description="URL của file thiết kế",
     *                                 example="https://domain.com/design1.png"
     *                             ),
     *                             @OA\Property(
     *                                 property="print_space",
     *                                 type="string",
     *                                 description="Vị trí in (ví dụ: Front, Back)",
     *                                 example="Front"
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="mockups",
     *                         type="array",
     *                         description="Danh sách mockup cho sản phẩm (tùy chọn)",
     *                         nullable=true,
     *                         @OA\Items(
     *                             required={"file_url", "print_space"},
     *                             @OA\Property(
     *                                 property="file_url",
     *                                 type="string",
     *                                 format="uri",
     *                                 description="URL của file mockup",
     *                                 example="https://domain.com/mockup1.png"
     *                             ),
     *                             @OA\Property(
     *                                 property="print_space",
     *                                 type="string",
     *                                 description="Vị trí in của mockup",
     *                                 example="Front"
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đơn hàng được tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_number", type="string", example="ORDER123456"),
     *                 @OA\Property(property="status", type="string", example="on hold"),
     *                 @OA\Property(property="store_name", type="string", example="My Store", nullable=true),
     *                 @OA\Property(property="channel", type="string", example="api", nullable=true),
     *                 @OA\Property(property="customer_email", type="string", example="a@gmail.com"),
     *                 @OA\Property(
     *                     property="shipping_address",
     *                     type="object",
     *                     @OA\Property(property="customer_name", type="string", example="Nguyen Van A"),
     *                     @OA\Property(property="company", type="string", example="My Store", nullable=true),
     *                     @OA\Property(property="address_1", type="string", example="123 Đường ABC"),
     *                     @OA\Property(property="address_2", type="string", example="Tầng 4, Tòa nhà XYZ", nullable=true),
     *                     @OA\Property(property="city", type="string", example="Hà Nội"),
     *                     @OA\Property(property="county", type="string", example="", nullable=true),
     *                     @OA\Property(property="postcode", type="string", example="100000"),
     *                     @OA\Property(property="country", type="string", example="VN"),
     *                     @OA\Property(property="phone", type="string", example="0123456789", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="part_number", type="string", example="SKU123"),
     *                         @OA\Property(property="title", type="string", example="Summer T-Shirt Campaign"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="print_price", type="string", example="10.00"),
     *                         @OA\Property(property="total_price", type="string", example="20.00"),
     *                         @OA\Property(
     *                             property="designs",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="file_url", type="string", example="https://domain.com/design1.png"),
     *                                 @OA\Property(property="print_space", type="string", example="Front")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="mockups",
     *                             type="array",
     *                             nullable=true,
     *                             @OA\Items(
     *                                 @OA\Property(property="file_url", type="string", example="https://domain.com/mockup1.png"),
     *                                 @OA\Property(property="print_space", type="string", example="Front")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="label_url", type="string", example="http://example.com/label.pdf", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-11T11:21:00+07:00"),
     *                 @OA\Property(property="total_price", type="string", example="20.00"),
     *                 @OA\Property(
     *                     property="transaction",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="amount", type="string", example="20.00"),
     *                     @OA\Property(property="status", type="string", example="approved"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-11T11:21:00+07:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi dữ liệu đầu vào hoặc số dư ví không đủ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Chi tiết lỗi validation (nếu có)",
     *                 nullable=true,
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string", example="The order number has already been taken")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Lỗi xác thực token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid API token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while creating the order"),
     *             @OA\Property(property="error", type="string", example="Database connection failed", nullable=true)
     *         )
     *     )
     * )
     */
    public function createOrder(Request $request)
    {
        try {
            // 1. Xác thực API token từ header Authorization
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token is required'
                ], 401);
            }

            $user = User::where('api_token', $token)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API token'
                ], 401);
            }

            // 2. Validate request data
            $validated = $request->validate([
                'order_number' => 'required|string|max:255', // Bỏ unique constraint
                'store_name' => 'nullable|string|max:255',
                'channel' => 'nullable|string|max:255',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'address' => 'required|string|max:500',
                'address_2' => 'nullable|string|max:500',
                'house_number' => 'nullable|string|max:50',
                'mailbox_number' => 'nullable|string|max:50',
                'city' => 'required|string|max:255',
                'state' => 'nullable|string|max:255',
                'postcode' => 'required|string|max:20',
                'country' => 'required|string|max:2',
                'shipping_method' => 'nullable|string|max:100',
                'order_note' => 'nullable|string|max:1000',
                'products' => 'required|array|min:1',
                'products.*.campaign_title' => 'nullable|string|max:255',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.part_number' => ['required', 'string', 'max:255', new ValidPartNumber],
                'products.*.designs' => 'required|array|min:1',
                'products.*.designs.*.file_url' => 'required_with:products.*.designs|url',
                'products.*.designs.*.print_space' => ['required', 'string', new ValidPrintSpace],
                'products.*.mockups' => 'nullable|array',
                'products.*.mockups.*.file_url' => 'required_with:products.*.mockups|url',
                'products.*.mockups.*.print_space' => ['required', 'string', new ValidPrintSpace],
            ]);

            DB::beginTransaction();

            // Kiểm tra external_id
            $existingOrder = ExcelOrder::where('external_id', $validated['order_number'])->first();
            if ($existingOrder) {
                if ($existingOrder->status !== 'cancelled') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order number already exists',
                        'existing_order' => [
                            'id' => $existingOrder->id,
                            'status' => $existingOrder->status
                        ]
                    ], 400);
                }
            }

            // Chuẩn bị danh sách tất cả items với thông tin cần thiết
            $allItems = [];
            foreach ($validated['products'] as $productIndex => $product) {
                $variant = ProductVariant::where('sku', $product['part_number'])
                    ->orWhere('twofifteen_sku', $product['part_number'])
                    ->orWhere('flashship_sku', $product['part_number'])
                    ->first();

                if ($variant) {
                    // Chuyển đổi part_number sang twofifteen_sku
                    $twofifteenSku = $variant->twofifteen_sku;

                    $allItems[] = [
                        'variant' => $variant,
                        'quantity' => $product['quantity'],
                        'product' => $product,
                        'original_index' => $productIndex,
                        'first_item_price' => $variant->getFirstItemPrice($validated['shipping_method'] ?? null),
                        'part_number' => $twofifteenSku // Sử dụng twofifteen_sku thay vì part_number gốc
                    ];
                }
            }

            // Tính tổng số tiền cần trừ
            $totalAmount = 0;
            $orderTotalPrices = [];
            $itemPrices = [];
            $itemPriceBreakdowns = []; // Mới thêm để lưu chi tiết giá

            // Logic tính giá mới theo yêu cầu:
            // 1. Tìm item có giá cao nhất trong toàn bộ đơn hàng
            $highestPriceItem = null;
            $highestPrice = 0;
            foreach ($allItems as $item) {
                if ($item['first_item_price'] > $highestPrice) {
                    $highestPrice = $item['first_item_price'];
                    $highestPriceItem = $item;
                }
            }

            // 2. Gom nhóm theo part_number để xử lý logic đặc biệt cho items giống nhau
            $productsByPartNumber = [];
            foreach ($allItems as $item) {
                $partNumber = $item['part_number'];
                if (!isset($productsByPartNumber[$partNumber])) {
                    $productsByPartNumber[$partNumber] = [];
                }
                $productsByPartNumber[$partNumber][] = $item;
            }

            // 3. Xử lý tính giá cho từng item (chỉ có 1 item duy nhất tính giá "1st item")
            $firstItemProcessed = false; // Đánh dấu đã xử lý item đầu tiên chưa

            foreach ($productsByPartNumber as $partNumber => $items) {
                foreach ($items as $index => $item) {
                    $variant = $item['variant'];
                    $quantity = $item['quantity'];
                    $originalIndex = $item['original_index'];

                    // Kiểm tra xem có phải là item có giá cao nhất và chưa được xử lý chưa
                    $isFirstItem = (!$firstItemProcessed && $highestPriceItem &&
                        $highestPriceItem['original_index'] === $originalIndex);

                    if ($isFirstItem) {
                        $firstItemProcessed = true;
                    }

                    // Tính giá mixed khi quantity > 1
                    $itemTotal = 0;
                    $averagePrice = 0;
                    $priceBreakdown = [];

                    if ($isFirstItem && $quantity > 1) {
                        // Trường hợp đặc biệt: item có giá cao nhất và quantity > 1
                        // 1 item tính giá position 1, các item còn lại tính giá position 2
                        $priceInfo1 = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, 1);
                        $priceInfo2 = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, 2);

                        if ($priceInfo1['shipping_price_found'] && $priceInfo2['shipping_price_found']) {
                            $firstPrice = round($priceInfo1['print_price'], 2);
                            $secondPrice = round($priceInfo2['print_price'], 2);
                            $itemTotal = $firstPrice + ($secondPrice * ($quantity - 1));
                            $itemTotal = round($itemTotal, 2);
                            $averagePrice = round($itemTotal / $quantity, 2); // Giá trung bình để lưu vào database

                            // Lưu chi tiết giá breakdown
                            $priceBreakdown = [
                                'first_item_price' => $firstPrice,
                                'additional_item_price' => $secondPrice,
                                'quantity' => $quantity,
                                'breakdown' => "1x{$firstPrice} + " . ($quantity - 1) . "x{$secondPrice}"
                            ];
                        }
                    } else {
                        // Trường hợp thông thường: tất cả items tính cùng 1 giá
                        $position = $isFirstItem ? 1 : 2;
                        $priceInfo = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, $position);

                        if ($priceInfo['shipping_price_found']) {
                            $unitPrice = round($priceInfo['print_price'], 2);
                            $itemTotal = $unitPrice * $quantity;
                            $itemTotal = round($itemTotal, 2);
                            $averagePrice = $unitPrice;

                            // Lưu chi tiết giá breakdown
                            $priceBreakdown = [
                                'unit_price' => $unitPrice,
                                'quantity' => $quantity,
                                'is_first_item' => $isFirstItem,
                                'breakdown' => $quantity . "x" . $unitPrice
                            ];
                        }
                    }

                    if ($itemTotal > 0) {
                        $totalAmount += $itemTotal;
                        $orderTotalPrices[$originalIndex] = $itemTotal;
                        $itemPrices[$originalIndex] = $averagePrice;
                        $itemPriceBreakdowns[$originalIndex] = $priceBreakdown;
                    }
                }
            }

            // Làm tròn totalAmount đến 2 chữ số thập phân
            $totalAmount = round($totalAmount, 2);

            // Log pricing logic để debug
            $this->logPricingLogic($validated, $allItems, $itemPriceBreakdowns, $totalAmount);

            // Kiểm tra và trừ tiền từ ví
            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet || !$wallet->hasEnoughBalance($totalAmount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance in wallet',
                    'required_amount' => $totalAmount,
                    'current_balance' => $wallet ? $wallet->balance : 0
                ], 400);
            }

            // Tạo giao dịch trừ tiền
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_code' => 'ORDER-' . time(),
                'type' => Transaction::TYPE_DEDUCT,
                'method' => Transaction::METHOD_VND,
                'amount' => $totalAmount,
                'status' => Transaction::STATUS_APPROVED,
                'note' => 'Deduct for API order: ' . $validated['order_number'],
                'approved_at' => now(),
            ]);

            // Trừ tiền từ ví
            if (!$wallet->withdraw($totalAmount)) {
                $transaction->reject('Failed to withdraw from wallet');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process payment'
                ], 500);
            }

            // 3. Tạo ExcelOrder chính
            $comment = $validated['order_note'] ?? '';

            // Kiểm tra nếu shipping_method là tiktok_label
            if (($validated['shipping_method'] ?? '') === 'tiktok_label') {
                $comment .= ' Shipping label: http://example.com/label.pdf'; // Thay thế bằng link thực tế
            }

            $order = ExcelOrder::create([
                'external_id' => $validated['order_number'],
                'brand' => $validated['store_name'],
                'channel' => $validated['channel'] ?? 'api',
                'buyer_email' => $validated['customer_email'],
                'first_name' => explode(' ', $validated['customer_name'])[0],
                'last_name' => substr($validated['customer_name'], strpos($validated['customer_name'], ' ') + 1) ?: '',
                'company' => $validated['store_name'],
                'address1' => $validated['address'],
                'address2' => $validated['address_2'],
                'city' => $validated['city'],
                'county' => $validated['state'],
                'post_code' => $validated['postcode'],
                'country' => $validated['country'],
                'phone1' => $validated['customer_phone'],
                'phone2' => null,
                'comment' => $comment,
                'shipping_method' => $validated['shipping_method'] ?? null,
                'status' => 'on hold',
                'warehouse' => 'UK',
                'created_by' => $user->id,
                'import_file_id' => null, // Không có file import cho API
            ]);

            // 4. Tạo các ExcelOrderItem và liên kết designs + mockups
            foreach ($validated['products'] as $index => $product) {
                // Tìm ProductVariant dựa vào part_number
                $variant = ProductVariant::where('sku', $product['part_number'])
                    ->orWhere('twofifteen_sku', $product['part_number'])
                    ->orWhere('flashship_sku', $product['part_number'])
                    ->first();

                $productId = null;
                $partNumber = $product['part_number'];
                if ($variant) {
                    $productId = $variant->product_id;
                    // Sử dụng twofifteen_sku cho part_number trong ExcelOrderItem
                    $partNumber = $variant->twofifteen_sku;
                }

                $orderItem = ExcelOrderItem::create([
                    'excel_order_id' => $order->id,
                    'part_number' => $partNumber,
                    'title' => $product['campaign_title'] ?? 'No title',
                    'quantity' => $product['quantity'],
                    'print_price' => $itemPrices[$index] ?? 0,
                    'product_id' => $productId,
                    'description' => json_encode($product['options'] ?? []),
                ]);

                // 5. Tạo designs cho item này
                foreach ($product['designs'] as $design) {
                    ExcelOrderDesign::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $this->getPositionTitle($design['print_space']),
                        'url' => $design['file_url']
                    ]);
                }

                // 6. Tạo mockups cho item này (nếu có)
                if (!empty($product['mockups'])) {
                    foreach ($product['mockups'] as $mockup) {
                        ExcelOrderMockup::create([
                            'excel_order_item_id' => $orderItem->id,
                            'title' => $this->getPositionTitle($mockup['print_space']),
                            'url' => $mockup['file_url']
                        ]);
                    }
                }
            }

            DB::commit();

            // 7. Load order với tất cả relationships để trả về
            $order->load(['items.designs', 'items.mockups', 'creator']);

            Log::info('Order created successfully via API:', [
                'order_id' => $order->id,
                'external_id' => $order->external_id,
                'user_id' => $user->id,
                'products_count' => count($validated['products']),
                'total_amount' => $totalAmount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->external_id,
                    'status' => $order->status,
                    'store_name' => $order->brand,
                    'channel' => $order->channel,
                    'customer_email' => $order->buyer_email,
                    'shipping_address' => [
                        'customer_name' => $order->first_name . ' ' . $order->last_name,
                        'company' => $order->company,
                        'address_1' => $order->address1,
                        'address_2' => $order->address2,
                        'city' => $order->city,
                        'county' => $order->county,
                        'postcode' => $order->post_code,
                        'country' => $order->country,
                        'phone' => $order->phone1,
                    ],
                    'products' => $order->items->map(function ($item, $index) use ($orderTotalPrices, $itemPriceBreakdowns) {
                        $baseData = [
                            'part_number' => $item->part_number,
                            'title' => $item->title,
                            'quantity' => $item->quantity,
                            'print_price' => number_format($item->print_price, 2, '.', ''),
                            'total_price' => number_format($orderTotalPrices[$index] ?? 0, 2, '.', ''),
                            'designs' => $item->designs->map(function ($design) {
                                return [
                                    'file_url' => $design->url,
                                    'print_space' => $design->title,
                                ];
                            }),
                            'mockups' => $item->mockups->map(function ($mockup) {
                                return [
                                    'file_url' => $mockup->url,
                                    'print_space' => $mockup->title,
                                ];
                            }),
                        ];


                        return $baseData;
                    }),
                    'label_url' => $order->comment,
                    'created_at' => $order->created_at->toISOString(),
                    'total_price' => number_format($totalAmount, 2, '.', ''),
                    'transaction' => [
                        'id' => $transaction->id,
                        'amount' => number_format($totalAmount, 2, '.', ''),
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at->toISOString()
                    ]

                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating order via API:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_token' => $request->bearerToken()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the order',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    /**
     * Cancel an order if it's in 'on hold' status
     */
    /**
     * @OA\Post(
     *     path="/api/orders/{orderId}/cancel",
     *     summary="Hủy đơn hàng",
     *     description="API để hủy một đơn hàng hiện có dựa trên ID đơn hàng. Yêu cầu token xác thực Bearer và đơn hàng phải ở trạng thái 'on hold' hoặc 'pending'.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         description="ID của đơn hàng cần hủy",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đơn hàng đã được hủy và hoàn tiền thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order cancelled and refunded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="order_id", type="integer", example=1),
     *                 @OA\Property(property="order_number", type="string", example="ORDER123456"),
     *                 @OA\Property(property="status", type="string", example="cancelled"),
     *                 @OA\Property(
     *                     property="refund_transaction",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="amount", type="string", example="20.00"),
     *                     @OA\Property(property="status", type="string", example="approved"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-11T11:35:00+07:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi do đơn hàng không hợp lệ hoặc trạng thái không cho phép hủy",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot cancel order in current status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Lỗi xác thực token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid API token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đơn hàng",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to cancel order"),
     *             @OA\Property(property="error", type="string", example="Transaction not found", nullable=true)
     *         )
     *     )
     * )
     */
    public function cancelOrder(Request $request, $orderId)
    {
        try {
            // 1. Xác thực API token
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token is required'
                ], 401);
            }

            $user = User::where('api_token', $token)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API token'
                ], 401);
            }

            // 2. Tìm đơn hàng
            $order = ExcelOrder::where('id', $orderId)
                ->where('created_by', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // 3. Kiểm tra trạng thái đơn hàng
            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already cancelled'
                ], 400);
            }

            if (!in_array($order->status, ['on hold', 'pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel order in current status'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // 4. Tìm giao dịch trừ tiền
                $transaction = Transaction::where('user_id', $user->id)
                    ->where('type', Transaction::TYPE_DEDUCT)
                    ->where('note', 'like', '%' . $order->external_id)
                    ->where('status', Transaction::STATUS_APPROVED)
                    ->whereNull('refunded_at')
                    ->first();

                if (!$transaction) {
                    throw new \Exception('Transaction not found');
                }

                // 5. Hoàn tiền cho khách hàng
                $refundTransaction = $transaction->refund($user->id, "Refund for cancelled order: {$order->external_id}");

                if (!$refundTransaction) {
                    throw new \Exception('Failed to process refund');
                }

                // 6. Cập nhật trạng thái đơn hàng
                $order->update([
                    'status' => 'cancelled',
                    'comment' => $order->comment . "\nCancelled at: " . now()->format('Y-m-d H:i:s')
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled and refunded successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->external_id,
                        'status' => $order->status,
                        'refund_transaction' => [
                            'id' => $refundTransaction->id,
                            'amount' => number_format($refundTransaction->amount, 2, '.', ''),
                            'status' => $refundTransaction->status,
                            'created_at' => $refundTransaction->created_at->toISOString()
                        ]
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details
     */
    /**
     * @OA\Get(
     *     path="/api/orders/{orderId}",
     *     summary="Lấy chi tiết đơn hàng",
     *     description="API để lấy thông tin chi tiết của một đơn hàng dựa trên ID. Yêu cầu token xác thực Bearer và đơn hàng phải thuộc về người dùng.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         description="ID của đơn hàng cần lấy chi tiết",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy chi tiết đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_number", type="string", example="ORDER123456"),
     *                 @OA\Property(property="status", type="string", example="on hold"),
     *                 @OA\Property(property="store_name", type="string", example="My Store", nullable=true),
     *                 @OA\Property(property="channel", type="string", example="api", nullable=true),
     *                 @OA\Property(property="customer_email", type="string", example="a@gmail.com"),
     *                 @OA\Property(
     *                     property="shipping_address",
     *                     type="object",
     *                     @OA\Property(property="customer_name", type="string", example="Nguyen Van A"),
     *                     @OA\Property(property="company", type="string", example="My Store", nullable=true),
     *                     @OA\Property(property="address_1", type="string", example="123 Đường ABC"),
     *                     @OA\Property(property="address_2", type="string", example="Tầng 4, Tòa nhà XYZ", nullable=true),
     *                     @OA\Property(property="city", type="string", example="Hà Nội"),
     *                     @OA\Property(property="county", type="string", example="", nullable=true),
     *                     @OA\Property(property="postcode", type="string", example="100000"),
     *                     @OA\Property(property="country", type="string", example="VN"),
     *                     @OA\Property(property="phone", type="string", example="0123456789", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="part_number", type="string", example="SKU123"),
     *                         @OA\Property(property="title", type="string", example="Summer T-Shirt Campaign"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="print_price", type="string", example="10.00"),
     *                         @OA\Property(property="total_price", type="string", example="20.00"),
     *                         @OA\Property(
     *                             property="designs",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="title", type="string", example="Front"),
     *                                 @OA\Property(property="url", type="string", example="https://domain.com/design1.png")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="mockups",
     *                             type="array",
     *                             nullable=true,
     *                             @OA\Items(
     *                                 @OA\Property(property="title", type="string", example="Front"),
     *                                 @OA\Property(property="url", type="string", example="https://domain.com/mockup1.png")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="label_url", type="string", example="http://example.com/label.pdf", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-11T11:38:00+07:00"),
     *                 @OA\Property(property="total_price", type="string", example="20.00"),
     *                 @OA\Property(property="tracking_number", type="string", example="TRK123456", nullable=true),
     *                 @OA\Property(property="internal_order_id", type="string", example="INT123456", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Lỗi xác thực token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid API token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đơn hàng",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while getting order details"),
     *             @OA\Property(property="error", type="string", example="Database connection failed", nullable=true)
     *         )
     *     )
     * )
     */
    public function getOrderDetailsApi(Request $request, $orderId)
    {
        try {
            // 1. Xác thực API token từ header Authorization
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token is required'
                ], 401);
            }

            $user = User::where('api_token', $token)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API token'
                ], 401);
            }

            // 2. Tìm đơn hàng và load relationships
            $order = ExcelOrder::with(['items.designs', 'items.mockups', 'creator'])
                ->where('id', $orderId)
                ->where('created_by', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // 3. Lấy mapping từ external_id → internal_id cho factory 'twofifteen'
            $mapping = OrderMapping::where('external_id', $order->external_id)
                ->where('factory', 'twofifteen')
                ->first();



            // 5. Tính tổng tiền của tất cả items
            $totalPrice = round($order->items->sum(function ($item) {
                return $item->print_price * $item->quantity;
            }), 2);


            // 6. Format response data
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->external_id,
                    'status' => $order->status,
                    'store_name' => $order->brand,
                    'channel' => $order->channel,
                    'customer_email' => $order->buyer_email,
                    'shipping_address' => [
                        'customer_name' => $order->first_name . ' ' . $order->last_name,
                        'company' => $order->company,
                        'address_1' => $order->address1,
                        'address_2' => $order->address2,
                        'city' => $order->city,
                        'county' => $order->county,
                        'postcode' => $order->post_code,
                        'country' => $order->country,
                        'phone' => $order->phone1,
                    ],
                    'products' => $order->items->map(function ($item) {
                        return [
                            'part_number' => $item->part_number,
                            'title' => $item->title,
                            'quantity' => $item->quantity,
                            'print_price' => $item->print_price,
                            'total_price' => $item->print_price * $item->quantity,
                            'designs' => $item->designs->map(function ($design) {
                                return [
                                    'title' => $design->title,
                                    'url' => $design->url,
                                ];
                            }),
                            'mockups' => $item->mockups->map(function ($mockup) {
                                return [
                                    'title' => $mockup->title,
                                    'url' => $mockup->url,
                                ];
                            }),
                        ];
                    }),
                    'label_url' => $order->comment,
                    'created_at' => $order->created_at->toISOString(),
                    'total_price' => $totalPrice,
                    'tracking_number' => $order->tracking_number ?? ""
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting order details via API:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_token' => $request->bearerToken()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while getting order details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy đơn hàng và hoàn tiền cho khách hàng
     */

    /**
     * Lấy danh sách đơn hàng được tạo qua API (file_id = null)
     */
    // public function getApiOrders(Request $request)
    // {
    //     try {
    //         $userId = Auth::id();

    //         // Khởi tạo query với điều kiện file_id là null và user hiện tại
    //         $query = ExcelOrder::with(['items', 'creator'])
    //             ->where('created_by', $userId)
    //             ->whereNull('import_file_id');

    //         // Thêm điều kiện tìm kiếm theo external_id nếu có
    //         if ($request->filled('external_id')) {
    //             $searchTerm = trim($request->external_id);
    //             $query->where('external_id', 'LIKE', "%{$searchTerm}%");
    //         }

    //         // Thêm điều kiện tìm kiếm theo trạng thái
    //         if ($request->filled('status')) {
    //             $query->where('status', $request->status);
    //         }

    //         // Thêm điều kiện tìm kiếm theo khoảng thời gian
    //         if ($request->filled('created_at_min')) {
    //             $startDate = Carbon::parse($request->created_at_min)->startOfDay();
    //             $query->where('created_at', '>=', $startDate);
    //         }

    //         if ($request->filled('created_at_max')) {
    //             $endDate = Carbon::parse($request->created_at_max)->endOfDay();
    //             $query->where('created_at', '<=', $endDate);
    //         }

    //         // Sắp xếp và phân trang
    //         $orders = $query->orderBy('created_at', 'desc')
    //             ->paginate(10);

    //         // Tính thống kê
    //         $statistics = [
    //             'total_orders' => $orders->total(),
    //             'total_items' => $orders->sum(function ($order) {
    //                 return $order->items->sum('quantity');
    //             }),
    //             'pending_orders' => $orders->where('status', 'pending')->count(),
    //             'processed_orders' => $orders->where('status', 'processed')->count(),
    //             'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
    //         ];

    //         // Trả về view với dữ liệu
    //         return view('customer.orders.api-order', [
    //             'orders' => $orders,
    //             'statistics' => $statistics
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Error getting API orders list:', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'user_id' => Auth::id()
    //         ]);

    //         return back()->with('error', 'Có lỗi khi tải danh sách đơn hàng: ' . $e->getMessage());
    //     }
    // }

    /**
     * Lấy danh sách đơn hàng được tạo qua API (file_id = null) dành cho admin
     */
    public function getAdminApiOrders(Request $request)
    {
        try {
            // Khởi tạo query với điều kiện file_id là null
            $query = ExcelOrder::with(['items', 'creator'])
                ->whereNull('import_file_id');

            // Thêm điều kiện tìm kiếm theo external_id nếu có
            if ($request->filled('external_id')) {
                $searchTerm = trim($request->external_id);
                $query->where('external_id', 'LIKE', "%{$searchTerm}%");
            }

            // Thêm điều kiện tìm kiếm theo email khách hàng
            if ($request->filled('customer_email')) {
                $searchTerm = trim($request->customer_email);
                $query->where('buyer_email', 'LIKE', "%{$searchTerm}%");
            }

            // Thêm điều kiện tìm kiếm theo tên khách hàng
            if ($request->filled('customer_name')) {
                $searchTerm = trim($request->customer_name);
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
                });
            }

            // Thêm điều kiện tìm kiếm theo trạng thái
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Thêm điều kiện tìm kiếm theo khoảng thời gian
            if ($request->filled('created_at_min')) {
                $startDate = Carbon::parse($request->created_at_min)->startOfDay();
                $query->where('created_at', '>=', $startDate);
            }

            if ($request->filled('created_at_max')) {
                $endDate = Carbon::parse($request->created_at_max)->endOfDay();
                $query->where('created_at', '<=', $endDate);
            }

            // Thêm điều kiện tìm kiếm theo warehouse
            if ($request->filled('warehouse')) {
                $query->where('warehouse', $request->warehouse);
            }

            // Sắp xếp và phân trang
            $orders = $query->orderBy('created_at', 'desc')
                ->paginate(20);

            // Tính thống kê
            $statistics = [
                'total_orders' => $orders->total(),
                'total_items' => $orders->sum(function ($order) {
                    return $order->items->sum('quantity');
                }),
                'pending_orders' => $orders->where('status', 'pending')->count(),
                'processed_orders' => $orders->where('status', 'processed')->count(),
                'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
                'on_hold_orders' => $orders->where('status', 'on hold')->count(),
                'total_amount' => $orders->sum(function ($order) {
                    return $order->items->sum(function ($item) {
                        return $item->print_price * $item->quantity;
                    });
                })
            ];

            // Lấy danh sách các warehouse có sẵn
            $warehouses = ExcelOrder::whereNull('import_file_id')
                ->distinct()
                ->pluck('warehouse');

            // Lấy danh sách các trạng thái có sẵn
            $statuses = ExcelOrder::whereNull('import_file_id')
                ->distinct()
                ->pluck('status');

            // Trả về view với dữ liệu
            return view('admin.orders.api-order-list', [

                'orders' => $orders,
                'statistics' => $statistics,
                'warehouses' => $warehouses,
                'statuses' => $statuses,
                'filters' => $request->only([
                    'external_id',
                    'customer_email',
                    'customer_name',
                    'status',
                    'warehouse',
                    'created_at_min',
                    'created_at_max'
                ])
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting admin API orders list:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi khi tải danh sách đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function showOrder(ExcelOrder $order)
    {
        try {
            // Load các relationship cần thiết
            $order->load(['items', 'creator', 'fulfillment']);

            // Tính toán tổng tiền và làm tròn đến 2 chữ số thập phân
            $totalAmount = round($order->items->sum(function ($item) {
                return $item->print_price * $item->quantity;
            }), 2);

            // Lấy thông tin giao dịch nếu có
            $transaction = null;
            if ($order->status === 'processed') {
                $transaction = Transaction::where('order_id', $order->id)
                    ->where('type', 'order')
                    ->first();
            }

            return view('admin.orders.show', [
                'order' => $order,
                'totalAmount' => $totalAmount,
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing order details:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
                'admin_id' => Auth::id()
            ]);

            return back()->with('error', 'Có lỗi khi tải thông tin đơn hàng: ' . $e->getMessage());
        }
    }

    /**
     * Log chi tiết về logic tính giá để debug
     */
    private function logPricingLogic($validated, $allItems, $itemPriceBreakdowns, $totalAmount, $orderNumber = null)
    {
        Log::info('=== PRICING LOGIC BREAKDOWN ===', [
            'order_number' => $orderNumber ?? $validated['order_number'] ?? 'N/A',
            'shipping_method' => $validated['shipping_method'] ?? 'N/A'
        ]);

        foreach ($allItems as $index => $item) {
            $breakdown = $itemPriceBreakdowns[$index] ?? null;

            Log::info("Product {$index}: {$item['part_number']}", [
                'quantity' => $item['quantity'],
                'first_item_price' => $item['first_item_price'],
                'pricing_breakdown' => $breakdown
            ]);
        }

        Log::info('Total amount calculated:', ['total' => $totalAmount]);
        Log::info('=== END PRICING BREAKDOWN ===');
    }

    /**
     * Cập nhật đơn hàng qua API với authentication token
     */
    /**
     * @OA\Put(
     *     path="/api/orders/{orderId}",
     *     summary="Cập nhật đơn hàng",
     *     description="API để cập nhật thông tin đơn hàng hiện có dựa trên ID. Yêu cầu token xác thực Bearer và đơn hàng phải ở trạng thái 'on hold' hoặc 'pending'.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         description="ID của đơn hàng cần cập nhật",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Dữ liệu đơn hàng cần cập nhật trong định dạng JSON. Tất cả các trường đều tùy chọn.",
     *         @OA\JsonContent(
     *             @OA\Property(property="order_number", type="string", description="Mã đơn hàng mới", example="ORDER123457", maxLength=255, nullable=true),
     *             @OA\Property(property="store_name", type="string", description="Tên cửa hàng", example="New Store", maxLength=255, nullable=true),
     *             @OA\Property(property="channel", type="string", description="Kênh bán hàng", example="web", maxLength=255, nullable=true),
     *             @OA\Property(property="customer_name", type="string", description="Tên khách hàng", example="Nguyen Van B", maxLength=255, nullable=true),
     *             @OA\Property(property="customer_email", type="string", format="email", description="Email khách hàng", example="b@gmail.com", maxLength=255, nullable=true),
     *             @OA\Property(property="customer_phone", type="string", description="Số điện thoại khách hàng", example="0987654321", maxLength=20, nullable=true),
     *             @OA\Property(property="address", type="string", description="Địa chỉ giao hàng chính", example="456 Đường XYZ", maxLength=500, nullable=true),
     *             @OA\Property(property="address_2", type="string", description="Địa chỉ bổ sung", example="Tầng 5", maxLength=500, nullable=true),
     *             @OA\Property(property="house_number", type="string", description="Số nhà", example="15B", maxLength=50, nullable=true),
     *             @OA\Property(property="mailbox_number", type="string", description="Số hộp thư", example="MB456", maxLength=50, nullable=true),
     *             @OA\Property(property="city", type="string", description="Thành phố", example="Hồ Chí Minh", maxLength=255, nullable=true),
     *             @OA\Property(property="state", type="string", description="Tiểu bang hoặc khu vực", example="", maxLength=255, nullable=true),
     *             @OA\Property(property="postcode", type="string", description="Mã bưu điện", example="700000", maxLength=20, nullable=true),
     *             @OA\Property(property="country", type="string", description="Mã quốc gia (ISO 3166-1 alpha-2)", example="VN", maxLength=2, nullable=true),
     *             @OA\Property(property="shipping_method", type="string", description="Phương thức vận chuyển", example="tiktok_label", maxLength=100, nullable=true),
     *             @OA\Property(property="order_note", type="string", description="Ghi chú đơn hàng", example="Giao hàng buổi chiều", maxLength=1000, nullable=true),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="Danh sách sản phẩm mới (thay thế sản phẩm cũ nếu cung cấp)",
     *                 nullable=true,
     *                 @OA\Items(
     *                     required={"quantity", "part_number", "designs"},
     *                     @OA\Property(property="campaign_title", type="string", description="Tiêu đề chiến dịch", example="Winter Campaign", maxLength=255, nullable=true),
     *                     @OA\Property(property="quantity", type="integer", description="Số lượng", example=3, minimum=1),
     *                     @OA\Property(property="part_number", type="string", description="Mã sản phẩm (SKU)", example="SKU456", maxLength=255),
     *                     @OA\Property(
     *                         property="designs",
     *                         type="array",
     *                         description="Danh sách thiết kế",
     *                         minItems=1,
     *                         @OA\Items(
     *                             required={"file_url", "print_space"},
     *                             @OA\Property(property="file_url", type="string", format="uri", description="URL file thiết kế", example="https://domain.com/design2.png"),
     *                             @OA\Property(property="print_space", type="string", description="Vị trí in", example="Back")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="mockups",
     *                         type="array",
     *                         description="Danh sách mockup",
     *                         nullable=true,
     *                         @OA\Items(
     *                             required={"file_url", "print_space"},
     *                             @OA\Property(property="file_url", type="string", format="uri", description="URL file mockup", example="https://domain.com/mockup2.png"),
     *                             @OA\Property(property="print_space", type="string", description="Vị trí in", example="Back")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_number", type="string", example="ORDER123457"),
     *                 @OA\Property(property="status", type="string", example="on hold"),
     *                 @OA\Property(property="store_name", type="string", example="New Store", nullable=true),
     *                 @OA\Property(property="channel", type="string", example="web", nullable=true),
     *                 @OA\Property(property="customer_email", type="string", example="b@gmail.com"),
     *                 @OA\Property(
     *                     property="shipping_address",
     *                     type="object",
     *                     @OA\Property(property="customer_name", type="string", example="Nguyen Van B"),
     *                     @OA\Property(property="company", type="string", example="New Store", nullable=true),
     *                     @OA\Property(property="address_1", type="string", example="456 Đường XYZ"),
     *                     @OA\Property(property="address_2", type="string", example="Tầng 5", nullable=true),
     *                     @OA\Property(property="city", type="string", example="Hồ Chí Minh"),
     *                     @OA\Property(property="county", type="string", example="", nullable=true),
     *                     @OA\Property(property="postcode", type="string", example="700000"),
     *                     @OA\Property(property="country", type="string", example="VN"),
     *                     @OA\Property(property="phone", type="string", example="0987654321", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="part_number", type="string", example="SKU456"),
     *                         @OA\Property(property="title", type="string", example="Winter Campaign"),
     *                         @OA\Property(property="quantity", type="integer", example=3),
     *                         @OA\Property(property="print_price", type="string", example="15.00"),
     *                         @OA\Property(property="total_price", type="string", example="45.00"),
     *                         @OA\Property(
     *                             property="designs",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="file_url", type="string", example="https://domain.com/design2.png"),
     *                                 @OA\Property(property="print_space", type="string", example="Back")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="mockups",
     *                             type="array",
     *                             nullable=true,
     *                             @OA\Items(
     *                                 @OA\Property(property="file_url", type="string", example="https://domain.com/mockup2.png"),
     *                                 @OA\Property(property="print_space", type="string", example="Back")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="label_url", type="string", example="http://example.com/label.pdf", nullable=true),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-11T11:41:00+07:00"),
     *                 @OA\Property(property="old_total_price", type="string", example="20.00"),
     *                 @OA\Property(property="new_total_price", type="string", example="45.00"),
     *                 @OA\Property(property="price_difference", type="string", example="25.00"),
     *                 @OA\Property(
     *                     property="payment_transaction",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="integer", example=3),
     *                     @OA\Property(property="type", type="string", example="deduct"),
     *                     @OA\Property(property="amount", type="string", example="25.00"),
     *                     @OA\Property(property="status", type="string", example="approved"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-11T11:41:00+07:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi dữ liệu đầu vào, số dư ví không đủ, hoặc mã đơn hàng trùng lặp",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 nullable=true,
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string", example="The order number has already been taken")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Lỗi xác thực token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid API token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đơn hàng",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the order"),
     *             @OA\Property(property="error", type="string", example="Database connection failed", nullable=true)
     *         )
     *     )
     * )
     */
    public function updateOrder(Request $request, $orderId)
    {
        try {
            // 1. Xác thực API token từ header Authorization
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token is required'
                ], 401);
            }

            $user = User::where('api_token', $token)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API token'
                ], 401);
            }

            // 2. Tìm đơn hàng hiện tại
            $existingOrder = ExcelOrder::with(['items.designs', 'items.mockups'])
                ->where('id', $orderId)
                ->where('created_by', $user->id)
                ->first();

            if (!$existingOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // 3. Kiểm tra trạng thái đơn hàng
            if (!in_array($existingOrder->status, ['on hold', 'pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update order in current status: ' . $existingOrder->status,
                    'current_status' => $existingOrder->status
                ], 400);
            }

            // 4. Validate request data (tất cả nullable cho update)
            $validated = $request->validate([
                'order_number' => 'nullable|string|max:255',
                'store_name' => 'nullable|string|max:255',
                'channel' => 'nullable|string|max:255',
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'address_2' => 'nullable|string|max:500',
                'house_number' => 'nullable|string|max:50',
                'mailbox_number' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postcode' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:2',
                'shipping_method' => 'nullable|string|max:100',
                'order_note' => 'nullable|string|max:1000',
                'products' => 'nullable|array',
                'products.*.campaign_title' => 'nullable|string|max:255',
                'products.*.quantity' => 'required_with:products|integer|min:1',
                'products.*.part_number' => ['required_with:products', 'string', 'max:255', new ValidPartNumber],
                'products.*.designs' => 'required_with:products|array|min:1',
                'products.*.designs.*.file_url' => 'required_with:products.*.designs|url',
                'products.*.designs.*.print_space' => ['required_with:products.*.designs', 'string', new ValidPrintSpace],
                'products.*.mockups' => 'nullable|array',
                'products.*.mockups.*.file_url' => 'required_with:products.*.mockups|url',
                'products.*.mockups.*.print_space' => ['required_with:products.*.mockups', 'string', new ValidPrintSpace],
            ]);

            DB::beginTransaction();

            // 5. Tính toán giá cũ của đơn hàng hiện tại
            $oldTotalAmount = $existingOrder->items->sum(function ($item) {
                return $item->print_price * $item->quantity;
            });

            // 6. Nếu có cập nhật products, tính toán giá mới
            $newTotalAmount = $oldTotalAmount;
            $orderTotalPrices = [];
            $itemPrices = [];
            $itemPriceBreakdowns = [];

            if (isset($validated['products']) && !empty($validated['products'])) {
                // Chuẩn bị danh sách tất cả items mới với thông tin cần thiết
                $allItems = [];
                $shippingMethod = $validated['shipping_method'] ?? $existingOrder->shipping_method;

                foreach ($validated['products'] as $productIndex => $product) {
                    $variant = ProductVariant::where('sku', $product['part_number'])
                        ->orWhere('twofifteen_sku', $product['part_number'])
                        ->orWhere('flashship_sku', $product['part_number'])
                        ->first();

                    if ($variant) {
                        $allItems[] = [
                            'variant' => $variant,
                            'quantity' => $product['quantity'],
                            'product' => $product,
                            'original_index' => $productIndex,
                            'first_item_price' => $variant->getFirstItemPrice($shippingMethod),
                            'part_number' => $product['part_number']
                        ];
                    }
                }

                // Tính tổng số tiền mới
                $newTotalAmount = 0;

                // Logic tính giá giống như createOrder
                // 1. Tìm item có giá cao nhất trong toàn bộ đơn hàng
                $highestPriceItem = null;
                $highestPrice = 0;
                foreach ($allItems as $item) {
                    if ($item['first_item_price'] > $highestPrice) {
                        $highestPrice = $item['first_item_price'];
                        $highestPriceItem = $item;
                    }
                }

                // 2. Gom nhóm theo part_number
                $productsByPartNumber = [];
                foreach ($allItems as $item) {
                    $partNumber = $item['part_number'];
                    if (!isset($productsByPartNumber[$partNumber])) {
                        $productsByPartNumber[$partNumber] = [];
                    }
                    $productsByPartNumber[$partNumber][] = $item;
                }

                // 3. Xử lý tính giá cho từng item
                $firstItemProcessed = false;

                foreach ($productsByPartNumber as $partNumber => $items) {
                    foreach ($items as $index => $item) {
                        $variant = $item['variant'];
                        $quantity = $item['quantity'];
                        $originalIndex = $item['original_index'];

                        $isFirstItem = (!$firstItemProcessed && $highestPriceItem &&
                            $highestPriceItem['original_index'] === $originalIndex);

                        if ($isFirstItem) {
                            $firstItemProcessed = true;
                        }

                        $itemTotal = 0;
                        $averagePrice = 0;
                        $priceBreakdown = [];

                        if ($isFirstItem && $quantity > 1) {
                            $priceInfo1 = $variant->getOrderPriceInfo($shippingMethod, 1);
                            $priceInfo2 = $variant->getOrderPriceInfo($shippingMethod, 2);

                            if ($priceInfo1['shipping_price_found'] && $priceInfo2['shipping_price_found']) {
                                $firstPrice = round($priceInfo1['print_price'], 2);
                                $secondPrice = round($priceInfo2['print_price'], 2);
                                $itemTotal = $firstPrice + ($secondPrice * ($quantity - 1));
                                $itemTotal = round($itemTotal, 2);
                                $averagePrice = round($itemTotal / $quantity, 2);

                                $priceBreakdown = [
                                    'first_item_price' => $firstPrice,
                                    'additional_item_price' => $secondPrice,
                                    'quantity' => $quantity,
                                    'breakdown' => "1x{$firstPrice} + " . ($quantity - 1) . "x{$secondPrice}"
                                ];
                            }
                        } else {
                            $position = $isFirstItem ? 1 : 2;
                            $priceInfo = $variant->getOrderPriceInfo($shippingMethod, $position);

                            if ($priceInfo['shipping_price_found']) {
                                $unitPrice = round($priceInfo['print_price'], 2);
                                $itemTotal = $unitPrice * $quantity;
                                $itemTotal = round($itemTotal, 2);
                                $averagePrice = $unitPrice;

                                $priceBreakdown = [
                                    'unit_price' => $unitPrice,
                                    'quantity' => $quantity,
                                    'is_first_item' => $isFirstItem,
                                    'breakdown' => $quantity . "x" . $unitPrice
                                ];
                            }
                        }

                        if ($itemTotal > 0) {
                            $newTotalAmount += $itemTotal;
                            $orderTotalPrices[$originalIndex] = $itemTotal;
                            $itemPrices[$originalIndex] = $averagePrice;
                            $itemPriceBreakdowns[$originalIndex] = $priceBreakdown;
                        }
                    }
                }

                $newTotalAmount = round($newTotalAmount, 2);

                // Log pricing logic cho update
                $this->logPricingLogic($validated, $allItems, $itemPriceBreakdowns, $newTotalAmount, $existingOrder->external_id);
            }

            // 7. Tính toán sự khác biệt về giá
            $priceDifference = $newTotalAmount - $oldTotalAmount;
            $wallet = Wallet::where('user_id', $user->id)->first();

            // 8. Xử lý payment difference
            $paymentTransaction = null;
            if ($priceDifference > 0) {
                // Cần trừ thêm tiền
                if (!$wallet || !$wallet->hasEnoughBalance($priceDifference)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient balance for additional charges',
                        'additional_amount' => $priceDifference,
                        'current_balance' => $wallet ? $wallet->balance : 0
                    ], 400);
                }

                // Tạo giao dịch trừ thêm tiền
                $paymentTransaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_code' => 'UPDATE-' . time(),
                    'type' => Transaction::TYPE_DEDUCT,
                    'method' => Transaction::METHOD_VND,
                    'amount' => $priceDifference,
                    'status' => Transaction::STATUS_APPROVED,
                    'note' => 'Additional charge for order update: ' . $existingOrder->external_id,
                    'approved_at' => now(),
                ]);

                if (!$wallet->withdraw($priceDifference)) {
                    $paymentTransaction->reject('Failed to withdraw additional amount from wallet');
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to process additional payment'
                    ], 500);
                }
            } elseif ($priceDifference < 0) {
                // Cần hoàn tiền
                $refundAmount = abs($priceDifference);

                $paymentTransaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_code' => 'REFUND-UPDATE-' . time(),
                    'type' => Transaction::TYPE_REFUND,
                    'method' => Transaction::METHOD_VND,
                    'amount' => $refundAmount,
                    'status' => Transaction::STATUS_APPROVED,
                    'note' => 'Refund for order update: ' . $existingOrder->external_id,
                    'approved_at' => now(),
                ]);

                if (!$wallet->deposit($refundAmount)) {
                    $paymentTransaction->reject('Failed to deposit refund to wallet');
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to process refund'
                    ], 500);
                }
            }
            // Nếu priceDifference == 0 thì không tạo transaction nào

            // 9. Cập nhật thông tin đơn hàng
            $updateData = [];
            if (isset($validated['order_number'])) {
                // Kiểm tra trùng lặp order number
                $duplicateCheck = ExcelOrder::where('external_id', $validated['order_number'])
                    ->where('id', '!=', $existingOrder->id)
                    ->first();
                if ($duplicateCheck) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order number already exists'
                    ], 400);
                }
                $updateData['external_id'] = $validated['order_number'];
            }

            if (isset($validated['store_name'])) $updateData['brand'] = $validated['store_name'];
            if (isset($validated['channel'])) $updateData['channel'] = $validated['channel'];
            if (isset($validated['customer_email'])) $updateData['buyer_email'] = $validated['customer_email'];
            if (isset($validated['customer_name'])) {
                $updateData['first_name'] = explode(' ', $validated['customer_name'])[0];
                $updateData['last_name'] = substr($validated['customer_name'], strpos($validated['customer_name'], ' ') + 1) ?: '';
            }
            if (isset($validated['store_name'])) $updateData['company'] = $validated['store_name'];
            if (isset($validated['address'])) $updateData['address1'] = $validated['address'];
            if (isset($validated['address_2'])) $updateData['address2'] = $validated['address_2'];
            if (isset($validated['city'])) $updateData['city'] = $validated['city'];
            if (isset($validated['state'])) $updateData['county'] = $validated['state'];
            if (isset($validated['postcode'])) $updateData['post_code'] = $validated['postcode'];
            if (isset($validated['country'])) $updateData['country'] = $validated['country'];
            if (isset($validated['customer_phone'])) $updateData['phone1'] = $validated['customer_phone'];
            if (isset($validated['shipping_method'])) $updateData['shipping_method'] = $validated['shipping_method'];

            if (isset($validated['order_note'])) {
                $comment = $validated['order_note'];
                if (($validated['shipping_method'] ?? $existingOrder->shipping_method) === 'tiktok_label') {
                    $comment .= ' Shipping label: http://example.com/label.pdf';
                }
                $updateData['comment'] = $comment;
            }

            if (!empty($updateData)) {
                $existingOrder->update($updateData);
            }

            // 10. Cập nhật products nếu có
            if (isset($validated['products']) && !empty($validated['products'])) {
                // Xóa các items cũ
                ExcelOrderDesign::whereIn('excel_order_item_id', $existingOrder->items->pluck('id'))->delete();
                ExcelOrderMockup::whereIn('excel_order_item_id', $existingOrder->items->pluck('id'))->delete();
                $existingOrder->items()->delete();

                // Tạo items mới
                foreach ($validated['products'] as $index => $product) {
                    $variant = ProductVariant::where('sku', $product['part_number'])
                        ->orWhere('twofifteen_sku', $product['part_number'])
                        ->orWhere('flashship_sku', $product['part_number'])
                        ->first();

                    $productId = $variant ? $variant->product_id : null;

                    $orderItem = ExcelOrderItem::create([
                        'excel_order_id' => $existingOrder->id,
                        'part_number' => $product['part_number'],
                        'title' => $product['campaign_title'] ?? 'No title',
                        'quantity' => $product['quantity'],
                        'print_price' => $itemPrices[$index] ?? 0,
                        'product_id' => $productId,
                        'description' => json_encode($product['options'] ?? []),
                    ]);

                    // Tạo designs cho item này
                    foreach ($product['designs'] as $design) {
                        ExcelOrderDesign::create([
                            'excel_order_item_id' => $orderItem->id,
                            'title' => $this->getPositionTitle($design['print_space']),
                            'url' => $design['file_url']
                        ]);
                    }

                    // Tạo mockups cho item này (nếu có)
                    if (!empty($product['mockups'])) {
                        foreach ($product['mockups'] as $mockup) {
                            ExcelOrderMockup::create([
                                'excel_order_item_id' => $orderItem->id,
                                'title' => $this->getPositionTitle($mockup['print_space']),
                                'url' => $mockup['file_url']
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            // 11. Load order với tất cả relationships để trả về
            $existingOrder->refresh();
            $existingOrder->load(['items.designs', 'items.mockups', 'creator']);

            Log::info('Order updated successfully via API:', [
                'order_id' => $existingOrder->id,
                'external_id' => $existingOrder->external_id,
                'user_id' => $user->id,
                'old_total' => $oldTotalAmount,
                'new_total' => $newTotalAmount,
                'price_difference' => $priceDifference
            ]);

            $responseData = [
                'id' => $existingOrder->id,
                'order_number' => $existingOrder->external_id,
                'status' => $existingOrder->status,
                'store_name' => $existingOrder->brand,
                'channel' => $existingOrder->channel,
                'customer_email' => $existingOrder->buyer_email,
                'shipping_address' => [
                    'customer_name' => $existingOrder->first_name . ' ' . $existingOrder->last_name,
                    'company' => $existingOrder->company,
                    'address_1' => $existingOrder->address1,
                    'address_2' => $existingOrder->address2,
                    'city' => $existingOrder->city,
                    'county' => $existingOrder->county,
                    'postcode' => $existingOrder->post_code,
                    'country' => $existingOrder->country,
                    'phone' => $existingOrder->phone1,
                ],
                'products' => $existingOrder->items->map(function ($item, $index) use ($orderTotalPrices) {
                    return [
                        'part_number' => $item->part_number,
                        'title' => $item->title,
                        'quantity' => $item->quantity,
                        'print_price' => number_format($item->print_price, 2, '.', ''),
                        'total_price' => number_format($orderTotalPrices[$index] ?? ($item->print_price * $item->quantity), 2, '.', ''),
                        'designs' => $item->designs->map(function ($design) {
                            return [
                                'file_url' => $design->url,
                                'print_space' => $design->title,
                            ];
                        }),
                        'mockups' => $item->mockups->map(function ($mockup) {
                            return [
                                'file_url' => $mockup->url,
                                'print_space' => $mockup->title,
                            ];
                        }),
                    ];
                }),
                'label_url' => $existingOrder->comment,
                'updated_at' => $existingOrder->updated_at->toISOString(),
                'old_total_price' => number_format($oldTotalAmount, 2, '.', ''),
                'new_total_price' => number_format($newTotalAmount, 2, '.', ''),
                'price_difference' => number_format($priceDifference, 2, '.', ''),
            ];

            if ($paymentTransaction) {
                $responseData['payment_transaction'] = [
                    'id' => $paymentTransaction->id,
                    'type' => $paymentTransaction->type,
                    'amount' => number_format($paymentTransaction->amount, 2, '.', ''),
                    'status' => $paymentTransaction->status,
                    'created_at' => $paymentTransaction->created_at->toISOString()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $responseData
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order via API:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId,
                'user_token' => $request->bearerToken()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Lấy danh sách sản phẩm UK",
     *     description="API để lấy danh sách sản phẩm UK, bao gồm thông tin variant và thuộc tính. Hỗ trợ phân trang và tìm kiếm theo tên hoặc mô tả sản phẩm. Yêu cầu token xác thực Bearer.",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng sản phẩm mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", example=10, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang hiện tại",
     *         required=false,
     *         @OA\Schema(type="integer", example=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Tìm kiếm sản phẩm theo tên hoặc mô tả",
     *         required=false,
     *         @OA\Schema(type="string", example="T-Shirt")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy danh sách sản phẩm thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="product_id", type="integer", example=1),
     *                         @OA\Property(property="product_name", type="string", example="Premium T-Shirt"),
     *                         @OA\Property(property="product_description", type="string", example="High-quality cotton t-shirt", nullable=true),
     *                         @OA\Property(property="template_link", type="string", example="https://domain.com/template/tshirt.psd", nullable=true),
     *                         @OA\Property(property="currency", type="string", example="GBP"),
     *                         @OA\Property(
     *                             property="variants",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="sku", type="string", example="TSHIRT-GBP-001"),
     *                                 @OA\Property(
     *                                     property="attributes",
     *                                     type="array",
     *                                     @OA\Items(
     *                                         @OA\Property(property="option", type="string", example="Color"),
     *                                         @OA\Property(property="option_value", type="string", example="Blue")
     *                                     )
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="total", type="integer", example=50),
     *                     @OA\Property(property="last_page", type="integer", example=5)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Lỗi xác thực token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid API token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while retrieving products"),
     *             @OA\Property(property="error", type="string", example="Database connection failed", nullable=true)
     *         )
     *     )
     * )
     */
    public function getProductsWithGBP(Request $request)
    {
        try {
            // 1. Xác thực API token
            $user = $this->authenticateUser($request);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API token'
                ], 401);
            }

            // 2. Lấy tham số query
            $perPage = $request->query('per_page', 10);
            $search = $request->query('search');

            // 3. Xây dựng truy vấn
            $query = Product::select(['id', 'name', 'description', 'template_link', 'currency'])
                ->with(['variants:id,product_id,sku', 'variants.attributes:name,value'])
                ->where('currency', Product::CURRENCY_GBP);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // 4. Phân trang
            $products = $query->paginate($perPage);

            // 5. Format dữ liệu
            $result = $products->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_description' => $product->description,
                    'template_link' => $product->template_link,
                    'currency' => $product->currency,
                    'variants' => $product->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'sku' => $variant->sku,
                            'attributes' => $variant->attributes->map(function ($attr) {
                                return [
                                    'option' => $attr->name,
                                    'option_value' => $attr->value
                                ];
                            })->all()
                        ];
                    })->all()
                ];
            })->all();

            // 6. Ghi log thành công
            Log::info('Products with GBP retrieved successfully via API:', [
                'user_id' => $user->id,
                'product_count' => $products->total(),
                'search' => $search,
                'per_page' => $perPage,
                'page' => $products->currentPage()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $result,
                    'pagination' => [
                        'current_page' => $products->currentPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                        'last_page' => $products->lastPage()
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving products with GBP via API:', [
                'user_token' => $request->bearerToken(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xác thực người dùng dựa trên Bearer token.
     *
     * @param Request $request
     * @return User|null
     */
    private function authenticateUser(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }
        return User::where('api_token', $token)->first();
    }
}
