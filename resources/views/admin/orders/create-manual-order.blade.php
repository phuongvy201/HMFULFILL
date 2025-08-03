@extends('layouts.admin')

@section('title', 'Tạo Đơn Thủ Công - Admin')

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
    }

    /* Scrollbar Styling */
    .modal .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .modal .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .modal .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .modal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .dark .modal .overflow-y-auto::-webkit-scrollbar-track {
        background: #374151;
    }

    .dark .modal .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #6b7280;
    }

    .dark .modal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>
@endpush

@section('content')
<div class="p-4 mx-auto max-w-7xl md:p-6">
    <!-- Breadcrumb -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Tạo Đơn Thủ Công</h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.statistics.dashboard') }}">
                        Dashboard
                        <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </li>
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.all-orders') }}">
                        Đơn hàng
                        <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </li>
                <li class="text-sm text-gray-800 dark:text-white/90">Tạo Đơn Thủ Công</li>
            </ol>
        </nav>
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
    <form method="POST" action="{{ route('admin.orders.store') }}" class="space-y-6">
        @csrf

        <!-- Order Information -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Thông tin đơn hàng</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã đơn hàng *</label>
                    <input type="text" name="order_number" id="order_number" value="{{ old('order_number') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="store_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tên cửa hàng</label>
                    <input type="text" name="store_name" id="store_name" value="{{ old('store_name') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kênh bán hàng</label>
                    <select name="channel" id="channel" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="admin-manual" {{ old('channel') == 'admin-manual' ? 'selected' : '' }}>Admin Thủ công</option>
                        <option value="web" {{ old('channel') == 'web' ? 'selected' : '' }}>Website</option>
                        <option value="tiktok" {{ old('channel') == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                        <option value="shopee" {{ old('channel') == 'shopee' ? 'selected' : '' }}>Shopee</option>
                        <option value="lazada" {{ old('channel') == 'lazada' ? 'selected' : '' }}>Lazada</option>
                    </select>
                </div>

                <div>
                    <label for="shipping_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phương thức vận chuyển</label>
                    <select name="shipping_method" id="shipping_method" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">-- Chọn phương thức --</option>
                        <option value="tiktok_label" {{ old('shipping_method') == 'tiktok_label' ? 'selected' : '' }}>TikTok Label</option>
                        <option value="seller_shipping" {{ old('shipping_method') == 'seller_shipping' ? 'selected' : '' }}>Seller Shipping</option>
                    </select>
                </div>

                <div>
                    <label for="warehouse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kho hàng *</label>
                    <select name="warehouse" id="warehouse" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">-- Chọn kho --</option>
                        <option value="UK" {{ old('warehouse') == 'UK' ? 'selected' : '' }}>UK</option>
                        <option value="US" {{ old('warehouse') == 'US' ? 'selected' : '' }}>US</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="order_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ghi chú đơn hàng</label>
                <textarea name="order_note" id="order_note" rows="3" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('order_note') }}</textarea>
            </div>
        </div>

        <!-- Shipping Information -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Thông tin giao hàng</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tên người nhận *</label>
                    <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email người nhận *</label>
                    <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Số điện thoại</label>
                    <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quốc gia *</label>
                    <select name="country" id="country" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">-- Chọn quốc gia --</option>
                        <option value="VN" {{ old('country') == 'VN' ? 'selected' : '' }}>Việt Nam</option>
                        <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>United States</option>
                        <option value="UK" {{ old('country') == 'UK' ? 'selected' : '' }}>United Kingdom</option>
                        <option value="CA" {{ old('country') == 'CA' ? 'selected' : '' }}>Canada</option>
                        <option value="AU" {{ old('country') == 'AU' ? 'selected' : '' }}>Australia</option>
                    </select>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Địa chỉ *</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="address_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Địa chỉ 2</label>
                    <input type="text" name="address_2" id="address_2" value="{{ old('address_2') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thành phố *</label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tỉnh/Bang</label>
                    <input type="text" name="state" id="state" value="{{ old('state') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="postcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã bưu điện *</label>
                    <input type="text" name="postcode" id="postcode" value="{{ old('postcode') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sản phẩm</h3>
                <button type="button" id="add-product" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                    + Thêm sản phẩm
                </button>
            </div>

            <div id="products-container">
                <!-- Products will be added here dynamically -->
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.all-orders') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                Hủy
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                Tạo Đơn Hàng
            </button>
        </div>
    </form>
</div>

