<?php

namespace App\Http\Controllers;


use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use App\Models\VariantAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use App\Models\FulfillmentLocation;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductController extends Controller
{

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.add-product', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'name' => 'required',
                'category_id' => 'required',
                'variants' => 'required|array',
                'variants.*.sku' => 'nullable|string',
                'variants.*.twofifteen_sku' => 'required|string',
                'variants.*.flashship_sku' => 'nullable|string',
                'variants.*.ship_tiktok_1' => 'required|numeric',
                'variants.*.ship_tiktok_2' => 'required|numeric',
                'variants.*.ship_seller_1' => 'required|numeric',
                'variants.*.ship_seller_2' => 'required|numeric',
                'variants.*.attributes' => 'required|array',
                'variants.*.attributes.*.name' => 'required|string',
                'variants.*.attributes.*.value' => 'required|string',
            ]);

            // Tạo sản phẩm cơ bản trước
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'status' => $request->status,
                'base_price' => $request->base_price,
                'template_link' => $request->template_link,
                'description' => $request->description,
            ]);

            // Xử lý fulfillment locations
            if ($request->fulfillment_locations) {
                foreach ($request->fulfillment_locations as $location) {
                    $product->fulfillmentLocations()->create([
                        'country_code' => $location['country_code']
                    ]);
                }
            }

            // Xử lý variants
            if ($request->variants) {
                foreach ($request->variants as $variantData) {
                    // Tạo variant
                    $variant = $product->variants()->create([
                        'sku' => $variantData['sku'],
                        'twofifteen_sku' => $variantData['twofifteen_sku'],
                        'flashship_sku' => $variantData['flashship_sku']
                    ]);

                    Log::info('Đã tạo variant:', ['variant_id' => $variant->id]);

                    // Tạo shipping prices
                    $shippingPrices = [
                        ['method' => ShippingPrice::METHOD_TIKTOK_1ST, 'price' => $variantData['ship_tiktok_1']],
                        ['method' => ShippingPrice::METHOD_TIKTOK_NEXT, 'price' => $variantData['ship_tiktok_2']],
                        ['method' => ShippingPrice::METHOD_SELLER_1ST, 'price' => $variantData['ship_seller_1']],
                        ['method' => ShippingPrice::METHOD_SELLER_NEXT, 'price' => $variantData['ship_seller_2']]
                    ];

                    foreach ($shippingPrices as $shipping) {
                        $variant->shippingPrices()->create([
                            'method' => $shipping['method'],
                            'price' => $shipping['price']
                        ]);
                        Log::info('Đã tạo shipping price:', [
                            'variant_id' => $variant->id,
                            'method' => $shipping['method'],
                            'price' => $shipping['price']
                        ]);
                    }

                    // Xử lý attributes
                    if (isset($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attribute) {
                            $variant->attributes()->create([
                                'name' => $attribute['name'],
                                'value' => $attribute['value'] !== 'undefined' ? $attribute['value'] : null
                            ]);
                        }
                    }
                }
            }

            // Xử lý images
            if ($request->hasFile('images')) {
                Log::info('Bắt đầu xử lý images');
                try {
                    foreach ($request->file('images') as $image) {
                        Log::info('Đang xử lý file:', [
                            'original_name' => $image->getClientOriginalName(),
                            'mime_type' => $image->getMimeType()
                        ]);

                        // Tạo tên file mới với timestamp
                        $imageName = time() . '_' . $image->getClientOriginalName();

                        // Di chuyển file vào thư mục public/images/products
                        $image->move(public_path('images/products'), $imageName);

                        // Đường dẫn để lưu vào database
                        $imagePath = 'images/products/' . $imageName;

                        Log::info('Đã lưu file tại: ' . $imagePath);

                        // Lưu thông tin vào database
                        $product->images()->create([
                            'image_url' => $imagePath
                        ]);
                    }
                    Log::info('Hoàn thành xử lý images');
                } catch (\Exception $e) {
                    Log::error('Lỗi khi xử lý images: ' . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::info('Không có images được upload');
            }

            return redirect()
                ->back()
                ->with('success', 'Sản phẩm đã được tạo thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo sản phẩm:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            // Gửi thông báo lỗi về trang trước đó
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Lỗi khi tạo sản phẩm: ' . $e->getMessage()]);
        }
    }

    public function adminIndex()
    {
        $products = Product::with(['category', 'images' => function ($query) {
            $query->orderBy('created_at', 'asc'); // Sắp xếp hình ảnh theo thời gian tạo
        }])->get(); // Lấy tất cả sản phẩm kèm theo hình ảnh

        // Lấy ảnh chính cho mỗi sản phẩm
        foreach ($products as $product) {
            $product->main_image = $product->images->first(); // Lấy hình ảnh đầu tiên làm ảnh chính
        }
        Log::info($products);
        return view('admin.products.product-list', compact('products')); // Trả về view với danh sách sản phẩm
    }
    public function index()
    {
        $products = Product::with(['images' => function ($query) {
            $query->orderBy('created_at', 'asc'); // Sắp xếp hình ảnh theo thời gian tạo
        }])->where('status', 1)->get(); // Lấy tất cả sản phẩm có status = 1

        // Lấy ảnh chính cho mỗi sản phẩm
        foreach ($products as $product) {
            $product->main_image = $product->images->first(); // Lấy hình ảnh đầu tiên làm ảnh chính
        }
        $categories = Category::all();

        return view('customer.home', compact('products', 'categories')); // Trả về view với danh sách sản phẩm
    }
    public function productList($slug = null)
    {
        $query = Product::with(['images' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }, 'fulfillmentLocations'])->where('status', 1);

        // Tìm kiếm theo tên sản phẩm
        if (request()->has('search')) {
            $searchTerm = request()->query('search');
            $query->where('name', 'LIKE', "%{$searchTerm}%");
        }

        // Lọc theo category (slug)
        if ($slug) {
            $category = Category::where('slug', $slug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Lọc theo country_code
        if (request()->has('country')) {
            $countryCode = request()->query('country');
            $query->whereHas('fulfillmentLocations', function ($q) use ($countryCode) {
                $q->where('country_code', $countryCode);
            });
        }

        $products = $query->paginate(16);

        foreach ($products as $product) {
            $mainImage = $product->images->first();
            if ($mainImage) {
                $product->main_image = $mainImage;
            }
            $product->fulfillment_locations = $product->fulfillmentLocations;
        }

        $categories = Category::all();
        $currentCategory = $slug ? Category::where('slug', $slug)->first() : null;

        return view('customer.products.products', compact('products', 'categories', 'currentCategory'));
    }



    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            Log::info('Đã xóa sản phẩm:', ['product_id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Sản phẩm đã được xóa thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa sản phẩm:', ['message' => $e->getMessage()]);
            return redirect()
                ->back()
                ->withErrors(['error' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage()]);
        }
    }

    public function show($slug)
    {
        $product = Product::with([
            'images',
            'variants.attributes',
            'variants.shippingPrices',
            'fulfillmentLocations'
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $groupedAttributes = $product->getGroupedAttributes()->toArray();
        Log::info($groupedAttributes);
        Log::info($product);

        // Lấy tỷ giá từ config
        $currencyRates = [
            'usd_to_vnd' => config('currency.usd_to_vnd'),
            'gbp_to_vnd' => config('currency.gbp_to_vnd'),
            'gbp_to_usd' => config('currency.gbp_to_usd'),
        ];

        // Lấy giá sản phẩm theo từng loại tiền
        $priceUSD = $product->price_usd;
        $priceVND = $product->price_vnd;
        $priceGBP = $product->price_gbp;

        // Mặc định tất cả user lấy giá Wood tier
        $currentUserTier = 'Wood';

        // Lấy giá cho từng variant (nếu cần dùng ở JS)
        $variants = $product->variants->map(function ($variant) use ($currentUserTier) {
            return [
                'id' => $variant->id,
                'attributes' => $variant->attributes,
                'price_usd' => $variant->price_usd,
                'price_vnd' => $variant->price_vnd,
                'price_gbp' => $variant->price_gbp,
                'current_user_tier' => $currentUserTier,
                'shipping_prices' => $variant->shippingPrices()
                    ->where('tier_name', $currentUserTier)
                    ->get()
                    ->map(function ($sp) {
                        return [
                            'method' => $sp->method,
                            'tier_name' => $sp->tier_name,
                            'price_usd' => $sp->price_usd,
                            'price_vnd' => $sp->price_vnd,
                            'price_gbp' => $sp->price_gbp,
                        ];
                    }),
                'all_tier_prices' => $variant->shippingPrices->groupBy('tier_name')->map(function ($prices, $tier) {
                    return $prices->map(function ($sp) {
                        return [
                            'method' => $sp->method,
                            'tier_name' => $sp->tier_name,
                            'price_usd' => $sp->price_usd,
                            'price_vnd' => $sp->price_vnd,
                            'price_gbp' => $sp->price_gbp,
                        ];
                    });
                }),
            ];
        });

        return view('customer.products.product-detail', compact(
            'product',
            'groupedAttributes',
            'currencyRates',
            'priceUSD',
            'priceVND',
            'priceGBP',
            'variants',
            'currentUserTier'
        ));
    }
    /**
     * Import sản phẩm từ file Excel
     * Cấu trúc cột Excel:
     * - A-O: Thông tin sản phẩm
     * - P-R: Thông tin variant (SKU)
     * - S: Trống/Dự trữ
     * - T-W: Wood tier prices (T=tiktok_1st, U=tiktok_next, V=seller_1st, W=seller_next)
     * - X-AA: Silver tier prices (X=tiktok_1st, Y=tiktok_next, Z=seller_1st, AA=seller_next)
     * - AB-AE: Gold tier prices (AB=tiktok_1st, AC=tiktok_next, AD=seller_1st, AE=seller_next)
     * - AF-AI: Diamond tier prices (AF=tiktok_1st, AG=tiktok_next, AH=seller_1st, AI=seller_next)
     * - AJ+: Variant attributes (name, value pairs)
     */
    public function import(Request $request)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 600); // Tăng lên 600 giây cho file lớn

        try {
            $currency = $request->input('currency', 'USD');
            $file = $request->file('excel_file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();

            // Lấy số dòng tối đa để kiểm tra
            $highestRow = $worksheet->getHighestRow();
            Log::info("Tổng số dòng trong file Excel: $highestRow");

            // Sử dụng iterator để xử lý từng dòng
            $rowIterator = $worksheet->getRowIterator(2); // Bỏ qua dòng tiêu đề
            $processedRows = 0;

            DB::beginTransaction();

            $currentProductId = null;
            $currentProduct = null;

            foreach ($rowIterator as $row) {
                $processedRows++;
                $rowIndex = $row->getRowIndex(); // Lấy số thứ tự dòng
                Log::info("Đang xử lý dòng: $rowIndex");

                try {
                    // Lấy giá trị ô của dòng hiện tại
                    $cells = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $cells[] = $cell->getValue();
                    }

                    // Bỏ qua nếu dòng hoàn toàn trống
                    if (empty(array_filter($cells))) {
                        Log::warning("Dòng $rowIndex trống, bỏ qua.");
                        continue;
                    }

                    // Tạo sản phẩm mới nếu có tên
                    if (!empty($cells[0])) {
                        $categoryId = $this->getCategoryId($cells[1] ?? null);

                        $currentProduct = Product::create([
                            'name' => $cells[0],
                            'category_id' => $categoryId,
                            'base_price' => (float)($cells[2] ?? 0),
                            'currency' => $currency,
                            'template_link' => $cells[3] ?? null,
                            'description' => $cells[4] ?? null,
                            'status' => 1,
                            'slug' => Str::slug($cells[0])
                        ]);

                        $currentProductId = $currentProduct->id;
                        Log::info("Tạo sản phẩm ID: $currentProductId cho dòng $rowIndex");

                        if (!empty($cells[5])) {
                            FulfillmentLocation::create([
                                'product_id' => $currentProductId,
                                'country_code' => $cells[5]
                            ]);
                        }

                        for ($i = 6; $i <= 15; $i++) {
                            if (!empty($cells[$i])) {
                                ProductImage::create([
                                    'product_id' => $currentProductId,
                                    'image_url' => $cells[$i]
                                ]);
                            }
                        }
                    }

                    // Tạo variant
                    if ($currentProductId) {
                        $variant = ProductVariant::create([
                            'product_id' => $currentProductId,
                            'sku' => $cells[16] ?? null,
                            'twofifteen_sku' => $cells[17] ?? null,
                            'flashship_sku' => $cells[18] ?? null,
                        ]);

                        // Cấu trúc cột Excel:
                        // T-W: Wood tier (T=tiktok_1st, U=tiktok_next, V=seller_1st, W=seller_next)
                        // X-AA: Silver tier (X=tiktok_1st, Y=tiktok_next, Z=seller_1st, AA=seller_next)
                        // AB-AE: Gold tier (AB=tiktok_1st, AC=tiktok_next, AD=seller_1st, AE=seller_next)
                        // AF-AI: Diamond tier (AF=tiktok_1st, AG=tiktok_next, AH=seller_1st, AI=seller_next)
                        $shippingMethods = [
                            ShippingPrice::METHOD_TIKTOK_1ST,
                            ShippingPrice::METHOD_TIKTOK_NEXT,
                            ShippingPrice::METHOD_SELLER_1ST,
                            ShippingPrice::METHOD_SELLER_NEXT
                        ];

                        $tierConfigs = [
                            'Wood' => ['start' => 19],      // Cột T-W (19-22 trong array)
                            'Silver' => ['start' => 23],   // Cột X-AA (23-26 trong array)
                            'Gold' => ['start' => 27],     // Cột AB-AE (27-30 trong array)
                            'Diamond' => ['start' => 31]   // Cột AF-AI (31-34 trong array)
                        ];

                        foreach ($tierConfigs as $tierName => $config) {
                            foreach ($shippingMethods as $methodIndex => $method) {
                                $colIndex = $config['start'] + $methodIndex;
                                if (!empty($cells[$colIndex])) {
                                    ShippingPrice::create([
                                        'variant_id' => $variant->id,
                                        'method' => $method,
                                        'tier_name' => $tierName,
                                        'price' => (float)($cells[$colIndex] ?? 0),
                                        'currency' => $currency
                                    ]);
                                }
                            }
                        }

                        $attributeStartColumn = 35; // Cột AJ trở đi là Variant Attributes
                        while (isset($cells[$attributeStartColumn]) && isset($cells[$attributeStartColumn + 1])) {
                            $attrName = $cells[$attributeStartColumn];
                            $attrValue = $cells[$attributeStartColumn + 1];

                            if (!empty($attrName) && !empty($attrValue)) {
                                VariantAttribute::create([
                                    'variant_id' => $variant->id,
                                    'name' => $attrName,
                                    'value' => $attrValue
                                ]);
                            }

                            $attributeStartColumn += 2;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Lỗi khi xử lý dòng $rowIndex: " . $e->getMessage());
                    continue; // Tiếp tục dòng tiếp theo
                }
            }

            DB::commit();
            Log::info("Tổng số dòng đã xử lý: $processedRows");
            return redirect()->back()->with('success', "Nhập dữ liệu thành công. Đã xử lý $processedRows/$highestRow dòng.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xử lý file Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi nhập dữ liệu: ' . $e->getMessage());
        }
    }

    private function getCategoryId($categoryInput)
    {
        if (is_numeric($categoryInput)) {
            return (int)$categoryInput;
        }

        $category = Category::where('name', trim($categoryInput))->first();
        if (!$category) {
            throw new \Exception('Không tìm thấy danh mục: ' . $categoryInput);
        }
        return $category->id;
    }
}
