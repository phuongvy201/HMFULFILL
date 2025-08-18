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
use App\Models\VariantAttribute;
use App\Rules\ValidPartNumber;
use App\Rules\ValidPrintSpace;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\UserTier;
use App\Services\OrderRowValidator;
use App\Services\OrderValidationService;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\GoogleDriveHelper;

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
    private OrderRowValidator $validator;
    private ExcelOrderImportService $excelOrderImportService;
    private OrderValidationService $orderValidationService;

    // Thêm properties cho API services
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

    public function __construct(OrderRowValidator $validator, ExcelOrderImportService $excelOrderImportService, OrderValidationService $orderValidationService)
    {
        $this->appId = config('services.twofifteen.app_id');
        $this->secretKey = config('services.twofifteen.secret_key');
        $this->validator = $validator;
        $this->excelOrderImportService = $excelOrderImportService;
        $this->orderValidationService = $orderValidationService;

        // Khởi tạo API services
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



            // Trả về view với biến $files
            return view('admin.orders.order-fulfillment-list', compact('files'));
        } catch (\Exception $e) {


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

            // Upload file lên AWS S3
            $filePath = $file->storeAs('fulfillment', $fileName, 's3');

            // Lấy URL public từ AWS S3
            $fileUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $filePath;

            // Tạo file tạm để đọc Excel (vì IOFactory cần file local)
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $file->move(dirname($tempFile), basename($tempFile));

            // Đọc file Excel từ file tạm
            $spreadsheet = IOFactory::load($tempFile);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            if ($highestRow <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'File không có dữ liệu'
                ], 422);
            }

            // Lấy user_id từ người dùng đang đăng nhập
            $userId = Auth::id();

            // Lưu thông tin file vào database, bao gồm user_id
            $importedFile = ImportFile::create([
                'file_name' => $fileName,
                'file_path' => $fileUrl, // URL từ AWS S3
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
            $this->excelOrderImportService->process($importedFile, $rows);

            // Xóa file tạm
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

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
            // Xóa file tạm nếu có lỗi
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

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



            // Lấy thông tin các file trước khi xóa
            $files = ImportFile::whereIn('id', $ids)->get();

            foreach ($files as $file) {
                // Lấy đường dẫn vật lý của file
                $filePath = public_path('uploads/fulfillment/' . $file->file_name);

                // Kiểm tra và xóa file vật lý nếu tồn tại
                if (file_exists($filePath)) {
                    unlink($filePath);
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

            // Upload file lên AWS S3
            $filePath = $file->storeAs('customer_fulfillment', $fileName, 's3');

            // Lấy URL public từ AWS S3
            $fileUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $filePath;

            // Tạo file tạm để đọc Excel (vì IOFactory cần file local)
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $file->move(dirname($tempFile), basename($tempFile));

            // Đọc file Excel từ file tạm
            $spreadsheet = IOFactory::load($tempFile);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            if ($highestRow <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'File has no data'
                ], 422);
            }

            // Lấy user_id từ người dùng đang đăng nhập
            $userId = Auth::id();

            // Lưu thông tin file vào database với status pending
            $importedFile = ImportFile::create([
                'file_name' => $fileName,
                'file_path' => $fileUrl, // URL từ AWS S3
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
            $result = $this->excelOrderImportService->processCustomer($importedFile, $rows, $request->input('warehouse'));

            // Nếu result là false, nghĩa là đã có lỗi
            if ($result === false) {
                return back()->with('error', 'Have error in file. Click to see detail.');
            }

            // Cập nhật status thành "on hold" sau khi xử lý thành công (đã trừ tiền)
            $importedFile->update([
                'status' => 'on hold', // Status "on hold" vì đã trừ tiền và tạo orders
                'total_rows' => $highestRow - 1,
                'processed_rows' => $highestRow - 1
            ]);

            // Xóa file tạm
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            return back()->with('success', 'File uploaded successfully. Status is on hold and orders are ready for processing.');
        } catch (\Exception $e) {
            Log::error('Error processing customer file: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Xóa file tạm nếu có lỗi
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

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

            // Lấy thông tin các file trước khi xóa với relationships
            $files = ImportFile::with(['excelOrders.items'])
                ->whereIn('id', $ids)
                ->get();

            // Kiểm tra xem tất cả file có trạng thái "failed" hoặc "on hold" không
            $nonDeletableFiles = $files->whereNotIn('status', ['failed', 'on hold']);
            if ($nonDeletableFiles->count() > 0) {
                $nonDeletableFileIds = $nonDeletableFiles->pluck('id')->toArray();
                return response()->json([
                    'success' => false,
                    'message' => 'Only files with "failed" or "on hold" status can be deleted. Files with IDs: ' . implode(', ', $nonDeletableFileIds) . ' cannot be deleted.'
                ], 400);
            }

            DB::beginTransaction();

            try {
                foreach ($files as $file) {
                    // Nếu file có status "on hold", thực hiện refund
                    if ($file->status === 'on hold') {
                        // Lấy danh sách orders để kiểm tra
                        $allOrders = $file->excelOrders;
                        $cancelledOrders = $allOrders->whereIn('status', ['cancelled', 'refunded']);
                        $refundableOrders = $allOrders->whereNotIn('status', ['cancelled', 'refunded']);

                        // Tính tổng tiền của file từ các orders CHƯA BỊ CANCEL
                        $totalAmount = $refundableOrders->sum(function ($order) {
                            return $order->items->sum(function ($item) {
                                return $item->print_price * $item->quantity;
                            });
                        });

                        // Log chi tiết đã xóa để tối ưu hóa

                        if ($totalAmount > 0) {
                            // Tạo transaction refund
                            $refundTransaction = Transaction::create([
                                'user_id' => $file->user_id,
                                'type' => Transaction::TYPE_REFUND,
                                'method' => Transaction::METHOD_VND,
                                'amount' => $totalAmount,
                                'status' => Transaction::STATUS_APPROVED,
                                'transaction_code' => 'REFUND_FILE_' . $file->id . '_' . time(),
                                'note' => "Hoàn tiền do xóa file on hold: {$file->file_name} (chỉ các đơn chưa cancel)",
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);

                            // Cập nhật balance của user qua Wallet
                            $user = User::find($file->user_id);
                            $wallet = $user->wallet;

                            if (!$wallet) {
                                throw new \Exception("Không tìm thấy ví của người dùng cho file {$file->id}");
                            }

                            if (!$wallet->deposit($totalAmount)) {
                                throw new \Exception("Không thể thêm tiền vào ví cho file {$file->id}");
                            }
                        } else {
                        }
                    }

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

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return response()->json([
                'success' => true,
                'message' => 'Files deleted successfully'
            ]);
        } catch (\Exception $e) {


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

            if ($request->filled('status')) {
                $query->where('status', $request->status);
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
            $userId = Auth::id();

            // Decode external_id từ URL
            $externalId = urldecode($externalId);

            // Lấy chi tiết đơn hàng cụ thể với các relationships cần thiết
            // Thêm điều kiện kiểm tra user_id để đảm bảo bảo mật
            $order = ExcelOrder::with([
                'items.mockups',
                'items.designs',
                'items.product',
                'importFile'
            ])->where('external_id', $externalId)
                ->where('created_by', $userId)
                ->firstOrFail();

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


            return view('customer.orders.order-uploaded-detail', compact('order', 'orderStatistics'));
        } catch (\Exception $e) {
            Log::error('Error in getCustomerOrderDetail:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'external_id' => $externalId,
                'user_id' => Auth::id()
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
                            ->orWhereRaw("users.first_name || ' ' || users.last_name like ?", ['%' . $customerSearch . '%']);
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
        $categories = \App\Models\Category::all();
        $products = Product::with(['variants.attributes'])->get();
        $result = [];
        foreach ($products as $product) {
            $variants = [];
            foreach ($product->variants as $variant) {
                $attributes = $variant->attributes->pluck('value', 'name')->toArray();
                $variants[] = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'twofifteen_sku' => $variant->twofifteen_sku,
                    'flashship_sku' => $variant->flashship_sku,
                    'attributes' => $attributes,
                    'attribute_text' => implode(', ', array_map(function ($name, $value) {
                        return "$name: $value";
                    }, array_keys($attributes), array_values($attributes)))
                ];
            }

            $result[] = [
                'id' => $product->id,

                'name' => $product->name,
                'category' => $product->category->name ?? 'Uncategorized',
                'variants' => $variants
            ];
        }

        return view('customer.orders.order-create', compact('categories'));
    }

    /**
     * Lấy danh sách products với variants cho customer
     */
    public function getCustomerProductsWithVariants()
    {
        $products = Product::with([
            'variants.attributes',
            'images' => function ($query) {
                // Lấy ảnh được tạo sớm nhất (oldest first)
                $query->orderBy('created_at', 'asc');
            },
            'category'
        ])->get();

        $result = [];
        foreach ($products as $product) {
            $variants = [];
            foreach ($product->variants as $variant) {
                $attributes = $variant->attributes->pluck('value', 'name')->toArray();
                $variants[] = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'twofifteen_sku' => $variant->twofifteen_sku,
                    'flashship_sku' => $variant->flashship_sku,
                    'attributes' => $attributes,
                    'attribute_text' => implode(', ', array_map(function ($name, $value) {
                        return "$name: $value";
                    }, array_keys($attributes), array_values($attributes)))
                ];
            }

            // Lấy hình ảnh từ bảng ProductImage - ảnh chính là ảnh được tạo sớm nhất
            $mainImageUrl = $product->getMainImageUrl();

            // Lấy tất cả ảnh của sản phẩm với thông tin chi tiết
            $images = $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => str_starts_with($image->image_url, 'http')
                        ? $image->image_url
                        : asset($image->image_url),
                    'created_at' => $image->created_at ? $image->created_at->toISOString() : null
                ];
            })->toArray();

            Log::info("Product ID {$product->id}: Found " . $product->images->count() . " images, Main image URL: " . ($mainImageUrl ?? 'null'));

            $result[] = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'base_price' => $product->base_price,
                'currency' => $product->currency,
                'category' => $product->category->name ?? 'Uncategorized',
                'image_url' => $mainImageUrl, // Ảnh chính (ảnh được tạo sớm nhất)
                'images' => $images, // Tất cả ảnh của sản phẩm
                'image_count' => $product->images->count(), // Số lượng ảnh
                'variants' => $variants
            ];
        }

        return response()->json($result);
    }

    /**
     * Xử lý tạo đơn hàng thủ công của customer
     */
    public function storeCustomerManualOrder(Request $request)
    {
        try {
            // Validate dữ liệu cơ bản
            $validated = $request->validate([
                'order_number' => 'required|string|max:255|unique:excel_orders,external_id',
                'store_name' => 'nullable|string|max:255',
                'channel' => 'nullable|string|max:255',
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'address_2' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postcode' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:2',
                'shipping_method' => 'nullable|string|max:100',
                'order_note' => 'nullable|string|max:1000',
                'warehouse' => 'required|in:US,UK',
                'products' => 'required|array|min:1',
                'products.*.variant_id' => 'required|exists:product_variants,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.title' => 'nullable|string|max:255',
                'products.*.designs' => 'required|array|min:1',
                'products.*.designs.*.file_url' => 'required|url',
                'products.*.designs.*.print_space' => 'required|string',
                'products.*.mockups' => 'nullable|array',
                'products.*.mockups.*.file_url' => 'required_with:products.*.mockups|url',
                'products.*.mockups.*.print_space' => 'required_with:products.*.mockups|string',
            ]);

            // Custom validation logic using OrderValidationService
            $validationErrors = $this->orderValidationService->validateCustomerManualOrder($validated);
            if (!empty($validationErrors)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors($validationErrors);
            }

            // No formatting needed - positions are entered manually and validated
            $formattedData = $validated;

            DB::beginTransaction();

            // Tính toán giá
            $user = Auth::user();
            $wallet = $user->wallet;

            $totalAmount = 0;
            $itemPrices = [];

            try {
                // Lấy tier hiện tại của user
                $userTier = \App\Models\UserTier::getCurrentTier($user->id);
                $currentTier = $userTier ? $userTier->tier : 'Wood';
                Log::info('[CUSTOMER-MANUAL] Tier hiện tại của user', [
                    'user_id' => $user->id,
                    'tier' => $currentTier,
                    'tier_data' => $userTier
                ]);

                // Logic tính giá sẽ áp dụng thứ tự ưu tiên:
                // 1. Giá theo tier của user (tier_name = $currentTier)
                // 2. Giá mặc định (tier_name = null)
                // 3. Giá Wood tier (tier_name = 'Wood') làm fallback cuối cùng

                // Chuẩn bị danh sách tất cả items với thông tin cần thiết
                $allItems = [];
                foreach ($validated['products'] as $productIndex => $product) {
                    $variant = ProductVariant::findOrFail($product['variant_id']);
                    $allItems[] = [
                        'variant' => $variant,
                        'quantity' => $product['quantity'],
                        'product' => $product,
                        'original_index' => $productIndex,
                        'first_item_price' => $variant->getFirstItemPrice($validated['shipping_method'] ?? null, $user->id),
                        'part_number' => $variant->twofifteen_sku ?? $variant->sku
                    ];
                }

                // Tìm item có giá cao nhất trong toàn bộ đơn hàng
                $highestPriceItem = null;
                $highestPrice = 0;
                foreach ($allItems as $item) {
                    if ($item['first_item_price'] > $highestPrice) {
                        $highestPrice = $item['first_item_price'];
                        $highestPriceItem = $item;
                    }
                }

                // Gom nhóm theo part_number để xử lý logic đặc biệt cho items giống nhau
                $productsByPartNumber = [];
                foreach ($allItems as $item) {
                    $partNumber = $item['part_number'];
                    if (!isset($productsByPartNumber[$partNumber])) {
                        $productsByPartNumber[$partNumber] = [];
                    }
                    $productsByPartNumber[$partNumber][] = $item;
                }

                // Xử lý tính giá cho từng item (chỉ có 1 item duy nhất tính giá "1st item")
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
                            $priceInfo1 = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, 1, $user->id);
                            $priceInfo2 = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, 2, $user->id);

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
                                    'tier_price' => $priceInfo1['tier_price'] ?? false,
                                    'tier' => $priceInfo1['tier'] ?? null,
                                    'breakdown' => "1x{$firstPrice} + " . ($quantity - 1) . "x{$secondPrice}"
                                ];

                                Log::info('[CUSTOMER-MANUAL] Tính giá (first_item_mix)', [
                                    'external_id' => $validated['order_number'],
                                    'part_number' => $partNumber,
                                    'quantity' => $quantity,
                                    'first_item_price' => $firstPrice,
                                    'second_item_price' => $secondPrice,
                                    'item_total' => $itemTotal,
                                    'average_price' => $averagePrice,
                                    'tier_price' => $priceInfo1['tier_price'] ?? false,
                                    'tier' => $priceInfo1['tier'] ?? null,
                                    'price_source' => $priceInfo1['tier_price'] ? 'tier_specific' : 'default_or_fallback',
                                    'breakdown' => $priceBreakdown['breakdown']
                                ]);
                            }
                        } else {
                            // Trường hợp thông thường: tất cả items tính cùng 1 giá
                            $position = $isFirstItem ? 1 : 2;
                            $priceInfo = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, $position, $user->id);

                            if ($priceInfo['shipping_price_found']) {
                                $unitPrice = round($priceInfo['print_price'], 2);
                                $itemTotal = $unitPrice * $quantity;
                                $itemTotal = round($itemTotal, 2);
                                $averagePrice = $unitPrice;

                                $priceBreakdown = [
                                    'unit_price' => $unitPrice,
                                    'quantity' => $quantity,
                                    'is_first_item' => $isFirstItem,
                                    'tier_price' => $priceInfo['tier_price'] ?? false,
                                    'tier' => $priceInfo['tier'] ?? null,
                                    'breakdown' => $quantity . "x" . $unitPrice
                                ];

                                Log::info('[CUSTOMER-MANUAL] Tính giá (' . ($isFirstItem ? 'first_item' : 'second_item') . ')', [
                                    'external_id' => $validated['order_number'],
                                    'part_number' => $partNumber,
                                    'quantity' => $quantity,
                                    'unit_price' => $unitPrice,
                                    'item_total' => $itemTotal,
                                    'average_price' => $averagePrice,
                                    'tier_price' => $priceInfo['tier_price'] ?? false,
                                    'tier' => $priceInfo['tier'] ?? null,
                                    'price_source' => $priceInfo['tier_price'] ? 'tier_specific' : 'default_or_fallback',
                                    'breakdown' => $priceBreakdown['breakdown']
                                ]);
                            }
                        }

                        if ($itemTotal > 0) {
                            $totalAmount += $itemTotal;
                            $itemPrices[$originalIndex] = $averagePrice;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error calculating prices: ' . $e->getMessage(), [
                    'validated' => $validated,
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error calculating prices: ' . $e->getMessage());
            }

            // Kiểm tra số dư ví
            if (!$wallet || !$wallet->hasEnoughBalance($totalAmount)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient wallet balance. Required: ' . number_format($totalAmount, 2) . ' USD. Current: ' . number_format($wallet ? $wallet->balance : 0, 2) . ' USD');
            }

            // Tạo giao dịch trừ tiền
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_code' => 'CUSTOMER-MANUAL-' . time(),
                'type' => Transaction::TYPE_DEDUCT,
                'method' => Transaction::METHOD_VND,
                'amount' => $totalAmount,
                'status' => Transaction::STATUS_APPROVED,
                'note' => 'Trừ tiền cho đơn hàng thủ công: ' . $validated['order_number'],
                'approved_at' => now(),
            ]);

            // Trừ tiền từ ví
            if (!$wallet->withdraw($totalAmount)) {
                $transaction->reject('Unable to deduct from wallet');
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Unable to process payment');
            }

            // Tạo đơn hàng
            $customerNameParts = explode(' ', trim($formattedData['customer_name']), 2);
            $firstName = $customerNameParts[0] ?? '';
            $lastName = $customerNameParts[1] ?? '';

            $order = ExcelOrder::create([
                'external_id' => $validated['order_number'],
                'brand' => $validated['store_name'] ?? '',
                'channel' => $validated['channel'] ?? 'customer-manual',
                'buyer_email' => $validated['customer_email'] ?? '1',
                'first_name' => $firstName ?? '1',
                'last_name' => $lastName ?? '1',
                'company' => $validated['store_name'] ?? '',
                'address1' => $validated['address'] ?? '1',
                'address2' => $validated['address_2'] ?? '',
                'city' => $validated['city'] ?? '1',
                'county' => $validated['state'] ?? '1',
                'post_code' => $validated['postcode'] ?? '1',
                'country' => $validated['country'] ?? '1',
                'phone1' => $validated['customer_phone'] ?? '1',
                'phone2' => null,
                'comment' => $validated['order_note'] ?? '',
                'shipping_method' => $validated['shipping_method'] ?? null,
                'status' => 'on hold',
                'warehouse' => $validated['warehouse'],
                'created_by' => $user->id,
                'import_file_id' => null,
            ]);

            // Tạo order items
            try {
                foreach ($validated['products'] as $index => $product) {
                    $variant = ProductVariant::findOrFail($product['variant_id']);

                    $orderItem = ExcelOrderItem::create([
                        'excel_order_id' => $order->id,
                        'part_number' => $variant->twofifteen_sku ?? $variant->sku,
                        'title' => $product['title'] ?? 'Customer Manual Order Item',
                        'quantity' => $product['quantity'],
                        'print_price' => $itemPrices[$index] ?? 0,
                        'product_id' => $variant->product_id,
                        'description' => '',
                    ]);

                    // Tạo designs
                    foreach ($product['designs'] as $design) {
                        $designUrl = $this->convertToDirectDownloadLink($design['file_url']);

                        ExcelOrderDesign::create([
                            'excel_order_item_id' => $orderItem->id,
                            'title' => $design['print_space'],
                            'url' => $designUrl
                        ]);
                    }

                    // Tạo mockups (nếu có)
                    if (!empty($product['mockups'])) {
                        foreach ($product['mockups'] as $mockup) {
                            $mockupUrl = $this->convertToDirectDownloadLink($mockup['file_url']);

                            ExcelOrderMockup::create([
                                'excel_order_item_id' => $orderItem->id,
                                'title' => $mockup['print_space'],
                                'url' => $mockupUrl
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error creating order items: ' . $e->getMessage(), [
                    'formattedData' => $formattedData,
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error creating order items: ' . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('customer.order-customer')
                ->with('success', 'Manual order created successfully! Order number: ' . $validated['order_number'])
                ->with('success_redirect', true);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating customer manual order: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the order: ' . $e->getMessage());
        }
    }

    /**
     * Convert URL to direct download link if it's a Google Drive link
     */
    private function convertToDirectDownloadLink(string $url): string
    {
        $originalUrl = trim($url);

        if (str_contains($originalUrl, 'drive.google.com')) {
            return GoogleDriveHelper::convertToDirectDownloadLink($originalUrl);
        }

        return $originalUrl;
    }

    /**
     * Validate customer manual order dựa trên OrderRowValidator logic
     */
    private function validateCustomerManualOrder(array $validated): array
    {
        $errors = [];
        $warehouse = $validated['warehouse'];
        $shippingMethod = $validated['shipping_method'] ?? '';
        $orderNote = $validated['order_note'] ?? '';

        // Validate shipping method và comment
        $this->validateShippingMethodAndComment($shippingMethod, $orderNote, $errors);

        // Validate từng product
        foreach ($validated['products'] as $productIndex => $product) {
            $variant = ProductVariant::find($product['variant_id']);
            if (!$variant) {
                $errors[] = "Product #" . ($productIndex + 1) . ": Variant không tồn tại.";
                continue;
            }

            $sku = $variant->sku;

            // Validate SKU và warehouse
            $this->validateSkuAndWarehouse($sku, $warehouse, $productIndex, $errors);

            // Validate print data (designs và mockups)
            $this->validatePrintData($product, $sku, $warehouse, $productIndex, $errors);
        }

        return $errors;
    }

    /**
     * Validate shipping method và comment
     */
    private function validateShippingMethodAndComment(string $shippingMethod, string $orderNote, array &$errors): void
    {
        $shippingMethodLower = strtolower($shippingMethod);

        // Kiểm tra shipping method chỉ được phép là 'tiktok_label' hoặc 'Tiktok_label'
        if (!empty($shippingMethod) && !in_array($shippingMethod, ['tiktok_label', 'Tiktok_label'])) {
            $errors[] = "Shipping method must be 'tiktok_label' or 'Tiktok_label', but got '$shippingMethod'.";
            return;
        }

        if ($shippingMethodLower === 'tiktok_label') {
            if (empty($orderNote)) {
                $errors[] = "Shipping method is '$shippingMethod' but no label link found in comment.";
            } else {
                // Kiểm tra xem comment có phải là link hợp lệ hay không
                if (!filter_var($orderNote, FILTER_VALIDATE_URL)) {
                    $errors[] = "Comment must contain a valid URL for shipping method '$shippingMethod'.";
                } else {
                    // Kiểm tra xem link có chứa các domain không được phép
                    if (
                        str_contains(strtolower($orderNote), 'seller-uk.tiktok.com') ||
                        str_contains(strtolower($orderNote), 'seller-us.tiktok.com') ||
                        str_contains(strtolower($orderNote), 'seller.tiktok.com')
                    ) {
                        $errors[] = "TikTok Seller links are not allowed in comment.";
                    }
                }
            }
        } else {
            // Nếu shipping method không phải 'tiktok_label', comment phải để trống
            if (!empty($orderNote)) {
                $errors[] = "Comment must be empty unless shipping method is 'tiktok_label' or 'Tiktok_label'.";
            }
        }
    }

    /**
     * Validate SKU và warehouse
     */
    private function validateSkuAndWarehouse(string $sku, string $warehouse, int $productIndex, array &$errors): void
    {
        $skuParts = explode('-', $sku);
        $skuSuffix = end($skuParts);

        if ($skuSuffix === 'UK' && $warehouse !== 'UK') {
            $errors[] = "Product #" . ($productIndex + 1) . ": SKU '$sku' is for UK warehouse but selected warehouse is $warehouse";
        }
        if ($skuSuffix === 'US' && $warehouse !== 'US') {
            $errors[] = "Product #" . ($productIndex + 1) . ": SKU '$sku' is for US warehouse but selected warehouse is $warehouse";
        }

        // Kiểm tra xem SKU được nhập có phải là twofifteen_sku hay flashship_sku không
        $variantWithTwofifteen = ProductVariant::where('twofifteen_sku', $sku)->first();
        $variantWithFlashship = ProductVariant::where('flashship_sku', $sku)->first();

        if ($variantWithTwofifteen || $variantWithFlashship) {
            $errors[] = "Product #" . ($productIndex + 1) . ": Product code (SKU) does not exist in the system: '$sku'.";
        }
    }

    /**
     * Validate print data (designs và mockups)
     */
    private function validatePrintData(array $product, string $sku, string $warehouse, int $productIndex, array &$errors): void
    {
        $productType = $this->getProductTypeFromSku($sku);
        $requiredPrintCount = $this->getRequiredPrintCount($sku);

        $designs = $product['designs'] ?? [];
        $mockups = $product['mockups'] ?? [];

        // Validate số lượng designs và mockups
        if ($requiredPrintCount !== null) {
            // SKU có 1S, 2S, 3S, 4S, 5S
            if (count($designs) !== $requiredPrintCount) {
                $errors[] = "Product #" . ($productIndex + 1) . ": SKU '$sku' requires exactly $requiredPrintCount design(s), but provided " . count($designs) . ".";
            }
            if (count($mockups) !== $requiredPrintCount) {
                $errors[] = "Product #" . ($productIndex + 1) . ": SKU '$sku' requires exactly $requiredPrintCount mockup(s), but provided " . count($mockups) . ".";
            }
        } else {
            // Logic mặc định cho các sản phẩm khác
            if (count($designs) > 0 && count($mockups) > 0) {
                if (count($designs) !== count($mockups)) {
                    $errors[] = "Product #" . ($productIndex + 1) . ": Number of designs (" . count($designs) . ") and mockups (" . count($mockups) . ") must be equal.";
                }
            }

            if (empty($designs)) {
                $errors[] = "Product #" . ($productIndex + 1) . ": At least one design URL is required.";
            }
            if (empty($mockups)) {
                $errors[] = "Product #" . ($productIndex + 1) . ": At least one mockup URL is required.";
            }
        }

        // Validate từng design và mockup
        foreach ($designs as $designIndex => $design) {
            $this->validateImageUrl($design['file_url'], $productIndex, $designIndex, 'design', $errors);
            $this->validatePrintSpace($design['print_space'], $warehouse, $productType, $productIndex, $designIndex, $errors);
        }

        foreach ($mockups as $mockupIndex => $mockup) {
            $this->validateImageUrl($mockup['file_url'], $productIndex, $mockupIndex, 'mockup', $errors);
            $this->validatePrintSpace($mockup['print_space'], $warehouse, $productType, $productIndex, $mockupIndex, $errors);
        }
    }

    /**
     * Validate image URL
     */
    private function validateImageUrl(string $url, int $productIndex, int $itemIndex, string $type, array &$errors): void
    {
        if (str_contains($url, 'drive.google.com')) {
            if (!str_contains($url, '/file/d/')) {
                $errors[] = "Product #" . ($productIndex + 1) . " $type #" . ($itemIndex + 1) . ": Google Drive link must be a sharing link.";
            }
        } else {
            if (!$this->isValidImageMime($url)) {
                $errors[] = "Product #" . ($productIndex + 1) . " $type #" . ($itemIndex + 1) . ": File is not a valid image (JPG, JPEG, PNG).";
            }
        }
    }

    /**
     * Validate print space
     */
    private function validatePrintSpace(string $printSpace, string $warehouse, string $productType, int $productIndex, int $itemIndex, array &$errors): void
    {
        $validSizes = $this->getValidSizesByProductType($productType);
        $validPositions = $this->getValidPositionsByProductType($productType);

        if ($warehouse === 'UK') {
            if (!in_array($printSpace, $validPositions)) {
                $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid print position '$printSpace'. Valid values for $productType in UK warehouse are: " . implode(', ', $validPositions);
            }
        } elseif ($warehouse === 'US') {
            if (str_contains($printSpace, '(Special)')) {
                if ($productType !== 'Default') {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Special position format is not allowed for $productType.";
                }
                $parts = explode('-', str_replace(' (Special)', '', $printSpace));
                if (count($parts) !== 2 || trim($parts[1]) !== 'Front') {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid print position format '$printSpace'. For $productType in US warehouse, Special position must be in format 'size-Front (Special)'.";
                }
                $size = trim($parts[0]);
                if (!in_array($size, $validSizes)) {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid size '$size' in position. Valid sizes for $productType are: " . implode(', ', $validSizes);
                }
            } else {
                $parts = explode('-', $printSpace);
                if (count($parts) !== 2) {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid print position format '$printSpace'. For $productType in US warehouse, position must be in format 'size-side'.";
                }

                $size = trim($parts[0]);
                $side = trim($parts[1]);

                if (!in_array($size, $validSizes)) {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid size '$size' in position. Valid sizes for $productType are: " . implode(', ', $validSizes);
                }

                if (!in_array($side, $validPositions)) {
                    $errors[] = "Product #" . ($productIndex + 1) . " item #" . ($itemIndex + 1) . ": Invalid side '$side' in position. Valid sides for $productType in US warehouse are: " . implode(', ', $validPositions);
                }
            }
        }
    }

    /**
     * Validate image MIME type
     */
    private function isValidImageMime(string $url): bool
    {
        $validImageMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $headers = @get_headers($url, 1);
        if (!$headers) return false;
        $mime = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : '';
        return in_array(strtolower($mime), $validImageMimeTypes);
    }

    /**
     * Get product type from SKU
     */
    private function getProductTypeFromSku(string $sku): string
    {
        if (str_starts_with($sku, 'OS01')) {
            return 'BabyOnesie';
        } elseif (str_starts_with($sku, 'DIECUT-MAGNET')) {
            return 'Diecut-Magnet';
        } elseif (str_starts_with($sku, 'MAGNET')) {
            return 'Magnet';
        } elseif (str_starts_with($sku, 'UV-STICKER')) {
            return 'UV Sticker';
        } elseif (str_starts_with($sku, 'VINYL-STICKER')) {
            return 'Vinyl Sticker';
        } elseif (str_starts_with($sku, 'CASE-IPHONE')) {
            return 'Phone Case';
        } elseif (str_starts_with($sku, 'TOTEBAG') || str_starts_with($sku, 'MUG')) {
            return 'Tote Bag';
        } elseif (str_starts_with($sku, 'MUG')) {
            return 'Mug';
        }
        return 'Default';
    }

    /**
     * Get required print count from SKU
     */
    private function getRequiredPrintCount(string $sku): ?int
    {
        $skuParts = explode('-', $sku);
        $printCountIndicator = $skuParts[count($skuParts) - 2] ?? '';
        if ($printCountIndicator === '1S') {
            return 1;
        } elseif ($printCountIndicator === '2S') {
            return 2;
        }
        if ($printCountIndicator === '3S') {
            return 3;
        }
        if ($printCountIndicator === '4S') {
            return 4;
        }
        if ($printCountIndicator === '5S') {
            return 5;
        }
        return null; // Không có yêu cầu cụ thể
    }

    /**
     * Get valid positions by product type
     */
    private function getValidPositionsByProductType(string $productType): array
    {
        $validPositionsByProductType = [
            'BabyOnesie' => ['Front', 'Back'],
            'Magnet' => ['Front'],
            'Diecut-Magnet' => ['Front'],
            'UV Sticker' => ['Front'],
            'Vinyl Sticker' => ['Front'],
            'Phone Case' => ['Front'],
            'Tote Bag' => ['Front', 'Back'],
            'Mug' => ['Front'],
            'Default' => ['Front', 'Back', 'Right Sleeve', 'Left Sleeve']
        ];

        return $validPositionsByProductType[$productType] ?? $validPositionsByProductType['Default'];
    }

    /**
     * Get valid sizes by product type
     */
    private function getValidSizesByProductType(string $productType): array
    {
        $validSizesByProductType = [
            'BabyOnesie' => ['0-6', '6-12', '12-18', '18-24'],
            'Magnet' => ['5x5', '7.5x4.5', '10x3'],
            'Diecut-Magnet' => ['2x2', '3x3', '4x4', '5x5', '6x6'],
            'UV Sticker' => ['2x2', '3x3', '4x4', '5x5', '6x6', '7x7', '8x8', '9x9', '10x10', '12x12', '15x15', '18x18', '20x20'],
            'Vinyl Sticker' => ['3x4', '6x8', '8x10'],
            'Phone Case' => ['15', '15 Pro', '15 Pro Max', '15 Plus', '16', '16 Plus', '16 Pro', '16 Pro Max'],
            'Tote Bag' => ['Tote Bag'],
            'Mug' => ['Mug'],
            'Default' => ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL']
        ];

        return $validSizesByProductType[$productType] ?? $validSizesByProductType['Default'];
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
            // Đã bỏ cập nhật status của các ExcelOrder liên quan

            // Có thể thêm logic gửi email thông báo cho khách hàng
            // $this->sendProcessedNotification($importFile);

            Log::info('Processed status logic completed', [
                'file_id' => $importFile->id,
                'excel_orders_updated' => 0 // Không còn cập nhật đơn hàng
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
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'address' => 'required|string|max:500',
                'address_2' => 'nullable|string|max:500',
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
                'products.*.designs.*.file_url' => 'required_with:products.*.designs|string|regex:/^https?:\/\/.+/',
                'products.*.designs.*.print_space' => ['required', 'string', new ValidPrintSpace],
                'products.*.mockups' => 'nullable|array',
                'products.*.mockups.*.file_url' => 'required_with:products.*.mockups|string|regex:/^https?:\/\/.+/',
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

            // Lấy tier hiện tại của user
            $userTier = \App\Models\UserTier::getCurrentTier($user->id);
            $currentTier = $userTier ? $userTier->tier : 'Wood';
            Log::info('[API-ORDER] Tier hiện tại của user', [
                'user_id' => $user->id,
                'tier' => $currentTier,
                'tier_data' => $userTier
            ]);

            // Logic tính giá sẽ áp dụng thứ tự ưu tiên:
            // 1. Giá theo tier của user (tier_name = $currentTier)
            // 2. Giá mặc định (tier_name = null)
            // 3. Giá Wood tier (tier_name = 'Wood') làm fallback cuối cùng

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
                        'first_item_price' => $variant->getFirstItemPrice($validated['shipping_method'] ?? null, $user->id),
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
                        $priceInfo1 = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, 1, $user->id);
                        $priceInfo2 = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, 2, $user->id);

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
                                'tier_price' => $priceInfo1['tier_price'] ?? false,
                                'tier' => $priceInfo1['tier'] ?? null,
                                'breakdown' => "1x{$firstPrice} + " . ($quantity - 1) . "x{$secondPrice}"
                            ];

                            Log::info('[API-ORDER] Tính giá (first_item_mix)', [
                                'external_id' => $validated['order_number'],
                                'part_number' => $partNumber,
                                'quantity' => $quantity,
                                'first_item_price' => $firstPrice,
                                'second_item_price' => $secondPrice,
                                'item_total' => $itemTotal,
                                'average_price' => $averagePrice,
                                'tier_price' => $priceInfo1['tier_price'] ?? false,
                                'tier' => $priceInfo1['tier'] ?? null,
                                'price_source' => $priceInfo1['tier_price'] ? 'tier_specific' : 'default_or_fallback',
                                'breakdown' => $priceBreakdown['breakdown']
                            ]);
                        }
                    } else {
                        // Trường hợp thông thường: tất cả items tính cùng 1 giá
                        $position = $isFirstItem ? 1 : 2;
                        $priceInfo = $variant->getOrderPriceInfo($validated['shipping_method'] ?? null, $position, $user->id);

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
                                'tier_price' => $priceInfo['tier_price'] ?? false,
                                'tier' => $priceInfo['tier'] ?? null,
                                'breakdown' => $quantity . "x" . $unitPrice
                            ];

                            Log::info('[API-ORDER] Tính giá (' . ($isFirstItem ? 'first_item' : 'second_item') . ')', [
                                'external_id' => $validated['order_number'],
                                'part_number' => $partNumber,
                                'quantity' => $quantity,
                                'unit_price' => $unitPrice,
                                'item_total' => $itemTotal,
                                'average_price' => $averagePrice,
                                'tier_price' => $priceInfo['tier_price'] ?? false,
                                'tier' => $priceInfo['tier'] ?? null,
                                'price_source' => $priceInfo['tier_price'] ? 'tier_specific' : 'default_or_fallback',
                                'breakdown' => $priceBreakdown['breakdown']
                            ]);
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
            // Validate shipping label link bắt buộc
            if (($validated['shipping_method'] ?? ($existingOrder->shipping_method ?? null)) === 'tiktok_label') {
                if (empty($validated['order_note'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order note (shipping label link) is required when shipping_method is tiktok_label.'
                    ], 400);
                }
            }

            // Gán comment nếu có
            if (isset($validated['order_note'])) {
                $updateData['comment'] = $validated['order_note'];
            }


            $order = ExcelOrder::create([
                'external_id' => $validated['order_number'],
                'brand' => $validated['store_name'] ?? '',
                'channel' => $validated['channel'] ?? 'api',
                'buyer_email' => $validated['customer_email'] ?? '',
                'first_name' => explode(' ', $validated['customer_name'])[0] ?? '',
                'last_name' => substr($validated['customer_name'], strpos($validated['customer_name'], ' ') + 1) ?: '',
                'address1' => $validated['address'],
                'address2' => $validated['address_2'] ?? null,
                'city' => $validated['city'] ?? '',
                'county' => $validated['state'] ?? '',
                'post_code' => $validated['postcode'] ?? '',
                'country' => $validated['country'] ?? '',
                'phone1' => $validated['customer_phone'] ?? '',
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
            // Kiểm tra xem request có phải từ admin hay không
            $isAdmin = Auth::check() && Auth::user()->role === 'admin';

            if ($isAdmin) {
                // Xử lý admin cancel order
                return $this->adminCancelOrder($request, $orderId);
            } else {
                // Xử lý customer cancel order (logic cũ)
                return $this->customerCancelOrder($request, $orderId);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function adminCancelOrder(Request $request, $orderId)
    {
        try {
            // 1. Tìm đơn hàng
            $order = ExcelOrder::with(['items', 'creator'])->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // 2. Kiểm tra trạng thái đơn hàng
            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already cancelled'
                ], 400);
            }

            // Admin có thể cancel order ở tất cả trạng thái
            DB::beginTransaction();

            try {
                // 3. Tính toán số tiền cần hoàn
                $orderTotal = $order->items->sum(function ($item) {
                    return (float)$item->print_price * (int)$item->quantity;
                });

                // 4. Hoàn tiền cho khách hàng
                $wallet = $order->creator->wallet;
                if (!$wallet) {
                    throw new \Exception('Customer wallet not found');
                }

                // Thêm tiền vào wallet
                $wallet->deposit($orderTotal);

                // 5. Tạo transaction hoàn tiền
                $reason = $request->input('reason', 'Admin cancelled order');
                $refundTransaction = Transaction::create([
                    'user_id' => $order->created_by,
                    'type' => Transaction::TYPE_REFUND,
                    'method' => Transaction::METHOD_VND,
                    'amount' => $orderTotal,
                    'status' => Transaction::STATUS_APPROVED,
                    'transaction_code' => 'ADMIN_CANCEL_' . $order->external_id . '_' . time(),
                    'note' => "Admin cancelled order: {$order->external_id}. Reason: {$reason}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 6. Cập nhật trạng thái đơn hàng
                $cancelReason = $reason ? " - Reason: {$reason}" : "";
                $order->update([
                    'status' => 'cancelled',
                    'comment' => $order->comment . "\nCancelled by admin at: " . now()->format('Y-m-d H:i:s') . $cancelReason
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled and refunded successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->external_id,
                        'status' => $order->status,
                        'refund_amount' => number_format($orderTotal, 2, '.', ''),
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

    private function customerCancelOrder(Request $request, $orderId)
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
     * Cancel order for customer via web interface
     */
    public function cancelCustomerOrder(Request $request, $orderId)
    {
        try {
            $userId = Auth::id();

            // Tìm đơn hàng của customer với items
            $order = ExcelOrder::with('items')
                ->where('id', $orderId)
                ->where('created_by', $userId)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            // Kiểm tra trạng thái đơn hàng
            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng đã được hủy trước đó'
                ], 400);
            }

            if ($order->status !== 'on hold') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể hủy đơn hàng đang ở trạng thái "On Hold"'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Tính tổng giá trị đơn hàng
                $orderTotal = $order->items->sum(function ($item) {
                    return $item->print_price * $item->quantity;
                });

                if ($orderTotal <= 0) {
                    throw new \Exception('Đơn hàng không có giá trị để hoàn tiền');
                }

                // Tạo transaction hoàn tiền
                $refundTransaction = Transaction::create([
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'type' => Transaction::TYPE_REFUND,
                    'method' => Transaction::METHOD_VND,
                    'amount' => $orderTotal,
                    'status' => Transaction::STATUS_APPROVED,
                    'transaction_code' => 'REFUND_' . $order->external_id . '_' . time(),
                    'note' => "Hoàn tiền cho đơn hàng đã hủy: {$order->external_id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Cập nhật balance của user qua Wallet
                $user = User::find($userId);
                $wallet = $user->wallet;

                if (!$wallet) {
                    throw new \Exception('Không tìm thấy ví của người dùng');
                }

                if (!$wallet->deposit($orderTotal)) {
                    throw new \Exception('Không thể thêm tiền vào ví');
                }

                // Cập nhật trạng thái đơn hàng
                $order->update([
                    'status' => 'cancelled',
                    'comment' => ($order->comment ?? '') . "\nHủy đơn bởi khách hàng lúc: " . now()->format('Y-m-d H:i:s') . " - Đã hoàn tiền: $" . number_format($orderTotal, 2)
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Đơn hàng đã được hủy và hoàn tiền thành công',
                    'refund_amount' => number_format($refundTransaction->amount, 2)
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy đơn hàng: ' . $e->getMessage()
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
     * Lấy danh sách tất cả đơn hàng dành cho admin
     */
    public function getAdminAllOrders(Request $request)
    {
        try {
            // Khởi tạo query lấy tất cả đơn hàng
            $query = ExcelOrder::with(['items', 'creator', 'orderMapping', 'importFile']);

            // Thêm điều kiện tìm kiếm theo external_id nếu có
            if ($request->filled('external_id')) {
                $searchTerm = trim($request->external_id);
                $query->where('external_id', 'LIKE', "%{$searchTerm}%");
            }

            // Thêm điều kiện tìm kiếm theo tên khách hàng
            if ($request->filled('customer_name')) {
                $searchTerm = trim($request->customer_name);
                $query->whereHas('creator', function ($q) use ($searchTerm) {
                    $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$searchTerm}%"]);
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

            // Thêm điều kiện tìm kiếm theo nguồn đơn hàng (API hoặc File upload)
            if ($request->filled('order_source')) {
                if ($request->order_source === 'api') {
                    $query->whereNull('import_file_id');
                } elseif ($request->order_source === 'file') {
                    $query->whereNotNull('import_file_id');
                }
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
                'api_orders' => $orders->where('import_file_id', null)->count(),
                'file_orders' => $orders->where('import_file_id', '!=', null)->count(),
                'total_amount' => $orders->sum(function ($order) {
                    return $order->items->sum(function ($item) {
                        return $item->print_price * $item->quantity;
                    });
                })
            ];

            // Lấy danh sách các warehouse có sẵn
            $warehouses = ExcelOrder::distinct()->pluck('warehouse');

            // Lấy danh sách các trạng thái có sẵn
            $statuses = ExcelOrder::distinct()->pluck('status');

            // Trả về view với dữ liệu
            return view('admin.orders.all-order-list', [
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
                    'order_source',
                    'created_at_min',
                    'created_at_max'
                ])
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting admin all orders list:', [
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
     * Lấy danh sách đơn hàng được tạo qua API (import_file_id = null) dành cho admin
     */
    public function getAdminApiOrders(Request $request)
    {
        try {
            // Khởi tạo query với điều kiện chỉ lấy đơn hàng từ API
            $query = ExcelOrder::with(['items', 'creator', 'orderMapping'])
                ->whereNull('import_file_id');

            // Thêm điều kiện tìm kiếm theo external_id nếu có
            if ($request->filled('external_id')) {
                $searchTerm = trim($request->external_id);
                $query->where('external_id', 'LIKE', "%{$searchTerm}%");
            }

            // Thêm điều kiện tìm kiếm theo tên khách hàng
            if ($request->filled('customer_name')) {
                $searchTerm = trim($request->customer_name);
                $query->whereHas('creator', function ($q) use ($searchTerm) {
                    $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$searchTerm}%"]);
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

            // Lấy danh sách các warehouse có sẵn (chỉ từ đơn hàng API)
            $warehouses = ExcelOrder::whereNull('import_file_id')
                ->distinct()
                ->pluck('warehouse');

            // Lấy danh sách các trạng thái có sẵn (chỉ từ đơn hàng API)
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
                'message' => 'Có lỗi khi tải danh sách đơn hàng API: ' . $e->getMessage()
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


        foreach ($allItems as $index => $item) {
            $breakdown = $itemPriceBreakdowns[$index] ?? null;
        }
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
                            'first_item_price' => $variant->getFirstItemPrice($shippingMethod, $user->id),
                            'part_number' => $variant->twofifteen_sku ?? $product['part_number']
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
                        $specialPriceAdjustment = 0;

                        // Kiểm tra position Special (chỉ áp dụng cho warehouse US)
                        if ($existingOrder->warehouse === 'US') {
                            // Kiểm tra trong designs có position chứa (Special) không
                            if (!empty($product['designs'])) {
                                foreach ($product['designs'] as $design) {
                                    if (str_contains($design['print_space'] ?? '', '(Special)')) {
                                        $specialPriceAdjustment = 2 * $quantity; // +$2 cho mỗi quantity
                                        break;
                                    }
                                }
                            }
                        }

                        if ($isFirstItem && $quantity > 1) {
                            $priceInfo1 = $variant->getOrderPriceInfo($shippingMethod, 1, $user->id);
                            $priceInfo2 = $variant->getOrderPriceInfo($shippingMethod, 2, $user->id);

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
                                    'tier_price' => $priceInfo1['tier_price'] ?? false,
                                    'tier' => $priceInfo1['tier'] ?? null,
                                    'breakdown' => "1x{$firstPrice} + " . ($quantity - 1) . "x{$secondPrice}"
                                ];

                                Log::info('[UPDATE] Tính giá (first_item_mix)', [
                                    'external_id' => $existingOrder->external_id,
                                    'part_number' => $partNumber,
                                    'quantity' => $quantity,
                                    'first_item_price' => $firstPrice,
                                    'second_item_price' => $secondPrice,
                                    'special_price_adjustment' => $specialPriceAdjustment,
                                    'item_total' => $itemTotal,
                                    'average_price' => $averagePrice,
                                    'tier_price' => $priceInfo1['tier_price'] ?? false,
                                    'tier' => $priceInfo1['tier'] ?? null,
                                    'price_source' => $priceInfo1['tier_price'] ? 'tier_specific' : 'default_or_fallback',
                                    'breakdown' => $priceBreakdown['breakdown']
                                ]);
                            }
                        } else {
                            $position = $isFirstItem ? 1 : 2;
                            $priceInfo = $variant->getOrderPriceInfo($shippingMethod, $position, $user->id);

                            if ($priceInfo['shipping_price_found']) {
                                $unitPrice = round($priceInfo['print_price'], 2);
                                $itemTotal = $unitPrice * $quantity;
                                $itemTotal = round($itemTotal, 2);
                                $averagePrice = $unitPrice;

                                $priceBreakdown = [
                                    'unit_price' => $unitPrice,
                                    'quantity' => $quantity,
                                    'is_first_item' => $isFirstItem,
                                    'tier_price' => $priceInfo['tier_price'] ?? false,
                                    'tier' => $priceInfo['tier'] ?? null,
                                    'breakdown' => $quantity . "x" . $unitPrice
                                ];

                                Log::info('[UPDATE] Tính giá (' . ($isFirstItem ? 'first_item' : 'second_item') . ')', [
                                    'external_id' => $existingOrder->external_id,
                                    'part_number' => $partNumber,
                                    'quantity' => $quantity,
                                    'unit_price' => $unitPrice,
                                    'special_price_adjustment' => $specialPriceAdjustment,
                                    'item_total' => $itemTotal,
                                    'average_price' => $averagePrice,
                                    'tier_price' => $priceInfo['tier_price'] ?? false,
                                    'tier' => $priceInfo['tier'] ?? null,
                                    'price_source' => $priceInfo['tier_price'] ? 'tier_specific' : 'default_or_fallback',
                                    'breakdown' => $priceBreakdown['breakdown']
                                ]);
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
                $customerNameParts = explode(' ', trim($validated['customer_name']), 2);
                $updateData['first_name'] = $customerNameParts[0] ?? '';
                $updateData['last_name'] = $customerNameParts[1] ?? '';
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

            // Validate shipping label link bắt buộc
            if (($validated['shipping_method'] ?? ($existingOrder->shipping_method ?? null)) === 'tiktok_label') {
                if (empty($validated['order_note'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order note (shipping label link) is required when shipping_method is tiktok_label.'
                    ], 400);
                }
            }

            // Gán comment nếu có
            if (isset($validated['order_note'])) {
                $updateData['comment'] = $validated['order_note'];
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

    /**
     * Xây dựng dữ liệu đơn hàng để gửi qua API factory
     */
    private function buildOrderDataForFactory($order)
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
                    'country' => 'US',
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

    /**
     * Xây dựng cấu hình API cho factory
     */
    private function buildApiConfigForFactory($factory, $data)
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

    /**
     * Xử lý response từ API factory
     */
    private function processFactoryOrderResponse($order, $response, $factory = null)
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

    /**
     * Xuất file CSV thông tin đơn hàng
     */


    public function exportOrdersCSV(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'order_ids' => 'nullable|array',
                'order_ids.*' => 'integer|exists:excel_orders,id',
                'order_ids_input' => 'nullable|string',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'status' => 'nullable|string',
                'warehouse' => 'nullable|string',
                'external_id' => 'nullable|string',
                'customer_name' => 'nullable|string',
                'export_all' => 'nullable|boolean',
                'export_format' => 'nullable|string|in:csv,tsv'
            ]);

            // Process order_ids from textarea input if provided
            $orderIds = $request->input('order_ids', []);
            if ($request->filled('order_ids_input')) {
                $inputIds = explode(',', $request->input('order_ids_input'));
                $inputIds = array_map('trim', $inputIds);
                $inputIds = array_filter($inputIds, 'is_numeric');
                $inputIds = array_map('intval', $inputIds);

                if (!empty($inputIds)) {
                    $orderIds = $inputIds;
                }
            }

            // Validate and fix date range if needed
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
                // Swap dates if from > to
                $temp = $dateFrom;
                $dateFrom = $dateTo;
                $dateTo = $temp;

                Log::warning('CSV Export - Date range corrected', [
                    'original_from' => $request->input('date_from'),
                    'original_to' => $request->input('date_to'),
                    'corrected_from' => $dateFrom,
                    'corrected_to' => $dateTo
                ]);

                // Update request data
                $request->merge([
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ]);
            }

            // Xây dựng query - đơn giản hóa relationships
            $query = ExcelOrder::with([
                'items.mockups',
                'items.designs',
                'creator'
            ]);

            // Debug: Log số lượng đơn hàng total
            $totalOrders = ExcelOrder::count();
            Log::info('CSV Export Debug - Total orders in database:', ['total' => $totalOrders]);

            // Áp dụng filters
            if (!empty($orderIds)) {
                $query->whereIn('id', $orderIds);
                Log::info('CSV Export - Filtering by order IDs:', ['order_ids' => $orderIds]);
            }

            // Chỉ áp dụng date filter nếu không chọn export_all
            if (!$request->input('export_all')) {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                    Log::info('CSV Export - Date from filter:', ['date_from' => $request->date_from]);
                }

                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                    Log::info('CSV Export - Date to filter:', ['date_to' => $request->date_to]);
                }
            } else {
                Log::info('CSV Export - Export all orders selected, skipping date filters');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
                Log::info('CSV Export - Status filter:', ['status' => $request->status]);
            }

            if ($request->filled('warehouse')) {
                $query->where('warehouse', $request->warehouse);
                Log::info('CSV Export - Warehouse filter:', ['warehouse' => $request->warehouse]);
            }

            if ($request->filled('external_id')) {
                $query->where('external_id', 'like', '%' . $request->external_id . '%');
                Log::info('CSV Export - External ID filter:', ['external_id' => $request->external_id]);
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('creator', function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->customer_name . '%')
                        ->orWhere('last_name', 'like', '%' . $request->customer_name . '%')
                        ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ['%' . $request->customer_name . '%']);
                });
                Log::info('CSV Export - Customer name filter:', ['customer_name' => $request->customer_name]);
            }

            // Debug: Log query trước khi execute
            $queryCount = $query->count();
            Log::info('CSV Export - Query result count:', ['count' => $queryCount]);

            $orders = $query->orderBy('created_at', 'desc')->get();

            if ($orders->isEmpty()) {
                // Debug: Kiểm tra ngày tạo đơn hàng gần nhất và cũ nhất
                $latestOrder = ExcelOrder::orderBy('created_at', 'desc')->first();
                $earliestOrder = ExcelOrder::orderBy('created_at', 'asc')->first();

                // Debug: Đếm đơn hàng theo ngày gần đây
                $ordersByDate = ExcelOrder::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->limit(10)
                    ->get();

                $debugInfo = [
                    'total_orders_in_database' => $totalOrders,
                    'applied_filters' => $request->only(['date_from', 'date_to', 'status', 'warehouse', 'external_id', 'customer_name', 'export_all']),
                    'order_ids_filter' => $orderIds,
                    'latest_order_date' => $latestOrder ? $latestOrder->created_at->format('Y-m-d H:i:s') : null,
                    'earliest_order_date' => $earliestOrder ? $earliestOrder->created_at->format('Y-m-d H:i:s') : null,
                    'recent_dates_with_orders' => $ordersByDate->map(function ($item) {
                        return [
                            'date' => $item->date,
                            'count' => $item->count
                        ];
                    })->toArray(),
                    'suggestions' => [
                        'valid_date_range' => [
                            'from' => $earliestOrder ? $earliestOrder->created_at->format('Y-m-d') : null,
                            'to' => $latestOrder ? $latestOrder->created_at->format('Y-m-d') : null
                        ],
                        'message' => 'Vui lòng chọn khoảng thời gian từ ' .
                            ($earliestOrder ? $earliestOrder->created_at->format('Y-m-d') : 'N/A') .
                            ' đến ' .
                            ($latestOrder ? $latestOrder->created_at->format('Y-m-d') : 'N/A') .
                            ' hoặc tick "Export All Orders".'
                    ]
                ];

                // Log thêm thông tin debug
                Log::warning('CSV Export - No orders found', $debugInfo);

                $message = 'Không có đơn hàng nào để xuất. ';

                if ($request->filled('date_from') && $request->filled('date_to')) {
                    $message .= 'Khoảng thời gian đã chọn (' . $request->date_from . ' - ' . $request->date_to . ') không có đơn hàng. ';
                }

                $message .= $debugInfo['suggestions']['message'];

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'debug' => $debugInfo
                ], 400);
            }

            // Chuẩn bị dữ liệu CSV
            $csvData = [];
            $headers = [
                'External_ID',
                'Internal_ID',
                'Customer_Name',
                'Customer_Email',
                'Link_Label',
                'Order_Status',
                'SKU',
                'Variant',
                'Quantity',
                'Price',
                'Mockup_1',
                'Design_1',
                'Mockup_2',
                'Design_2',
                'Mockup_3',
                'Design_3',
                'Mockup_4',
                'Design_4',
                'Mockup_5',
                'Design_5',
                'Tracking_Number',
                'Created_Date',
                'Created_Time'
            ];

            $csvData[] = $headers;

            // Xác định format trước khi process data
            $exportFormat = $request->input('export_format', 'csv');

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    // Lấy thông tin variant
                    $variantInfo = $this->getVariantInfo($item->part_number);

                    // Chuẩn bị mockups và designs
                    $mockups = $item->mockups->pluck('url')->toArray();
                    $designs = $item->designs->pluck('url')->toArray();

                    // Đảm bảo có đủ 5 slots cho mockup và design
                    $mockups = array_pad($mockups, 5, '');
                    $designs = array_pad($designs, 5, '');

                    // Format External ID dựa trên export format
                    $externalIdForExport = $this->formatExternalIdForExport($order->external_id, $exportFormat);

                    // Lấy Internal ID từ OrderMapping
                    $internalId = '';
                    $orderMapping = OrderMapping::where('external_id', $order->external_id)->first();
                    if ($orderMapping) {
                        $internalId = $orderMapping->internal_id;
                    }

                    // Lấy thông tin customer
                    $customerName = '';
                    $customerEmail = '';
                    if ($order->creator) {
                        $customerName = $order->creator->getFullName();
                        $customerEmail = $order->creator->email ?? '';
                    }

                    $row = [
                        $externalIdForExport,
                        $internalId,
                        $customerName,
                        $customerEmail,
                        $order->comment ?? '',
                        $order->status,
                        $item->part_number,
                        $variantInfo,
                        $item->quantity,
                        number_format($item->print_price, 2, '.', ''),
                        $mockups[0],
                        $designs[0],
                        $mockups[1],
                        $designs[1],
                        $mockups[2],
                        $designs[2],
                        $mockups[3],
                        $designs[3],
                        $mockups[4],
                        $designs[4],
                        $order->tracking_number ?? '',
                        $order->created_at->format('Y-m-d'),
                        $order->created_at->format('H:i:s')
                    ];

                    $csvData[] = $row;
                }
            }

            // Xác định extension và delimiter
            $extension = $exportFormat === 'tsv' ? 'tsv' : 'csv';
            $delimiter = $exportFormat === 'tsv' ? "\t" : ',';

            // Tạo file
            $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.' . $extension;
            $filePath = storage_path('app/temp/' . $filename);

            // Tạo thư mục temp nếu chưa tồn tại
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $file = fopen($filePath, 'w');

            // Thêm BOM để Excel hiển thị UTF-8 đúng
            fwrite($file, "\xEF\xBB\xBF");

            foreach ($csvData as $row) {
                if ($exportFormat === 'tsv') {
                    // TSV: Tab-separated, không cần quote (tốt hơn cho external_id số lớn)
                    fwrite($file, implode("\t", $row) . "\n");
                } else {
                    // CSV: Comma-separated với quotes
                    fputcsv($file, $row, ',', '"', '\\');
                }
            }
            fclose($file);

            Log::info('Export completed', [
                'filename' => $filename,
                'format' => $exportFormat,
                'orders_count' => $orders->count(),
                'total_rows' => count($csvData) - 1, // Trừ header
                'created_by' => Auth::id()
            ]);

            // Trả về file download
            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error exporting orders:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'created_by' => Auth::id(),
                'format' => $request->input('export_format', 'csv')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage()
            ], 500);
        }
    }
    public function exportCustomerOrdersCSV(Request $request)
    {
        try {
            // Xác thực yêu cầu
            $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'status' => 'nullable|string',
                'export_format' => 'nullable|string|in:csv,tsv',
                'selected_orders' => 'nullable|string' // Cho chế độ xuất đơn hàng được chọn
            ]);

            // Kiểm tra và sửa khoảng thời gian nếu cần
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
                // Đổi chỗ ngày nếu date_from > date_to
                [$dateFrom, $dateTo] = [$dateTo, $dateFrom];

                Log::warning('Xuất CSV Khách hàng - Sửa khoảng thời gian', [
                    'original_from' => $request->input('date_from'),
                    'original_to' => $request->input('date_to'),
                    'corrected_from' => $dateFrom,
                    'corrected_to' => $dateTo,
                    'created_by' => Auth::id()
                ]);

                $request->merge([
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ]);
            }

            // Xây dựng truy vấn cho đơn hàng của người dùng đã xác thực
            $query = ExcelOrder::where('created_by', Auth::id())
                ->with(['items.mockups', 'items.designs']);

            // Áp dụng bộ lọc cho chế độ "Export All"
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
                Log::info('Xuất CSV Khách hàng - Bộ lọc ngày bắt đầu:', [
                    'date_from' => $request->date_from
                ]);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
                Log::info('Xuất CSV Khách hàng - Bộ lọc ngày kết thúc:', [
                    'date_to' => $request->date_to
                ]);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
                Log::info('Xuất CSV Khách hàng - Bộ lọc trạng thái:', [
                    'status' => $request->status,
                    'created_by' => Auth::id()
                ]);
            }

            // Xử lý chế độ "Export Selected"
            if ($request->filled('selected_orders')) {
                $selectedOrderIds = array_filter(explode(',', $request->input('selected_orders')));
                if (!empty($selectedOrderIds)) {
                    $query->whereIn('id', $selectedOrderIds);
                    Log::info('Xuất CSV Khách hàng - Bộ lọc đơn hàng được chọn:', [
                        'selected_orders' => $selectedOrderIds,
                        'created_by' => Auth::id()
                    ]);
                }
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            // Log thông tin khi không có đơn hàng
            if ($orders->isEmpty()) {
                Log::info('Xuất CSV Khách hàng - Không tìm thấy đơn hàng, xuất file trống', [
                    'applied_filters' => $request->only(['date_from', 'date_to', 'status', 'selected_orders']),
                    'created_by' => Auth::id()
                ]);
            }

            // Chuẩn bị dữ liệu CSV
            $csvData = [];
            $headers = [
                'Order_ID',
                'Customer_Name',
                'First_Name',
                'Last_Name',
                'Email',
                'Address1',
                'Address2',
                'City',
                'County',
                'Postcode',
                'Country',
                'Phone1',
                'Phone2',
                'Label_Url',
                'Order_Status',
                'Shipping_Method',
                'SKU',
                'Variant',
                'Quantity',
                'Price',
                'Mockup_1',
                'Design_1',
                'Tracking_Number',
                'Created_Date',
                'Created_Time'
            ];

            $csvData[] = $headers;

            $exportFormat = $request->input('export_format', 'csv');

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    $variantInfo = $this->getVariantInfo($item->part_number); // Phương thức giả định
                    $mockups = $item->mockups->pluck('url')->toArray();
                    $designs = $item->designs->pluck('url')->toArray();
                    $mockups = array_pad($mockups, 1, '');
                    $designs = array_pad($designs, 1, '');

                    $externalIdForExport = $this->formatExternalIdForExport($order->external_id, $exportFormat); // Phương thức giả định

                    // Lấy tên đầy đủ của customer
                    $customerName = trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? ''));

                    $row = [
                        $externalIdForExport,
                        $customerName,
                        $order->first_name ?? '',
                        $order->last_name ?? '',
                        $order->buyer_email ?? '',
                        $order->address1 ?? '',
                        $order->address2 ?? '',
                        $order->city ?? '',
                        $order->county ?? '',
                        $order->post_code ?? '',
                        $order->country ?? '',
                        $order->phone1 ?? '',
                        $order->phone2 ?? '',
                        $order->label_url ?? '',
                        $order->status,
                        $order->shipping_method ?? '',
                        $item->part_number,
                        $variantInfo,
                        $item->quantity,
                        number_format($item->print_price, 2, '.', ''),
                        $mockups[0],
                        $designs[0],
                        $order->tracking_number ?? '',
                        $order->created_at->format('Y-m-d'),
                        $order->created_at->format('H:i:s')
                    ];

                    $csvData[] = $row;
                }
            }

            // Xác định phần mở rộng và ký tự phân tách
            $extension = $exportFormat === 'tsv' ? 'tsv' : 'csv';
            $delimiter = $exportFormat === 'tsv' ? "\t" : ',';

            // Tạo file
            $filename = 'my_orders_export_' . date('Y-m-d_H-i-s') . '.' . $extension;
            $filePath = storage_path('app/temp/' . $filename);

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $file = fopen($filePath, 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM UTF-8 cho tương thích Excel

            foreach ($csvData as $row) {
                if ($exportFormat === 'tsv') {
                    fwrite($file, implode("\t", $row) . "\n");
                } else {
                    fputcsv($file, $row, $delimiter, '"', '\\');
                }
            }
            fclose($file);


            return response()->download($filePath, $filename, [
                'Content-Type' => $exportFormat === 'tsv' ? 'text/tab-separated-values' : 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xuất đơn hàng khách hàng:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'created_by' => Auth::id(),
                'format' => $request->input('export_format', 'csv')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xuất đơn hàng của bạn: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Format external_id cho export với cách xử lý đúng cho CSV và TSV
     */
    private function formatExternalIdForExport($externalId, $format)
    {
        // Ép external_id thành chuỗi
        $formattedId = (string)$externalId;

        if ($format === 'tsv') {
            // TSV: Thêm apostrophe để force Excel hiểu là text
            return "'" . $formattedId;
        } else {
            // CSV: Thêm apostrophe để force Excel hiểu là text (không convert thành scientific notation)
            return "'" . $formattedId;
        }
    }


    /**
     * Lấy thông tin variant theo format: Size,Color,Sides
     */
    private function getVariantInfo($sku)
    {
        try {
            // Tìm variant theo SKU - đơn giản hóa không dùng with('attributes')
            $variant = ProductVariant::where('sku', $sku)
                ->orWhere('twofifteen_sku', $sku)
                ->orWhere('flashship_sku', $sku)
                ->first();

            if (!$variant) {
                Log::debug('No variant found for SKU: ' . $sku);
                return $sku; // Trả về SKU gốc nếu không tìm thấy variant
            }

            // Tìm attributes trực tiếp từ bảng variant_attributes
            $attributes = VariantAttribute::where('variant_id', $variant->id)->get();

            if ($attributes->isEmpty()) {
                Log::debug('No attributes found for variant ID: ' . $variant->id);
                return $sku; // Trả về SKU nếu không có attributes
            }

            $attributeData = $attributes->pluck('value', 'name')->toArray();

            // Chuẩn bị thông tin variant theo format: Size,Color,Sides
            // Support case variations
            $size = $attributeData['Size'] ?? $attributeData['size'] ?? $attributeData['SIZE'] ?? '';
            $color = $attributeData['Color'] ?? $attributeData['Colour'] ?? $attributeData['color'] ?? $attributeData['COLOR'] ?? '';
            $sides = $attributeData['Sides'] ?? $attributeData['Side'] ?? $attributeData['sides'] ?? $attributeData['side'] ?? $attributeData['SIDES'] ?? '';

            // Trả về format: S,White,1 side
            $variantParts = array_filter([$size, $color, $sides]);
            $result = !empty($variantParts) ? implode(',', $variantParts) : $sku;



            return $result;
        } catch (\Exception $e) {
            Log::warning('Error getting variant info for SKU: ' . $sku, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $sku; // Fallback trả về SKU gốc
        }
    }

    /**
     * Đẩy đơn hàng qua xưởng (factory)
     */
    public function processOrdersToFactory(Request $request)
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
                    $orderData = $this->buildOrderDataForFactory($order);
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
                    $apiConfig = $this->buildApiConfigForFactory('dtf', $dtfOrders);
                    $response = Http::withHeaders($apiConfig['headers'])
                        ->withQueryParameters($apiConfig['parameters'])
                        ->post($apiConfig['config']['apiUrl'] . '/api/orders/batch', $dtfOrders);

                    if ($response->successful()) {
                        $apiResponse = $response->json();



                        foreach ($apiResponse['orders'] as $orderResponse) {
                            $order = $orders->firstWhere('external_id', $orderResponse['external_id']);
                            if ($order) {
                                // Kiểm tra và lấy internal_id an toàn - DTF batch response sử dụng 'order_id'
                                $internalId = $orderResponse['order_id'] ?? $orderResponse['id'] ?? $orderResponse['internal_id'] ?? null;

                                if (!$internalId) {


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
                    $apiConfig = $this->buildApiConfigForFactory('twofifteen', $orderData);

                    $response = Http::withHeaders($apiConfig['headers'])
                        ->withQueryParameters($apiConfig['parameters'])
                        ->post($apiConfig['config']['apiUrl'] . '/orders.php', $orderData);

                    $results[] = $this->processFactoryOrderResponse($order, $response, 'twofifteen');
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
            Log::error('Order process to factory error:', [
                'message' => $e->getMessage(),
                'order_ids' => $request->input('order_ids')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xử lý đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTrackingNumbers(Request $request)
    {
        try {
            // Chạy command để cập nhật tracking numbers
            $exitCode = Artisan::call('orders:update-tracking-numbers');

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tracking numbers updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update tracking numbers'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error updating tracking numbers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating tracking numbers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