<!-- Product Picker Modal -->
<div id="productPickerModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Modal Header -->
            <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modal-title">
                        Chọn Sản Phẩm
                    </h3>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="bg-white dark:bg-gray-800 px-6 py-4 max-h-96 overflow-y-auto">
                <!-- Breadcrumb -->
                <div class="mb-4">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <button type="button" id="breadcrumbProducts" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                    </svg>
                                    Sản phẩm
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
                </div>

                <!-- Search Box -->
                <div class="mb-4">
                    <div class="relative">
                        <input type="text" id="productSearch" placeholder="Tìm kiếm sản phẩm..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Category Filter (Only for products) -->
                <div class="mb-4" id="categoryFilterSection">
                    <div class="flex flex-wrap gap-2" id="categoryFilters">
                        <button type="button" class="filter-pill px-3 py-1 text-sm bg-blue-500 text-white rounded-full transition-colors" data-category="all">
                            Tất cả
                        </button>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="productsGrid">
                    <!-- Products will be populated here -->
                </div>

                <!-- Variants Grid -->
                <div class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="variantsGrid">
                    <!-- Variants will be populated here -->
                </div>

                <!-- No Results -->
                <div id="noResults" class="hidden text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.239 0-4.24-.677-5.937-1.833C4.416 12.089 3 10.072 3 8a8 8 0 1116 0c0 2.072-1.416 4.089-3.063 5.167z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Không tìm thấy kết quả</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Thử tìm kiếm với từ khóa khác</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmSelection" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Chọn Sản Phẩm
                </button>
                <button type="button" class="close-modal mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Hủy
                </button>
            </div>
        </div>
    </div>
