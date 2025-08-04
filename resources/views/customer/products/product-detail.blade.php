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
                class="max-w-7xl mx-auto bg-white p-6 mb-10 shadow-md rounded-lg product-sans-regular">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Images -->
                    <div id="gallery" class="relative w-full">
                        <!-- Main image -->
                        <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
                            <img
                                id="main-image"
                                src="{{ $product->images->isNotEmpty() ? asset($product->images->first()->image_url) : asset('images/placeholder.jpg') }}"
                                class="absolute block w-full h-full object-contain -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                                alt="{{ $product->name }}" />
                        </div>
                        <!-- Thumbnails -->
                        <div class="flex gap-4 mt-4 custom-scrollbar overflow-x-auto">
                            @foreach ($product->images as $image)
                            <img
                                onclick="showImage(this)"
                                src="{{ asset($image->image_url) }}"
                                class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                                alt="Thumbnail of {{ $product->name }}" />
                            @endforeach
                        </div>
                    </div>
                    <!-- Product Information -->
                    <div>
                        <h2 class="text-gray-600 text-sm uppercase">{{ $product->category->name }}</h2>
                        <h1 class="text-2xl font-bold text-gray-800 font-serif">{{ $product->name }}</h1>
                        <p class="text-gray-500 text-sm font-medium mt-1">
                            <span class="bg-gray-200 px-2 py-1 rounded">SKU: <span id="selected-sku">-</span></span>
                        </p>
                        <p style="color: #f7961d" class="text-2xl mt-2 roboto-bold">
                            Price:
                            @if($product->currency === 'GBP')
                            <span>GBP £<span id="total-price-gbp">{{ number_format($product->base_price, 2) }}</span></span> |
                            <span>USD $<span id="total-price-usd">{{ number_format($product->base_price * 1.27, 2) }}</span></span> |
                            <span>VND ₫<span id="total-price-vnd">{{ number_format($product->base_price * 30894.31, 0) }}</span></span>
                            @elseif($product->currency === 'USD')
                            <span>USD $<span id="total-price-usd">{{ number_format($product->base_price, 2) }}</span></span> |
                            <span>GBP £<span id="total-price-gbp">{{ number_format($product->base_price / 1.27, 2) }}</span></span> |
                            <span>VND ₫<span id="total-price-vnd">{{ number_format($product->base_price * 24326.23, 0) }}</span></span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">Fulfillment Location:
                            @foreach($product->fulfillmentLocations as $location)
                            <span id="fulfillment-location">{{ $location->country_code }}</span>
                            @endforeach
                        </p>
                        <!-- Product Attributes -->
                        @foreach($groupedAttributes as $name => $values)
                        <div class="mt-4">
                            <label
                                style="color: #005366"
                                class="block text-gray-700 font-semibold">{{ $name }}</label>
                            <select
                                name="{{ strtolower(str_replace(' ', '-', $name)) }}"
                                onchange="findMatchingVariant()"
                                class="attribute-select bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Choose {{ $name }}</option>
                                @foreach($values as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                        <!-- Shipping Method -->
                        <div class="mt-6">
                            <label
                                style="color: #005366"
                                class="block text-gray-700 font-semibold">Shipping Method</label>
                            <select
                                id="shipping-method"
                                onchange="updateShippingPrice()"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Select Shipping Method</option>
                                <option value="tiktok_1st">Ship by TikTok - 1 item</option>
                                <option value="tiktok_next">Ship by TikTok - 2 items</option>
                                <option value="seller_1st">Ship by Seller - 1 item</option>
                                <option value="seller_next">Ship by Seller - 2 items</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="max-w-5xl mx-auto p-4 border rounded-lg shadow-sm product-sans-regular mb-10">
                <!-- Tabs -->
                <div class="flex">
                    <button style="padding: 1rem 1.5rem; border-bottom: 2px solid #d97706" class="active">Product Information</button>
                    <button style="padding: 1rem 1.5rem; border-bottom: 2px solid #d97706">Product Description</button>
                </div>
                <!-- Content -->
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800">PRODUCT DESCRIPTION</h2>
                    <p class="mt-2 text-gray-600">{{ $product->description }}</p>
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
            // Thumbnail gallery functionality
            function showImage(thumbnail) {
                const mainImage = document.getElementById('main-image');
                mainImage.src = thumbnail.src;
                document.querySelectorAll('.custom-scrollbar img').forEach(thumb => {
                    thumb.classList.remove('thumbnail-active');
                });
                thumbnail.classList.add('thumbnail-active');
            }

            // Initialize first thumbnail
            document.addEventListener('DOMContentLoaded', () => {
                const firstThumbnail = document.querySelector('.custom-scrollbar img');
                if (firstThumbnail) {
                    firstThumbnail.classList.add('thumbnail-active');
                    showImage(firstThumbnail);
                }
            });

            // Initialize variants and base price
            const variants = @json($product->variants);
            const basePrice = {
                @if($product->currency === 'GBP')
                    gbp: {{ $product->base_price }},
                    usd: {{ $product->base_price * 1.34 }},
                    vnd: {{ $product->base_price * 35078.0 }}
                @elseif($product->currency === 'USD')
                    usd: {{ $product->base_price }},
                    gbp: {{ $product->base_price / 1.34 }},
                    vnd: {{ $product->base_price * 26128.0 }}
                @endif
            };

            // Update price display
            function updatePrices(prices) {
                document.getElementById('total-price-gbp').textContent = prices.gbp.toFixed(2);
                document.getElementById('total-price-usd').textContent = prices.usd.toFixed(2);
                document.getElementById('total-price-vnd').textContent = Math.round(prices.vnd).toLocaleString('vi-VN');
            }

            // Find matching variant based on selected attributes
            function findMatchingVariant() {
                const selectedValues = {};
                const selects = document.querySelectorAll('.attribute-select');

                selects.forEach(select => {
                    const name = select.name.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    selectedValues[name] = select.value;
                });

                const allSelected = Object.values(selectedValues).every(value => value !== '');
                const skuElement = document.getElementById('selected-sku');

                if (!allSelected) {
                    skuElement.textContent = '-';
                    updatePrices(basePrice);
                    return null;
                }

                const matchingVariant = variants.find(variant => {
                    return variant.attributes.every(attr => selectedValues[attr.name] === attr.value);
                });

                if (matchingVariant) {
                    skuElement.textContent = matchingVariant.sku || '-';
                    return matchingVariant;
                } else {
                    skuElement.textContent = 'No matching variant found';
                    updatePrices(basePrice);
                    return null;
                }
            }

            // Update shipping price based on selected variant and shipping method
            function updateShippingPrice() {
                const currentVariant = findMatchingVariant();
                const shippingMethod = document.getElementById('shipping-method').value;

                if (!currentVariant) {
                    alert('Please select all product options');
                    document.getElementById('shipping-method').value = '';
                    updatePrices(basePrice);
                    return;
                }

                if (!shippingMethod) {
                    updatePrices(basePrice);
                    return;
                }

                const shippingPrice = currentVariant.shipping_prices.find(price => price.method === shippingMethod);

                if (shippingPrice) {
                    const total = {
                        @if($product->currency === 'GBP')
                            gbp:parseFloat(shippingPrice.price_gbp || 0),
                            usd: parseFloat(shippingPrice.price_usd || 0),
                            vnd:  parseFloat(shippingPrice.price_vnd || 0)
                        @elseif($product->currency === 'USD')
                            usd: parseFloat(shippingPrice.price_usd || 0),
                            gbp: parseFloat(shippingPrice.price_gbp || 0),
                            vnd:  parseFloat(shippingPrice.price_vnd || 0)
                        @endif
                    };
                    updatePrices(total);
                } else {
                    updatePrices(basePrice);
                }
            }

            // Initialize event listeners
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.attribute-select').forEach(select => {
                    select.addEventListener('change', () => {
                        findMatchingVariant();
                        document.getElementById('shipping-method').value = '';
                        updatePrices(basePrice);
                    });
                });
                findMatchingVariant();
                updatePrices(basePrice);
            });
        </script>
        @endsection