@extends('layouts.app')

@section('title', 'Product Detail')

@section('content')
<section class="products">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">{{ $product->name }}</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-gray-200">{{ $product->category->name }}</span>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">{{ $product->name }}</span>
            </div>
        </div>
    </div>
    <div
        class="max-w-7xl  mx-auto bg-white p-6 mb-10 shadow-md rounded-lg product-sans-regular">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Hình ảnh sản phẩm -->

            <div id="gallery" class="relative w-full">
                <!-- Main image -->
                <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
                    <img
                        id="main-image"
                        src="{{ asset($product->images->first()->image_url) }}"
                        class="absolute block w-full h-full object-contain -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                        alt="{{ $product->name }}" />
                </div>

                <!-- Thumbnails -->
                <div class="flex gap-4 mt-4 custom-scrollbar overflow-x-auto">
                    @foreach ($product->images as $image)
                    <img
                        onclick="showImage(this)"
                        src="{{ asset($image->image_url) }}"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition thumbnail-active"
                        alt="{{ $product->name }}" />
                    @endforeach

                </div>
            </div>
            <!-- Thông tin sản phẩm -->
            <div>
                <h2 class="text-gray-600 text-sm uppercase">{{ $product->category->name }}</h2>
                <h1 class="text-2xl font-bold text-gray-800 font-serif">
                    {{ $product->name }}
                </h1>
                <p class="text-gray-500 text-sm font-medium mt-1">
                    <span class="bg-gray-200 px-2 py-1 rounded">SKU: <span id="selected-sku">-</span></span>
                </p>
                <p style="color: #f7961d" class="text-2xl mt-2 roboto-bold">
                    Price: $<span id="total-price">{{ $product->base_price }}</span>
                </p>
                <p class="text-sm text-gray-500">Fulfillment Location:
                    @foreach($product->fulfillmentLocations as $fulfillmentLocation)
                    <span id="fulfillment-location">{{ $fulfillmentLocation->country_code }}</span>
                    @endforeach
                </p>
                

                <!-- Tùy chọn -->

                @foreach($groupedAttributes as $name => $values)
                <div class="mt-4">
                    <label
                        style="color: #005366"
                        class="block text-gray-700 font-semibold">{{ $name }}</label>
                    <select
                        name="{{ strtolower(str_replace(' ', '_', $name)) }}"
                        onchange="findMatchingVariant()"
                        class="attribute-select bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                   focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5
                   dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                   dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="">Choose {{ $name }}</option>
                        @foreach($values as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach

            


                <!-- Vận chuyển -->
                <div class="mt-6">
                    <label
                        style="color: #005366"
                        class="block text-gray-700 font-semibold">Shipping Method</label>
                    <select
                        id="shipping-method"
                        onchange="updateShippingPrice()"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected value="">Select Shipping Method</option>
                        <option value="tiktok_1st">Ship by Tiktok - 1 item</option>
                        <option value="tiktok_next">Ship by Tiktok - 2 items</option>
                        <option value="seller_1st">Ship by Seller - 1 item</option>
                        <option value="seller_next">Ship by Seller - 2 items</option>
                    </select>
                </div>



                <!-- Nút hành động -->

                <!-- Chia sẻ -->
            </div>
        </div>
    </div>
    <div
        class="max-w-5xl mx-auto p-4 border rounded-lg shadow-sm product-sans-regular mb-10">
        <!-- Tabs -->
        <div class="flex ">
            <button style="border-bottom: 2px solid #f7961d" class="px-4 py-2">
                Product Information
            </button>
           
        </div>

        <!-- Content -->
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-800">
                PRODUCT DESCRIPTION
            </h2>
            <p class="mt-2 text-gray-600">
               {{ $product->description }}
            </p>

         

            <!-- Download Button -->
            <div class="mt-6">
                <a href="{{ $product->template_link }}" target="_blank" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800">
                    <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-transparent group-hover:dark:bg-transparent">
                        Get Template
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>
<script>
    function showImage(thumbnail) {
        // Cập nhật ảnh chính
        const mainImage = document.getElementById("main-image");
        mainImage.src = thumbnail.src;

        // Xóa class active từ tất cả thumbnails
        const thumbnails = document.querySelectorAll(".custom-scrollbar img");
        thumbnails.forEach((thumb) => {
            thumb.classList.remove("thumbnail-active");
        });

        // Thêm class active cho thumbnail được chọn
        thumbnail.classList.add("thumbnail-active");
    }

    // Khởi tạo thumbnail đầu tiên là active
    document.addEventListener("DOMContentLoaded", function() {
        const firstThumbnail = document.querySelector(".custom-scrollbar img");
        if (firstThumbnail) {
            showImage(firstThumbnail);
        }
    });

    // Khởi tạo dữ liệu variants từ PHP
    var variants = @json($product->variants);
    var basePrice = {{ $product->base_price }};

    function findMatchingVariant() {
        var selectedValues = {};
        var selects = document.querySelectorAll('.attribute-select');
        
        selects.forEach(function(select) {
            var name = select.name.replace(/_/g, ' ').replace(/\b\w/g, function(l) { 
                return l.toUpperCase(); 
            });
            selectedValues[name] = select.value;
        });

        var allSelected = Object.values(selectedValues).every(function(value) {
            return value !== '';
        });

        var skuElement = document.getElementById('selected-sku');
        if (!allSelected) {
            skuElement.textContent = '-';
            return null;
        }

        var matchingVariant = variants.find(function(variant) {
            return variant.attributes.every(function(attr) {
                return selectedValues[attr.name] === attr.value;
            });
        });

        if (matchingVariant) {
            skuElement.textContent = matchingVariant.sku;
            return matchingVariant;
        } else {
            skuElement.textContent = 'Không có sản phẩm phù hợp';
            return null;
        }
    }

    function updateShippingPrice() {
        var currentVariant = findMatchingVariant();
        if (!currentVariant) {
            alert('Please select all product options');
            document.getElementById('shipping-method').value = '';
            return;
        }

        var shippingMethod = document.getElementById('shipping-method').value;
        
        if (!shippingMethod) {
            document.getElementById('total-price').textContent = basePrice.toFixed(2);
            return;
        }

        var shippingPrice = currentVariant.shipping_prices.find(function(price) {
            return price.method === shippingMethod;
        });

        if (shippingPrice) {
            var shipping = parseFloat(shippingPrice.price);
            var total =shipping;
            
            document.getElementById('total-price').textContent = total.toFixed(2);
        }
    }

    // Event listeners cho attribute selects
    document.querySelectorAll('.attribute-select').forEach(function(select) {
        select.addEventListener('change', function() {
            findMatchingVariant(); // Cập nhật SKU
            // Reset shipping
            document.getElementById('shipping-method').value = '';
            document.getElementById('shipping-price').textContent = '0.00';
            document.getElementById('total-price').textContent = basePrice.toFixed(2);
        });
    });

    // Khởi tạo ban đầu
    document.addEventListener('DOMContentLoaded', function() {
        findMatchingVariant();
    });
</script>
@endsection