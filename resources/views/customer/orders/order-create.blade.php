@extends('layouts.customer')

@section('title', 'Create Manual Order')

@push('styles')
<style>
    /* Product Card Hover Effects */
    .product-card {
        transition: all 0.2s ease-in-out;
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .product-card.selected {
        border-color: #10b981 !important;
        background-color: #f0fdf4;
    }

    .dark .product-card.selected {
        background-color: #064e3b;
    }

    /* Filter Pills */
    .filter-pill {
        transition: all 0.2s ease;
    }

    .filter-pill:hover {
        transform: translateY(-1px);
    }

    /* Modal Animation */
    .modal {
        backdrop-filter: blur(4px);
        z-index: 9999;
        /* Ensure modal is above sidebar */
    }

    /* Scrollbar Styling for Modal Content */
    .modal-content::-webkit-scrollbar {
        width: 6px;
    }

    .modal-content::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .modal-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .modal-content::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .dark .modal-content::-webkit-scrollbar-track {
        background: rgb(62, 66, 72);
    }

    .dark .modal-content::-webkit-scrollbar-thumb {
        background: #6b7280;
    }

    .dark .modal-content::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>
@endpush

@section('content-customer')
<div class="p-4 mx-auto max-w-7xl md:p-6">
    <!-- Breadcrumb -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Create Manual Order</h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('customer.dashboard') }}">
                        Home
                        <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </li>
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('customer.order-customer') }}">
                        Orders
                        <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </li>
                <li class="text-sm text-gray-800 dark:text-white/90">Create Manual Order</li>
            </ol>
        </nav>
    </div>

    <!-- Product Selection Modal -->
    <div id="productSelectionView" class="hidden fixed inset-0 bg-gray-500 bg-opacity-60 modal flex items-center justify-center z-9999">

        <div class="bg-white dark:bg-gray-800 w-full max-w-4xl rounded-lg shadow-lg max-h-[90vh] flex flex-col">


            <!-- Content -->
            <div class="p-6 overflow-y-auto modal-content">
                <!-- Breadcrumb -->
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <button type="button" id="breadcrumbProducts" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Products
                            </button>
                        </li>
                        <li id="breadcrumbVariants" class="hidden">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400" id="selectedProductName">Variants</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Search and Filter -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Search Box -->
                        <div class="flex-1 relative">
                            <input type="text" id="productSearch" placeholder="Search products..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Category Filter (Only for products) -->
                    <div class="mt-4" id="categoryFilterSection">
                        <div class="flex flex-wrap gap-2" id="categoryFilters">
                            <button type="button" class="filter-pill px-4 py-2 text-sm bg-blue-500 text-white rounded-full transition-colors hover:bg-blue-600" data-category="all">
                                All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="productsGrid">
                    <!-- Products will be populated here -->
                </div>

                <!-- Variants Grid -->
                <div class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="variantsGrid">
                    <!-- Variants will be populated here -->
                </div>

                <!-- No Results -->
                <div id="noResults" class="hidden text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.239 0-4.24-.677-5.937-1.833C4.416 12.089 3 10.072 3 8a8 8 0 1116 0c0 2.072-1.416 4.089-3.063 5.167z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No results found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try searching with different keywords</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span id="stepIndicator">Step 1/2: Select Product</span>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="cancelSelection" class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </button>
                        <button type="button" id="confirmSelection" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
                            Select Product
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('customer.order-store') }}" class="space-y-6">
        @csrf

        <!-- Order Information -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order Number <span class="text-red-500">*</span></label>
                    <input type="text" name="order_number" id="order_number" value="{{ old('order_number') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="store_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store Name</label>
                    <input type="text" name="store_name" id="store_name" value="{{ old('store_name') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sales Channel</label>
                    <select name="channel" id="channel" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="customer-manual" {{ old('channel') == 'customer-manual' ? 'selected' : '' }}>Select Sales Channel</option>
                        <option value="manual" {{ old('channel') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="web" {{ old('channel') == 'web' ? 'selected' : '' }}>Website</option>
                        <option value="tiktok" {{ old('channel') == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                        <option value="shopee" {{ old('channel') == 'shopee' ? 'selected' : '' }}>Shopee</option>
                    </select>
                </div>

                <div>
                    <label for="shipping_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Shipping Method</label>
                    <select name="shipping_method" id="shipping_method" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">-- Select Method --</option>
                        <option value="tiktok_label" {{ old('shipping_method') == 'tiktok_label' ? 'selected' : '' }}>TikTok Label</option>
                        <option value="seller_shipping" {{ old('shipping_method') == 'seller_shipping' ? 'selected' : '' }}>Seller Shipping</option>
                    </select>
                </div>

                <div>
                    <label for="warehouse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Warehouse *</label>
                    <select name="warehouse" id="warehouse" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">-- Select Warehouse --</option>
                        <option value="UK" {{ old('warehouse') == 'UK' ? 'selected' : '' }}>UK</option>
                        <option value="US" {{ old('warehouse') == 'US' ? 'selected' : '' }}>US</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="order_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order Note (Disabled)</label>
                <textarea name="order_note" id="order_note" rows="3" disabled class="w-full p-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Only allowed when TikTok Label is selected">{{ old('order_note') }}</textarea>
            </div>
        </div>

        <!-- Shipping Information -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Shipping Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recipient Name *</label>
                    <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recipient Email *</label>
                    <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                    <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country *</label>
                    <select name="country" id="country" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">-- Select Country --</option>
                        <option value="VN" {{ old('country') == 'VN' ? 'selected' : '' }}>Vietnam</option>
                        <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>United States</option>
                        <option value="UK" {{ old('country') == 'UK' ? 'selected' : '' }}>United Kingdom</option>
                        <option value="CA" {{ old('country') == 'CA' ? 'selected' : '' }}>Canada</option>
                        <option value="AU" {{ old('country') == 'AU' ? 'selected' : '' }}>Australia</option>
                    </select>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address *</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="address_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address 2</label>
                    <input type="text" name="address_2" id="address_2" value="{{ old('address_2') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City *</label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State/Province</label>
                    <input type="text" name="state" id="state" value="{{ old('state') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="postcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postal Code *</label>
                    <input type="text" name="postcode" id="postcode" value="{{ old('postcode') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Products</h3>
                <button type="button" id="add-product" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                    + Add Product
                </button>
            </div>

            <div id="products-container">
                <!-- Products will be added here dynamically -->
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('customer.order-customer') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                Create Order
            </button>
        </div>
    </form>
</div>

@if(session('success_redirect'))
<script>
    sessionStorage.removeItem('orderCreateFormData');
</script>
@endif

<script>
    let productIndex = 0;
    let products = [];
    let currentProductIndex = null;
    let selectedVariant = null;
    let filteredProducts = [];
    let currentStep = 'products'; // 'products' or 'variants'
    let selectedProduct = null;
    let currentVariants = [];

    // Load products on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadProducts();
        initializeSelectionModal();
    });

    // Load products from API
    async function loadProducts() {
        try {
            const response = await fetch('{{ route("customer.api.products-with-variants") }}');
            const data = await response.json();

            // Transform data to include image_url
            products = data.map(item => ({
                id: item.id,
                name: item.name,
                category: item.category,
                description: item.description,
                base_price: item.base_price,
                currency: item.currency,
                image_url: item.image_url, // Include image URL
                image_count: item.image_count,
                variants: item.variants
            }));

            filteredProducts = [...products];
            populateCategories();
            console.log("Products loaded:", products);

            // After products are loaded, restore form data
            restoreFormData();
        } catch (error) {
            console.error('Error loading products:', error);
            // If products fail to load, still try to restore form data
            restoreFormData();
        }
    }

    // Save form data to sessionStorage
    function saveFormData() {
        const formData = {
            order_number: document.getElementById('order_number')?.value || '',
            store_name: document.getElementById('store_name')?.value || '',
            channel: document.getElementById('channel')?.value || '',
            customer_name: document.getElementById('customer_name')?.value || '',
            customer_email: document.getElementById('customer_email')?.value || '',
            customer_phone: document.getElementById('customer_phone')?.value || '',
            address: document.getElementById('address')?.value || '',
            address_2: document.getElementById('address_2')?.value || '',
            city: document.getElementById('city')?.value || '',
            state: document.getElementById('state')?.value || '',
            postcode: document.getElementById('postcode')?.value || '',
            country: document.getElementById('country')?.value || '',
            shipping_method: document.getElementById('shipping_method')?.value || '',
            order_note: document.getElementById('order_note')?.value || '',
            warehouse: document.getElementById('warehouse')?.value || '',
            products: []
        };

        // Save products data
        const productItems = document.querySelectorAll('.product-item');
        productItems.forEach((item, index) => {
            const productData = {
                variant_id: item.querySelector('.variant-id-input')?.value || '',
                quantity: item.querySelector('input[name*="[quantity]"]')?.value || '1',
                title: item.querySelector('input[name*="[title]"]')?.value || '',
                designs: [],
                mockups: []
            };

            // Save designs
            const designs = item.querySelectorAll('.design-item');
            designs.forEach(design => {
                productData.designs.push({
                    file_url: design.querySelector('input[type="url"]')?.value || '',
                    print_space: design.querySelector('select')?.value || ''
                });
            });

            // Save mockups
            const mockups = item.querySelectorAll('.mockup-item');
            mockups.forEach(mockup => {
                productData.mockups.push({
                    file_url: mockup.querySelector('input[type="url"]')?.value || '',
                    print_space: mockup.querySelector('select')?.value || ''
                });
            });

            formData.products.push(productData);
        });

        sessionStorage.setItem('orderCreateFormData', JSON.stringify(formData));
    }

    // Restore form data from sessionStorage
    function restoreFormData() {
        const savedData = sessionStorage.getItem('orderCreateFormData');
        if (!savedData) {
            addProduct(); // Add first product by default if no saved data
            return;
        }

        try {
            const formData = JSON.parse(savedData);

            // Restore basic form fields
            if (formData.order_number) document.getElementById('order_number').value = formData.order_number;
            if (formData.store_name) document.getElementById('store_name').value = formData.store_name;
            if (formData.channel) document.getElementById('channel').value = formData.channel;
            if (formData.customer_name) document.getElementById('customer_name').value = formData.customer_name;
            if (formData.customer_email) document.getElementById('customer_email').value = formData.customer_email;
            if (formData.customer_phone) document.getElementById('customer_phone').value = formData.customer_phone;
            if (formData.address) document.getElementById('address').value = formData.address;
            if (formData.address_2) document.getElementById('address_2').value = formData.address_2;
            if (formData.city) document.getElementById('city').value = formData.city;
            if (formData.state) document.getElementById('state').value = formData.state;
            if (formData.postcode) document.getElementById('postcode').value = formData.postcode;
            if (formData.country) document.getElementById('country').value = formData.country;
            if (formData.shipping_method) document.getElementById('shipping_method').value = formData.shipping_method;
            if (formData.order_note) document.getElementById('order_note').value = formData.order_note;
            if (formData.warehouse) document.getElementById('warehouse').value = formData.warehouse;

            // Trigger shipping method change to update order note field
            handleShippingMethodChange();

            // Restore products
            if (formData.products && formData.products.length > 0) {
                formData.products.forEach((product, index) => {
                    addProduct();
                    const productItem = document.querySelector(`[data-index="${index}"]`);

                    if (productItem) {
                        // Set variant
                        if (product.variant_id) {
                            productItem.querySelector('.variant-id-input').value = product.variant_id;

                            // Find and set the selected variant text
                            const variantInfo = findVariantById(product.variant_id);
                            if (variantInfo) {
                                const productName = variantInfo.productName || 'Product';
                                const variantText = variantInfo.variantText || 'Variant';
                                const sku = variantInfo.sku || '';
                                productItem.querySelector('.selected-product-text').textContent = `${productName} - ${variantText} (${sku})`;
                                productItem.querySelector('.selected-product-text').classList.remove('text-gray-500', 'dark:text-gray-400');
                                productItem.querySelector('.selected-product-text').classList.add('text-gray-900', 'dark:text-white');
                            }
                        }

                        // Set quantity and title
                        if (product.quantity) productItem.querySelector('input[name*="[quantity]"]').value = product.quantity;
                        if (product.title) productItem.querySelector('input[name*="[title]"]').value = product.title;

                        // Restore designs
                        if (product.designs && product.designs.length > 0) {
                            // Remove default design first
                            const designsContainer = productItem.querySelector('.designs-container');
                            designsContainer.innerHTML = '';

                            product.designs.forEach((design, designIndex) => {
                                // Create design HTML manually instead of using addDesign
                                const designHtml = `
                                <div class="design-item grid grid-cols-1 md:grid-cols-2 gap-2 mb-2 p-2 border border-gray-100 dark:border-gray-600 rounded">
                                    <div>
                                        <input type="url" name="products[${index}][designs][${designIndex}][file_url]" placeholder="Design file URL" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="${design.file_url || ''}" required>
                                    </div>
                                    <div class="flex gap-2">
                                        <select name="products[${index}][designs][${designIndex}][print_space]" class="flex-1 p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                            <option value="">-- Print Space --</option>
                                            <option value="Front" ${design.print_space === 'Front' ? 'selected' : ''}>Front</option>
                                            <option value="Back" ${design.print_space === 'Back' ? 'selected' : ''}>Back</option>
                                            <option value="Left Sleeve" ${design.print_space === 'Left Sleeve' ? 'selected' : ''}>Left Sleeve</option>
                                            <option value="Right Sleeve" ${design.print_space === 'Right Sleeve' ? 'selected' : ''}>Right Sleeve</option>
                                            <option value="Hem" ${design.print_space === 'Hem' ? 'selected' : ''}>Hem</option>
                                        </select>
                                        <button type="button" class="remove-design bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                                `;
                                designsContainer.insertAdjacentHTML('beforeend', designHtml);
                            });
                        }

                        // Restore mockups
                        if (product.mockups && product.mockups.length > 0) {
                            // Remove default mockup first
                            const mockupsContainer = productItem.querySelector('.mockups-container');
                            mockupsContainer.innerHTML = '';

                            product.mockups.forEach((mockup, mockupIndex) => {
                                // Create mockup HTML manually instead of using addMockup
                                const mockupHtml = `
                                <div class="mockup-item grid grid-cols-1 md:grid-cols-2 gap-2 mb-2 p-2 border border-gray-100 dark:border-gray-600 rounded">
                                    <div>
                                        <input type="url" name="products[${index}][mockups][${mockupIndex}][file_url]" placeholder="Mockup file URL" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="${mockup.file_url || ''}" required>
                                    </div>
                                    <div class="flex gap-2">
                                        <select name="products[${index}][mockups][${mockupIndex}][print_space]" class="flex-1 p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                            <option value="">-- Print Space --</option>
                                            <option value="Front" ${mockup.print_space === 'Front' ? 'selected' : ''}>Front</option>
                                            <option value="Back" ${mockup.print_space === 'Back' ? 'selected' : ''}>Back</option>
                                            <option value="Left Sleeve" ${mockup.print_space === 'Left Sleeve' ? 'selected' : ''}>Left Sleeve</option>
                                            <option value="Right Sleeve" ${mockup.print_space === 'Right Sleeve' ? 'selected' : ''}>Right Sleeve</option>
                                            <option value="Hem" ${mockup.print_space === 'Hem' ? 'selected' : ''}>Hem</option>
                                        </select>
                                        <button type="button" class="remove-mockup bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                                `;
                                mockupsContainer.insertAdjacentHTML('beforeend', mockupHtml);
                            });
                        }
                    }
                });
            } else {
                addProduct(); // Add first product by default
            }

            // Clear saved data after restoration
            sessionStorage.removeItem('orderCreateFormData');

        } catch (error) {
            console.error('Error restoring form data:', error);
            addProduct(); // Add first product by default if restoration fails
        }
    }

    // Helper function to find variant by ID
    function findVariantById(variantId) {
        for (const product of products) {
            const variant = product.variants.find(v => v.id == variantId);
            if (variant) {
                return {
                    ...variant,
                    productName: product.name,
                    variantText: variant.attribute_text,
                    sku: variant.sku
                };
            }
        }
        return null;
    }

    // Initialize selection modal functionality
    function initializeSelectionModal() {
        const modal = document.getElementById('productSelectionView');
        const searchInput = document.getElementById('productSearch');
        const confirmBtn = document.getElementById('confirmSelection');

        // Open modal when product picker button is clicked
        document.addEventListener('click', function(e) {
            if (e.target.closest('.product-picker-btn')) {
                const btn = e.target.closest('.product-picker-btn');
                currentProductIndex = btn.dataset.productIndex;
                openSelectionModal();
            }
        });

        // Close modal
        document.addEventListener('click', function(e) {
            if (e.target.id === 'closeProductSelection' || e.target.id === 'cancelSelection') {
                closeSelectionModal();
            }
        });

        // Search functionality
        searchInput.addEventListener('input', function() {
            if (currentStep === 'products') {
                filterProducts();
            } else if (currentStep === 'variants') {
                filterProducts(); // This will call populateVariantsGrid with filtered variants
            }
        });

        // Category filter
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('filter-pill')) {
                // Remove active class from all pills
                document.querySelectorAll('.filter-pill').forEach(pill => {
                    pill.classList.remove('bg-blue-500', 'text-white');
                    pill.classList.add('bg-gray-200', 'text-gray-700', 'dark:bg-gray-600', 'dark:text-gray-300');
                });

                // Add active class to clicked pill
                e.target.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-600', 'dark:text-gray-300');
                e.target.classList.add('bg-blue-500', 'text-white');

                filterProducts();
            }
        });

        // Product/Variant selection
        document.addEventListener('click', function(e) {
            if (e.target.closest('.product-card') && !modal.classList.contains('hidden')) {
                const card = e.target.closest('.product-card');

                if (currentStep === 'products') {
                    // Product selection - go to variants step
                    const productId = card.dataset.productId;
                    selectedProduct = products.find(p => p.id == productId);
                    showVariantsStep();
                } else if (currentStep === 'variants') {
                    // Variant selection
                    // Remove selected class from all cards
                    document.querySelectorAll('.product-card').forEach(c => {
                        c.classList.remove('selected', 'border-green-500', 'bg-green-50', 'dark:bg-green-900');
                    });

                    // Add selected class to clicked card
                    card.classList.add('selected', 'border-green-500', 'bg-green-50', 'dark:bg-green-900');

                    // Store selected variant
                    selectedVariant = {
                        id: card.dataset.variantId,
                        productName: selectedProduct.name || 'Product without name',
                        variantText: card.dataset.variantText,
                        sku: card.dataset.sku
                    };

                    // Enable confirm button
                    confirmBtn.disabled = false;
                    confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        });

        // Breadcrumb navigation
        document.getElementById('breadcrumbProducts').addEventListener('click', function() {
            if (currentStep === 'variants') {
                showProductsStep();
            }
        });

        // Confirm selection
        confirmBtn.addEventListener('click', function() {
            if (selectedVariant && currentProductIndex !== null) {
                const hiddenInput = document.querySelector(`[data-index="${currentProductIndex}"] .variant-id-input`);
                const displayText = document.querySelector(`[data-index="${currentProductIndex}"] .selected-product-text`);

                hiddenInput.value = selectedVariant.id;
                displayText.textContent = `${selectedVariant.productName} - ${selectedVariant.variantText} (${selectedVariant.sku})`;
                displayText.classList.remove('text-gray-500', 'dark:text-gray-400');
                displayText.classList.add('text-gray-900', 'dark:text-white');

                closeSelectionModal();
                saveFormData(); // Save after variant selection
            }
        });
    }

    function openSelectionModal() {
        const modal = document.getElementById('productSelectionView');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Reset modal state
        selectedVariant = null;
        selectedProduct = null;
        currentStep = 'products';
        document.getElementById('confirmSelection').disabled = true;
        document.getElementById('confirmSelection').classList.add('opacity-50', 'cursor-not-allowed');
        document.getElementById('productSearch').value = '';

        // Reset category filter to "all"
        document.querySelectorAll('.filter-pill').forEach(pill => {
            pill.classList.remove('bg-blue-500', 'text-white');
            pill.classList.add('bg-gray-200', 'text-gray-700', 'dark:bg-gray-600', 'dark:text-gray-300');
        });
        const allButton = document.querySelector('[data-category="all"]');
        if (allButton) {
            allButton.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-600', 'dark:text-gray-300');
            allButton.classList.add('bg-blue-500', 'text-white');
        }

        // Show products step
        showProductsStep();
    }

    function showProductsStep() {
        currentStep = 'products';

        // Update breadcrumb
        document.getElementById('breadcrumbProducts').classList.remove('text-gray-500', 'dark:text-gray-400');
        document.getElementById('breadcrumbProducts').classList.add('text-blue-600', 'dark:text-blue-400');
        document.getElementById('breadcrumbVariants').classList.add('hidden');

        // Update search placeholder
        document.getElementById('productSearch').placeholder = 'Search products...';

        // Show/hide sections
        document.getElementById('categoryFilterSection').classList.remove('hidden');
        document.getElementById('productsGrid').classList.remove('hidden');
        document.getElementById('variantsGrid').classList.add('hidden');

        // Update confirm button
        document.getElementById('confirmSelection').textContent = 'Select Product';
        document.getElementById('confirmSelection').disabled = true;
        document.getElementById('confirmSelection').classList.add('opacity-50', 'cursor-not-allowed');

        // Update step indicator
        document.getElementById('stepIndicator').textContent = 'Step 1/2: Select Product';

        // Filter and populate products
        filterProducts();
    }

    function showVariantsStep() {
        currentStep = 'variants';

        // Update breadcrumb
        document.getElementById('breadcrumbProducts').classList.remove('text-blue-600', 'dark:text-blue-400');
        document.getElementById('breadcrumbProducts').classList.add('text-gray-500', 'dark:text-gray-400');
        document.getElementById('breadcrumbVariants').classList.remove('hidden');
        document.getElementById('selectedProductName').textContent = selectedProduct.name;

        // Update search placeholder
        document.getElementById('productSearch').placeholder = 'Search variant...';
        document.getElementById('productSearch').value = '';

        // Show/hide sections
        document.getElementById('categoryFilterSection').classList.add('hidden');
        document.getElementById('productsGrid').classList.add('hidden');
        document.getElementById('variantsGrid').classList.remove('hidden');

        // Update confirm button
        document.getElementById('confirmSelection').textContent = 'Select Variant';
        document.getElementById('confirmSelection').disabled = true;
        document.getElementById('confirmSelection').classList.add('opacity-50', 'cursor-not-allowed');

        // Update step indicator
        document.getElementById('stepIndicator').textContent = 'Step 2/2: Select Variant';

        // Populate variants
        currentVariants = selectedProduct.variants || [];
        populateVariantsGrid();
    }

    function closeSelectionModal() {
        const modal = document.getElementById('productSelectionView');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        // Reset all states
        currentProductIndex = null;
        selectedVariant = null;
        selectedProduct = null;
        currentStep = 'products';
        currentVariants = [];
    }

    function populateCategories() {
        const container = document.getElementById('categoryFilters');
        const categories = [...new Set(products.map(p => p.category))];

        // Keep "All" button and add category buttons
        const allButton = container.querySelector('[data-category="all"]');
        container.innerHTML = '';
        container.appendChild(allButton);

        categories.forEach(category => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'filter-pill px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-full transition-colors hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500';
            button.dataset.category = category;
            button.textContent = category;
            container.appendChild(button);
        });
    }

    function filterProducts() {
        const searchTerm = document.getElementById('productSearch').value.toLowerCase();

        if (currentStep === 'products') {
            const selectedCategory = document.querySelector('.filter-pill.bg-blue-500').dataset.category;

            filteredProducts = products.filter(product => {
                // Category filter
                const categoryMatch = selectedCategory === 'all' || product.category === selectedCategory;

                // Search filter
                const searchMatch = searchTerm === '' ||
                    product.name.toLowerCase().includes(searchTerm) ||
                    product.description?.toLowerCase().includes(searchTerm);

                return categoryMatch && searchMatch;
            });

            populateProductsGrid();
        } else if (currentStep === 'variants') {
            const filteredVariants = currentVariants.filter(variant => {
                return searchTerm === '' ||
                    variant.sku.toLowerCase().includes(searchTerm) ||
                    variant.twofifteen_sku?.toLowerCase().includes(searchTerm) ||
                    variant.flashship_sku?.toLowerCase().includes(searchTerm) ||
                    variant.attribute_text.toLowerCase().includes(searchTerm);
            });

            populateVariantsGrid(filteredVariants);
        }
    }

    function populateProductsGrid() {
        const container = document.getElementById('productsGrid');
        const noResults = document.getElementById('noResults');

        container.innerHTML = '';

        if (filteredProducts.length === 0) {
            noResults.classList.remove('hidden');
            return;
        }

        noResults.classList.add('hidden');

        filteredProducts.forEach(product => {
            const card = document.createElement('div');
            card.className = 'product-card p-6 bg-white dark:bg-gray-700 rounded-lg border-2 border-transparent hover:border-blue-500 hover:shadow-lg transition-all cursor-pointer';
            card.dataset.productId = product.id;

            // Debug: kiểm tra dữ liệu sản phẩm
            console.log("Product data:", product);

            // Lấy hình ảnh
            const imageUrl = product.image_url;
            const hasImage = imageUrl && imageUrl !== 'null' && imageUrl !== '' && imageUrl !== null;
            const productName = product.name || 'Product without name';
            const productCategory = product.category || 'Uncategorized';
            const productCurrency = product.currency || 'GBP';
            const productBasePrice = product.base_price || '0';
            const variantCount = product.variants ? product.variants.length : 0;
            const imageCount = product.image_count || 0;

            card.innerHTML = `
                <div class="space-y-4">
                    <div class="w-full h-48 bg-gray-100 dark:bg-gray-600 rounded-lg overflow-hidden flex items-center justify-center">
                        ${hasImage ? `
                            <img src="${imageUrl}" alt="${productName}" class="w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full flex items-center justify-center" style="display:none;">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        ` : `
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        `}
                    </div>
                    <div class="flex justify-between items-start">
                        <h4 class="font-semibold text-gray-900 dark:text-white">${productName}</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            ${productCategory}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                            ${variantCount} variant${variantCount > 1 ? 's' : ''}
                        </span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                            ${productCurrency} ${productBasePrice}
                        </span>
                    </div>
                    ${imageCount > 0 ? `
                        <div class="flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            ${imageCount} images
                        </div>
                    ` : ''}
                    <div class="flex items-center justify-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-sm text-blue-500 font-medium">Click to view variants</span>
                    </div>
                </div>
            `;

            container.appendChild(card);
        });
    }

    function populateVariantsGrid(variants = currentVariants) {
        const container = document.getElementById('variantsGrid');
        const noResults = document.getElementById('noResults');

        container.innerHTML = '';

        if (variants.length === 0) {
            noResults.classList.remove('hidden');
            return;
        }

        noResults.classList.add('hidden');

        variants.forEach(variant => {
            const card = document.createElement('div');
            card.className = 'product-card p-4 bg-white dark:bg-gray-700 rounded-lg border-2 border-transparent hover:border-blue-500 hover:shadow-lg transition-all cursor-pointer';
            card.dataset.variantId = variant.id || '';
            card.dataset.variantText = variant.attribute_text || 'Variant without name';
            card.dataset.sku = variant.sku || '';

            // Handle undefined values
            const variantText = variant.attribute_text || 'Variant without name';
            const mainSku = variant.sku || 'N/A';
            const twofifteenSku = variant.twofifteen_sku || '';
            const flashshipSku = variant.flashship_sku || '';
            // Variants sử dụng hình ảnh của product cha
            const imageUrl = selectedProduct?.image_url;
            const hasImage = imageUrl && imageUrl !== 'null' && imageUrl !== '';

            card.innerHTML = `
            <div class="space-y-3">
                <div class="w-full h-24 bg-gray-100 dark:bg-gray-600 rounded-lg overflow-hidden flex items-center justify-center">
                    ${hasImage ? `<img src="${imageUrl}" alt="${variantText}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <svg class="w-8 h-8 text-gray-400" style="display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>` : `
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    `}
                </div>
                <h4 class="font-medium text-gray-900 dark:text-white text-sm">${variantText}</h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 dark:text-gray-400">SKU:</span>
                        <span class="text-xs text-gray-900 dark:text-gray-100 font-mono">${mainSku}</span>
                    </div>
                    ${twofifteenSku ? `
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 dark:text-gray-400">TwoFifteen:</span>
                        <span class="text-xs text-gray-900 dark:text-gray-100 font-mono">${twofifteenSku}</span>
                    </div>
                    ` : ''}
                    ${flashshipSku ? `
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Flashship:</span>
                        <span class="text-xs text-gray-900 dark:text-gray-100 font-mono">${flashshipSku}</span>
                    </div>
                    ` : ''}
                </div>
                <div class="flex items-center justify-center mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-xs text-green-500 font-medium">Click to select</span>
                </div>
            </div>
        `;

            container.appendChild(card);
        });
    }

    // Add product function
    function addProduct() {
        const container = document.getElementById('products-container');
        const productHtml = `
        <div class="product-item border border-gray-200 dark:border-gray-600 rounded-lg p-4 mb-4" data-index="${productIndex}">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-semibold text-gray-900 dark:text-white">Product #${productIndex + 1}</h4>
                <button type="button" class="remove-product bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                     <svg class="fill-white w-5 h-5" width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z"/>
    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Product *</label>
                    <div class="relative">
                        <input type="hidden" name="products[${productIndex}][variant_id]" class="variant-id-input" required>
                        <button type="button" class="product-picker-btn w-full p-3 text-left border border-gray-300 rounded-md bg-white dark:bg-gray-700 dark:border-gray-600 hover:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" data-product-index="${productIndex}">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 selected-product-text">Select Product...</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity *</label>
                    <input type="number" name="products[${productIndex}][quantity]" min="1" value="1" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Title</label>
                <input type="text" name="products[${productIndex}][title]" placeholder="Enter product title" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Designs -->
            <div class="designs-section mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Design * (at least 1)</label>
                    <button type="button" class="add-design bg-blue-500 text-white px-2 py-2 rounded text-sm hover:bg-blue-600 transition-colors">
                        + Add Design
                    </button>
                </div>
                <div class="designs-container">
                    <!-- Designs will be added here -->
                </div>
            </div>

            <!-- Mockups -->
            <div class="mockups-section">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mockup * (at least 1)</label>
                    <button type="button" class="add-mockup bg-blue-500 text-white px-2 py-2 rounded text-sm hover:bg-blue-600 transition-colors">
                        + Add Mockup
                    </button>
                </div>
                <div class="mockups-container">
                    <!-- Mockups will be added here -->
                </div>
            </div>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', productHtml);

        // Add first design and mockup by default
        addDesign(productIndex);
        addMockup(productIndex);

        productIndex++;
    }

    // Add design function
    function addDesign(productIdx) {
        const container = document.querySelector(`[data-index="${productIdx}"] .designs-container`);
        const designIndex = container.children.length;

        const designHtml = `
        <div class="design-item grid grid-cols-1 md:grid-cols-2 gap-2 mb-2 p-2 border border-gray-100 dark:border-gray-600 rounded">
            <div>
                <input type="url" name="products[${productIdx}][designs][${designIndex}][file_url]" placeholder="Design file URL" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
            <div class="flex gap-2">
                <select name="products[${productIdx}][designs][${designIndex}][print_space]" class="flex-1 p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="">-- Print Space --</option>
                    <option value="Front">Front</option>
                    <option value="Back">Back</option>
                    <option value="Left Sleeve">Left Sleeve</option>
                    <option value="Right Sleeve">Right Sleeve</option>
                    <option value="Hem">Hem</option>
                </select>
                <button type="button" class="remove-design bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                    Remove
                </button>
            </div>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', designHtml);
    }

    // Add mockup function
    function addMockup(productIdx) {
        const container = document.querySelector(`[data-index="${productIdx}"] .mockups-container`);
        const mockupIndex = container.children.length;

        const mockupHtml = `
        <div class="mockup-item grid grid-cols-1 md:grid-cols-2 gap-2 mb-2 p-2 border border-gray-100 dark:border-gray-600 rounded">
            <div>
                <input type="url" name="products[${productIdx}][mockups][${mockupIndex}][file_url]" placeholder="Mockup file URL" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
            <div class="flex gap-2">
                <select name="products[${productIdx}][mockups][${mockupIndex}][print_space]" class="flex-1 p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="">-- Print Space --</option>
                    <option value="Front">Front</option>
                    <option value="Back">Back</option>
                    <option value="Left Sleeve">Left Sleeve</option>
                    <option value="Right Sleeve">Right Sleeve</option>
                    <option value="Hem">Hem</option>
                </select>
                <button type="button" class="remove-mockup bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                    Remove
                </button>
            </div>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', mockupHtml);
    }

    // Function to handle shipping method change
    function handleShippingMethodChange() {
        const shippingMethod = document.getElementById('shipping_method').value;
        const orderNote = document.getElementById('order_note');
        const orderNoteLabel = document.querySelector('label[for="order_note"]');

        if (shippingMethod === 'tiktok_label') {
            // Enable order note for TikTok Label
            orderNote.disabled = false;
            orderNote.placeholder = 'Enter TikTok label link...';
            orderNoteLabel.innerHTML = 'Order Note (Link Label) *';
            orderNoteLabel.classList.add('text-red-600');
            orderNote.classList.remove('bg-gray-100', 'cursor-not-allowed');
            orderNote.classList.add('bg-white');
        } else {
            // Disable order note for other shipping methods
            orderNote.disabled = true;
            orderNote.value = '';
            orderNote.placeholder = 'Only allowed when TikTok Label is selected';
            orderNoteLabel.innerHTML = 'Order Note (Disabled)';
            orderNoteLabel.classList.remove('text-red-600');
            orderNote.classList.add('bg-gray-100', 'cursor-not-allowed');
            orderNote.classList.remove('bg-white');
        }
    }

    // Event listeners
    document.addEventListener('click', function(e) {
        if (e.target.id === 'add-product') {
            addProduct();
            saveFormData(); // Save after adding product
        }

        if (e.target.classList.contains('remove-product')) {
            if (document.querySelectorAll('.product-item').length > 1) {
                e.target.closest('.product-item').remove();
                saveFormData(); // Save after removing product
            } else {
                alert('You need at least 1 product!');
            }
        }

        if (e.target.classList.contains('add-design')) {
            const productItem = e.target.closest('.product-item');
            const productIdx = productItem.dataset.index;
            addDesign(productIdx);
            saveFormData(); // Save after adding design
        }

        if (e.target.classList.contains('remove-design')) {
            const designsContainer = e.target.closest('.designs-container');
            if (designsContainer.children.length > 1) {
                e.target.closest('.design-item').remove();
                saveFormData(); // Save after removing design
            } else {
                alert('You need at least 1 design!');
            }
        }

        if (e.target.classList.contains('add-mockup')) {
            const productItem = e.target.closest('.product-item');
            const productIdx = productItem.dataset.index;
            addMockup(productIdx);
            saveFormData(); // Save after adding mockup
        }

        if (e.target.classList.contains('remove-mockup')) {
            const mockupsContainer = e.target.closest('.mockups-container');
            if (mockupsContainer.children.length > 1) {
                e.target.closest('.mockup-item').remove();
                saveFormData(); // Save after removing mockup
            } else {
                alert('You need at least 1 mockup!');
            }
        }
    });

    // Add event listener for shipping method change
    document.addEventListener('DOMContentLoaded', function() {
        const shippingMethodSelect = document.getElementById('shipping_method');
        if (shippingMethodSelect) {
            shippingMethodSelect.addEventListener('change', handleShippingMethodChange);
            // Initialize on page load
            handleShippingMethodChange();
        }

        // Save form data before submit
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                saveFormData();
            });
        }

        // Auto-save form data on input changes
        const formInputs = document.querySelectorAll('input, select, textarea');
        formInputs.forEach(input => {
            input.addEventListener('change', function() {
                saveFormData();
            });
        });
    });
</script>
@endsection