</div>

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
        addProduct(); // Add first product by default
        initializeModal();
    });

    // Load products from API
    async function loadProducts() {
        try {
            const response = await fetch('{{ route("customer.api.products-with-variants") }}');
            const data = await response.json();

            // Transform data to separate products and variants
            products = data.map(item => ({
                id: item.id,
                name: item.name,
                category: item.category,
                description: item.description,
                base_price: item.base_price,
                currency: item.currency,
                variants: item.variants
            }));

            filteredProducts = [...products];
            populateCategories();
            showProductsStep();
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    // Initialize modal functionality
    function initializeModal() {
        const modal = document.getElementById('productPickerModal');
        const searchInput = document.getElementById('productSearch');
        const confirmBtn = document.getElementById('confirmSelection');

        // Open modal when product picker button is clicked
        document.addEventListener('click', function(e) {
            if (e.target.closest('.product-picker-btn')) {
                const btn = e.target.closest('.product-picker-btn');
                currentProductIndex = btn.dataset.productIndex;
                openModal();
            }
        });

        // Close modal
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('close-modal') || e.target.closest('.close-modal')) {
                closeModal();
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
            if (e.target.closest('.product-card')) {
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
                        productName: selectedProduct.name || 'Sản phẩm không tên',
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

                closeModal();
            }
        });

        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    function openModal() {
        const modal = document.getElementById('productPickerModal');
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
        document.querySelector('[data-category="all"]').classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-600', 'dark:text-gray-300');
        document.querySelector('[data-category="all"]').classList.add('bg-blue-500', 'text-white');

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
        document.getElementById('productSearch').placeholder = 'Tìm kiếm sản phẩm...';

        // Show/hide sections
        document.getElementById('categoryFilterSection').classList.remove('hidden');
        document.getElementById('productsGrid').classList.remove('hidden');
        document.getElementById('variantsGrid').classList.add('hidden');

        // Update confirm button
        document.getElementById('confirmSelection').textContent = 'Chọn Sản Phẩm';
        document.getElementById('confirmSelection').disabled = true;
        document.getElementById('confirmSelection').classList.add('opacity-50', 'cursor-not-allowed');

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
        document.getElementById('productSearch').placeholder = 'Tìm kiếm variant...';
        document.getElementById('productSearch').value = '';

        // Show/hide sections
        document.getElementById('categoryFilterSection').classList.add('hidden');
        document.getElementById('productsGrid').classList.add('hidden');
        document.getElementById('variantsGrid').classList.remove('hidden');

        // Update confirm button
        document.getElementById('confirmSelection').textContent = 'Chọn Variant';
        document.getElementById('confirmSelection').disabled = true;
        document.getElementById('confirmSelection').classList.add('opacity-50', 'cursor-not-allowed');

        // Populate variants
        currentVariants = selectedProduct.variants || [];
        populateVariantsGrid();
    }

    function closeModal() {
        const modal = document.getElementById('productPickerModal');
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
            card.className = 'product-card p-4 bg-white dark:bg-gray-700 rounded-lg border-2 border-transparent hover:border-blue-500 hover:shadow-lg transition-all cursor-pointer';
            card.dataset.productId = product.id;

            // Lấy hình ảnh theo cách API đã xử lý
            const imageUrl = product.image_url; // API đã xử lý URL đầy đủ
            const hasImage = imageUrl && imageUrl !== 'null' && imageUrl !== '';
            const productName = product.name || 'Sản phẩm không tên';
            const productCategory = product.category || 'Chưa phân loại';
            const productDescription = product.description || '';
            const productCurrency = product.currency || 'USD';
            const productBasePrice = product.base_price || '0';
            const variantCount = product.variants ? product.variants.length : 0;
            const imageCount = product.image_count || 0;

            card.innerHTML = `
            <div class="space-y-3">
                <div class="w-full h-32 bg-gray-100 dark:bg-gray-600 rounded-lg overflow-hidden flex items-center justify-center">
                    ${hasImage ? `<img src="${imageUrl}" alt="${productName}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <svg class="w-12 h-12 text-gray-400" style="display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>` : `
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    `}
                </div>
                <div class="flex justify-between items-start">
                    <h4 class="font-medium text-gray-900 dark:text-white text-sm">${productName}</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        ${productCategory}
                    </span>
                </div>
                ${productDescription ? `<p class="text-sm text-gray-600 dark:text-gray-400">${productDescription}</p>` : ''}
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">
                        ${variantCount} variant${variantCount > 1 ? 's' : ''}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        ${productCurrency} ${productBasePrice}
                    </span>
                </div>
                ${imageCount > 0 ? `<div class="flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    ${imageCount} hình ảnh
                </div>` : ''}
                <div class="flex items-center justify-center mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <svg class="w-4 h-4 text-blue-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-xs text-blue-500 font-medium">Chọn để xem variants</span>
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
            card.dataset.variantText = variant.attribute_text || 'Variant không tên';
            card.dataset.sku = variant.sku || '';

            // Handle undefined values
            const variantText = variant.attribute_text || 'Variant không tên';
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
                    <span class="text-xs text-green-500 font-medium">Click để chọn</span>
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
                <h4 class="font-semibold text-gray-900 dark:text-white">Sản phẩm #${productIndex + 1}</h4>
                <button type="button" class="remove-product bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                    Xóa
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Chọn sản phẩm *</label>
                    <div class="relative">
                        <input type="hidden" name="products[${productIndex}][variant_id]" class="variant-id-input" required>
                        <button type="button" class="product-picker-btn w-full p-3 text-left border border-gray-300 rounded-md bg-white dark:bg-gray-700 dark:border-gray-600 hover:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" data-product-index="${productIndex}">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 selected-product-text">Chọn sản phẩm...</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Số lượng *</label>
                    <input type="number" name="products[${productIndex}][quantity]" min="1" value="1" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tiêu đề sản phẩm</label>
                <input type="text" name="products[${productIndex}][title]" placeholder="Nhập tiêu đề sản phẩm" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Designs -->
            <div class="designs-section mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thiết kế * (ít nhất 1)</label>
                    <button type="button" class="add-design bg-green-500 text-white px-2 py-1 rounded text-sm hover:bg-green-600 transition-colors">
                        + Thêm thiết kế
                    </button>
                </div>
                <div class="designs-container">
                    <!-- Designs will be added here -->
                </div>
            </div>

            <!-- Mockups -->
            <div class="mockups-section">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mockup * (ít nhất 1)</label>
                    <button type="button" class="add-mockup bg-purple-500 text-white px-2 py-1 rounded text-sm hover:bg-purple-600 transition-colors">
                        + Thêm mockup
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
                <input type="url" name="products[${productIdx}][designs][${designIndex}][file_url]" placeholder="URL file thiết kế" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
            <div class="flex gap-2">
                <select name="products[${productIdx}][designs][${designIndex}][print_space]" class="flex-1 p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="">-- Vị trí in --</option>
                    <option value="front">Mặt trước</option>
                    <option value="back">Mặt sau</option>
                    <option value="left">Bên trái</option>
                    <option value="right">Bên phải</option>
                </select>
                <button type="button" class="remove-design bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                    Xóa
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
                <input type="url" name="products[${productIdx}][mockups][${mockupIndex}][file_url]" placeholder="URL file mockup" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
            <div class="flex gap-2">
                <select name="products[${productIdx}][mockups][${mockupIndex}][print_space]" class="flex-1 p-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="">-- Vị trí in --</option>
                    <option value="front">Mặt trước</option>
                    <option value="back">Mặt sau</option>
                    <option value="left">Bên trái</option>
                    <option value="right">Bên phải</option>
                </select>
                <button type="button" class="remove-mockup bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                    Xóa
                </button>
            </div>
        </div>
        `;

        container.insertAdjacentHTML('beforeend', mockupHtml);
    }

    // Event listeners
    document.addEventListener('click', function(e) {
        if (e.target.id === 'add-product') {
            addProduct();
        }

        if (e.target.classList.contains('remove-product')) {
            if (document.querySelectorAll('.product-item').length > 1) {
                e.target.closest('.product-item').remove();
            } else {
                alert('Cần có ít nhất 1 sản phẩm!');
            }
        }

        if (e.target.classList.contains('add-design')) {
            const productItem = e.target.closest('.product-item');
            const productIdx = productItem.dataset.index;
            addDesign(productIdx);
        }

        if (e.target.classList.contains('remove-design')) {
            const designsContainer = e.target.closest('.designs-container');
            if (designsContainer.children.length > 1) {
                e.target.closest('.design-item').remove();
            } else {
                alert('Cần có ít nhất 1 thiết kế!');
            }
        }

        if (e.target.classList.contains('add-mockup')) {
            const productItem = e.target.closest('.product-item');
            const productIdx = productItem.dataset.index;
            addMockup(productIdx);
        }

        if (e.target.classList.contains('remove-mockup')) {
            const mockupsContainer = e.target.closest('.mockups-container');
            if (mockupsContainer.children.length > 1) {
                e.target.closest('.mockup-item').remove();
            } else {
                alert('Cần có ít nhất 1 mockup!');
            }
        }
    });
</script>
@endsection