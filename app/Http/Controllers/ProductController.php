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

        $products = $query->paginate(10);

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

        // Lấy giá cho từng variant (nếu cần dùng ở JS)
        $variants = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'attributes' => $variant->attributes,
                'price_usd' => $variant->price_usd,
                'price_vnd' => $variant->price_vnd,
                'price_gbp' => $variant->price_gbp,
                'shipping_prices' => $variant->shippingPrices->map(function ($sp) {
                    return [
                        'method' => $sp->method,
                        'price_usd' => $sp->price_usd,
                        'price_vnd' => $sp->price_vnd,
                        'price_gbp' => $sp->price_gbp,
                    ];
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
            'variants'
        ));
    }
    public function import(Request $request)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        try {
            $currency = $request->input('currency', 'USD');
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $headers = array_shift($rows); // Bỏ dòng tiêu đề

            DB::beginTransaction();

            $currentProductId = null;
            $currentProduct = null;

            foreach ($rows as $rowIndex => $row) {
                try {
                    // Tạo sản phẩm mới nếu có tên
                    if (!empty($row[0])) {
                        $categoryId = $this->getCategoryId($row[1]);

                        $currentProduct = Product::create([
                            'name' => $row[0],
                            'category_id' => $categoryId,
                            'base_price' => (float)$row[2],
                            'currency' => $currency,
                            'template_link' => $row[3] ?? null,
                            'description' => $row[4] ?? null,
                            'status' => 1,
                            'slug' => Str::slug($row[0])
                        ]);

                        $currentProductId = $currentProduct->id;

                        if (!empty($row[5])) {
                            FulfillmentLocation::create([
                                'product_id' => $currentProductId,
                                'country_code' => $row[5]
                            ]);
                        }

                        for ($i = 6; $i <= 15; $i++) {
                            if (!empty($row[$i])) {
                                ProductImage::create([
                                    'product_id' => $currentProductId,
                                    'image_url' => $row[$i]
                                ]);
                            }
                        }
                    }

                    // Tạo variant
                    if ($currentProductId) {
                        $variant = ProductVariant::create([
                            'product_id' => $currentProductId,
                            'sku' => $row[16] ?? null,
                            'twofifteen_sku' => $row[17] ?? null,
                            'flashship_sku' => $row[18] ?? null,
                        ]);

                        $shippingMethods = [
                            19 => ShippingPrice::METHOD_TIKTOK_1ST,
                            20 => ShippingPrice::METHOD_TIKTOK_NEXT,
                            21 => ShippingPrice::METHOD_SELLER_1ST,
                            22 => ShippingPrice::METHOD_SELLER_NEXT
                        ];

                        foreach ($shippingMethods as $colIndex => $method) {
                            if (!empty($row[$colIndex])) {
                                ShippingPrice::create([
                                    'variant_id' => $variant->id,
                                    'method' => $method,
                                    'price' => (float)$row[$colIndex],
                                    'currency' => $currency
                                ]);
                            }
                        }

                        $attributeStartColumn = 23;
                        while (isset($row[$attributeStartColumn]) && isset($row[$attributeStartColumn + 1])) {
                            $attrName = $row[$attributeStartColumn];
                            $attrValue = $row[$attributeStartColumn + 1];

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
                    Log::error("Dòng $rowIndex bị lỗi: " . $e->getMessage());
                    // Không rollback toàn bộ, tiếp tục dòng sau
                    continue;
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Import dữ liệu thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xử lý file Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
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
