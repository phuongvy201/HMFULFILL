@extends('layouts.admin')

@section('title', 'API Orders List')

@section('content-admin')
<style>
    /* Modern Scrollbar Styling */
    .table-wrapper {
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .table-wrapper::-webkit-scrollbar {
        height: 10px;
    }

    .table-wrapper::-webkit-scrollbar-track {
        background: #f4f4f5;
        border-radius: 5px;
    }

    .table-wrapper::-webkit-scrollbar-thumb {
        background: #6b7280;
        border-radius: 5px;
        transition: background 0.2s;
    }

    .table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #4b5563;
    }

    .table-container {
        margin-bottom: 24px;
    }

    .fixed-scrollbar {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        height: 10px;
        background: #f4f4f5;
        z-index: 10;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .fixed-scrollbar::-webkit-scrollbar {
        height: 10px;
    }

    .fixed-scrollbar::-webkit-scrollbar-track {
        background: #f4f4f5;
        border-radius: 5px;
    }

    .fixed-scrollbar::-webkit-scrollbar-thumb {
        background: #6b7280;
        border-radius: 5px;
    }

    .fixed-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #4b5563;
    }

    /* Enhanced Table Styling */
    .table-content table {
        width: 100%;
        min-width: 1102px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-content th,
    .table-content td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .table-content th {
        background: #f9fafb;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .table-content td {
        font-size: 0.875rem;
        color: #374151;
    }

    .table-content tr:hover {
        background: #f9fafb;
    }

    /* Status Badge Styling */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1.5;
    }

    /* Form Input Styling */
    .form-input {
        transition: all 0.2s ease;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.875rem;
        width: 100%;
        background: #fff;
    }

    .form-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        margin-bottom: 6px;
    }

    /* Button Styling */
    .btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #2563eb;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    /* Card Styling */
    .stat-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    /* Breadcrumb Styling */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
    }

    .breadcrumb a {
        color: #6b7280;
        transition: color 0.2s ease;
    }

    .breadcrumb a:hover {
        color: #2563eb;
    }
</style>

<main>
    <div class="p-6 mx-auto max-w-7xl">
        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: `API Orders List`}">
            <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">API Orders List</h2>
                <nav class="breadcrumb">
                    <ol class="flex items-center gap-2">
                        <li>
                            <a class="inline-flex items-center gap-2 text-gray-500 hover:text-blue-600" href="{{ route('admin.statistics.dashboard') }}">
                                Dashboard
                                <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                        </li>
                        <li class="text-gray-900 dark:text-white" x-text="pageName"></li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Breadcrumb End -->

        <!-- Action Buttons -->
        <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Order List
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <button id="updateTracking" class="inline-flex items-center gap-2 rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Update Tracking
                </button>
                <button id="processSelected" class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Process Selected Orders
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="stat-card">
                <h5 class="text-sm font-medium text-gray-500">Total Orders</h5>
                <h2 class="text-3xl font-bold text-gray-900">{{ $statistics['total_orders'] }}</h2>
            </div>
            <div class="stat-card">
                <h5 class="text-sm font-medium text-gray-500">Total Items</h5>
                <h2 class="text-3xl font-bold text-gray-900">{{ $statistics['total_items'] }}</h2>
            </div>
            <div class="stat-card">
                <h5 class="text-sm font-medium text-gray-500">Pending Orders</h5>
                <h2 class="text-3xl font-bold text-gray-900">{{ $statistics['pending_orders'] }}</h2>
            </div>
        </div>

        <div class="border-t border-gray-100 py-6 dark:border-gray-800">
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/50">
                <!-- Filter Form -->
                <div class="p-6">
                    <form action="{{ route('admin.api-orders') }}" method="GET" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="form-label">External ID</label>
                            <input type="text" class="form-input" name="external_id" value="{{ $filters['external_id'] ?? '' }}">
                        </div>
                        <div>
                            <label class="form-label">Name</label>
                            <input type="text" class="form-input" name="customer_name" value="{{ $filters['customer_name'] ?? '' }}">
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select class="form-input" name="status">
                                <option value="">All</option>
                                @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Warehouse</label>
                            <select class="form-input" name="warehouse">
                                <option value="">All</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse }}" {{ ($filters['warehouse'] ?? '') == $warehouse ? 'selected' : '' }}>
                                    {{ $warehouse }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-input" name="created_at_min" value="{{ $filters['created_at_min'] ?? '' }}">
                        </div>
                        <div>
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-input" name="created_at_max" value="{{ $filters['created_at_max'] ?? '' }}">
                        </div>
                        <div class="flex items-end gap-3">
                            <button type="submit" class="btn btn-primary">
                                Search
                            </button>
                            <a href="{{ route('admin.api-orders') }}" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Orders Table -->
                <div class="table-container">
                    <div class="table-wrapper">
                        <div class="table-content">
                            <table class="w-full min-w-[1102px]">
                                <thead>
                                    <tr>
                                        <th class="w-10">
                                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        </th>
                                        <th>Internal ID</th>
                                        <th>External ID</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Warehouse</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Tracking Number</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                    <!-- Order Row -->
                                    <tr class="hover:bg-gray-50 transition-colors duration-200" data-order-id="{{ $order->id }}">
                                        <td>
                                            <input type="checkbox" name="selected_orders[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        </td>
                                        <td>{{ $order->orderMapping->internal_id ?? 'N/A' }}</td>
                                        <td>{{ $order->external_id }}</td>
                                        <td>{{ $order->creator->first_name ?? 'N/A' }} {{ $order->creator->last_name ?? '' }}</td>
                                        <td>{{ $order->creator->email ?? 'N/A' }}</td>
                                        <td>{{ $order->warehouse }}</td>
                                        <td>${{ number_format($order->items ? $order->items->sum(function($item) {
                                            return (float)$item->print_price * (int)$item->quantity;
                                        }) : 0, 2) }}</td>
                                        <td>
                                            @php
                                            if ($order->status === 'processed') {
                                            $statusClass = 'green-100 text-green-700';
                                            } elseif ($order->status === 'pending') {
                                            $statusClass = 'yellow-100 text-yellow-700';
                                            } elseif ($order->status === 'cancelled') {
                                            $statusClass = 'red-100 text-red-700';
                                            } elseif ($order->status === 'Shipped') {
                                            $statusClass = 'green-100 text-green-700';
                                            } elseif ($order->status === 'failed') {
                                            $statusClass = 'red-100 text-red-700';
                                            } elseif ($order->status === 'on hold') {
                                            $statusClass = 'blue-100 text-blue-700';
                                            } else {
                                            $statusClass = 'gray-100 text-gray-700';
                                            }
                                            @endphp
                                            <span class="status-badge bg-{{ $statusClass }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                            <br>
                                            <span class="text-xs text-red-500">
                                                @if(is_array($order->api_response))
                                                @if(isset($order->api_response['error']))
                                                {{ $order->api_response['error'] }}
                                                @else
                                                {{ json_encode($order->api_response) }}
                                                @endif
                                                @else
                                                {{ $order->api_response }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $order->tracking_number }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-primary">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View
                                                </a>
                                                @if($order->warehouse === 'US' && $order->status === 'processed' && $order->orderMapping)
                                                <button onclick="editDtfOrder('{{ $order->orderMapping->internal_id }}')" class="btn btn-secondary">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Order Items Rows -->
                                    @if($order->items)
                                    @foreach($order->items as $item)
                                    <tr class="bg-gray-50">
                                        <td colspan="9" class="px-8 py-4">
                                            <div class="grid grid-cols-3 gap-6">
                                                <!-- Item Details -->
                                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                                    <h4 class="font-semibold text-gray-900 mb-2">{{ $item->title }}</h4>
                                                    <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                                                    <p class="text-sm text-gray-600">Part Number: {{ $item->part_number }}</p>
                                                    <p class="text-sm text-gray-600">Label Name: {{ $item->label_name }}</p>
                                                    <p class="text-sm text-gray-600">Label Type: {{ $item->label_type }}</p>
                                                    <p class="text-sm text-gray-600">Description: {{ $item->description }}</p>
                                                </div>

                                                <!-- Mockups -->
                                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                                    <h4 class="font-semibold text-gray-900 mb-2">Mockups</h4>
                                                    <div class="flex space-x-4">
                                                        @if($item->mockups)
                                                        @foreach($item->mockups as $mockup)
                                                        <a href="{{ $mockup->url }}" target="_blank" class="group">
                                                            <div class="relative w-24 h-24 overflow-hidden rounded-lg border border-gray-200 hover:border-blue-500 transition-colors duration-200">
                                                                @if(str_contains(strtolower($mockup->url), 'https://drive'))
                                                                <img src="{{ asset('assets/images/google-drive.png') }}"
                                                                    alt="{{ $mockup->title }}"
                                                                    class="w-full h-full object-cover" />
                                                                @else
                                                                <img src="{{ $mockup->url }}"
                                                                    alt="{{ $mockup->title }}"
                                                                    class="w-full h-full object-cover" />
                                                                @endif
                                                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-2">
                                                                    <span class="text-xs text-white font-medium">{{ $mockup->title }}</span>
                                                                </div>
                                                            </div>
                                                        </a>
                                                        @endforeach
                                                        @else
                                                        <p class="text-sm text-gray-500">No mockups available</p>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Designs -->
                                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                                    <h4 class="font-semibold text-gray-900 mb-2">Designs</h4>
                                                    <div class="flex space-x-4">
                                                        @if($item->designs)
                                                        @foreach($item->designs as $design)
                                                        <a href="{{ $design->url }}" target="_blank" class="group">
                                                            <div class="relative w-24 h-24 overflow-hidden rounded-lg border border-gray-200 hover:border-blue-500 transition-colors duration-200">
                                                                @if(str_contains(strtolower($design->url), 'https://drive'))
                                                                <img src="{{ asset('assets/images/google-drive.png') }}"
                                                                    alt="{{ $design->title }}"
                                                                    class="w-full h-full object-cover" />
                                                                @else
                                                                <img src="{{ $design->url }}"
                                                                    alt="{{ $design->title }}"
                                                                    class="w-full h-full object-cover" />
                                                                @endif
                                                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-2">
                                                                    <span class="text-xs text-white font-medium">{{ $design->title }}</span>
                                                                </div>
                                                            </div>
                                                        </a>
                                                        @endforeach
                                                        @else
                                                        <p class="text-sm text-gray-500">No designs available</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                    @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No orders found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="fixed-scrollbar">
                        <div style="width: 100%; height: 1px;"></div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-gray-200">
                    {{ $orders->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableWrapper = document.querySelector('.table-wrapper');
        const fixedScrollbar = document.querySelector('.fixed-scrollbar');
        const scrollContent = fixedScrollbar.querySelector('div');

        function updateScrollbarWidth() {
            scrollContent.style.width = tableWrapper.scrollWidth + 'px';
        }

        fixedScrollbar.addEventListener('scroll', function() {
            tableWrapper.scrollLeft = fixedScrollbar.scrollLeft;
        });

        tableWrapper.addEventListener('scroll', function() {
            fixedScrollbar.scrollLeft = tableWrapper.scrollLeft;
        });

        updateScrollbarWidth();
        window.addEventListener('resize', updateScrollbarWidth);

        // Checkbox functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        const orderCheckboxes = document.querySelectorAll('.order-checkbox');
        const processSelectedButton = document.getElementById('processSelected');

        selectAllCheckbox.addEventListener('change', function() {
            orderCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateProcessButtonState();
        });

        orderCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateProcessButtonState();
                selectAllCheckbox.checked = Array.from(orderCheckboxes).every(cb => cb.checked);
            });
        });

        function updateProcessButtonState() {
            const hasSelectedOrders = Array.from(orderCheckboxes).some(cb => cb.checked);
            processSelectedButton.disabled = !hasSelectedOrders;
        }

        // Update Tracking functionality
        const updateTrackingButton = document.getElementById('updateTracking');
        if (updateTrackingButton) {
            updateTrackingButton.addEventListener('click', async function() {
                try {
                    this.disabled = true;
                    this.innerHTML = `
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Updating...
                    `;

                    const response = await fetch('/admin/orders/update-tracking', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: result.message
                        }).then(() => {
                            // Reload page to show updated tracking numbers
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: result.message
                        });
                    }
                } catch (error) {
                    console.error('Update tracking error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Có lỗi xảy ra khi cập nhật tracking numbers'
                    });
                } finally {
                    this.disabled = false;
                    this.innerHTML = `
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Update Tracking
                    `;
                }
            });
        }

        processSelectedButton.addEventListener('click', async function() {
            const selectedOrders = Array.from(orderCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selectedOrders.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select at least one order to process'
                });
                return;
            }

            try {
                this.disabled = true;
                this.innerHTML = `
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                `;

                const response = await fetch('/admin/api-orders/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        order_ids: selectedOrders
                    })
                });

                const result = await response.json();

                if (result.success) {
                    const failedOrders = result.results.filter(r => !r.success);
                    if (failedOrders.length > 0) {
                        const errorMessages = failedOrders.map(o =>
                            `Order ${o.external_id}: ${o.message}`
                        ).join('\n');

                        Swal.fire({
                            icon: 'warning',
                            title: 'Partial Success',
                            text: `Some orders failed to process:\n${errorMessages}`,
                            html: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'All orders processed successfully!'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'An error occurred while processing orders'
                    });
                }
            } catch (error) {
                console.error('Process error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing orders'
                });
            } finally {
                this.disabled = false;
                this.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Process Selected Orders
                `;
            }
        });
    });

    function editDtfOrder(orderId) {
        Swal.fire({
            title: 'Edit DTF Order',
            html: `
                <form id="editOrderForm" class="text-left">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Channel</label>
                        <input type="text" id="channel" name="channel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Buyer Email</label>
                        <input type="email" id="buyer_email" name="buyer_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Shipping Address</label>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" id="firstName" name="shipping_address[firstName]" placeholder="First Name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="lastName" name="shipping_address[lastName]" placeholder="Last Name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="company" name="shipping_address[company]" placeholder="Company" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="address1" name="shipping_address[address1]" placeholder="Address 1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="address2" name="shipping_address[address2]" placeholder="Address 2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="city" name="shipping_address[city]" placeholder="City" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="state" name="shipping_address[state]" placeholder="State" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="postcode" name="shipping_address[postcode]" placeholder="Postcode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="country" name="shipping_address[country]" placeholder="Country" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="phone1" name="shipping_address[phone1]" placeholder="Phone 1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" id="phone2" name="shipping_address[phone2]" placeholder="Phone 2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                const form = document.getElementById('editOrderForm');
                const formData = new FormData(form);
                const data = {};

                // Chỉ lấy những trường có giá trị
                for (let [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        if (key.startsWith('shipping_address[')) {
                            const field = key.match(/\[(.*?)\]/)[1];
                            if (!data.shipping_address) {
                                data.shipping_address = {};
                            }
                            data.shipping_address[field] = value;
                        } else {
                            data[key] = value;
                        }
                    }
                }

                // Nếu không có dữ liệu nào được nhập
                if (Object.keys(data).length === 0) {
                    Swal.showValidationMessage('Vui lòng nhập ít nhất một trường để cập nhật');
                    return false;
                }

                try {
                    const response = await fetch(`/admin/api/dtf/orders/${orderId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Failed to update order');
                    }

                    const result = await response.json();
                    return result;
                } catch (error) {
                    console.error('Update error:', error);
                    Swal.showValidationMessage(`Request failed: ${error.message}`);
                    return false;
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Order updated successfully'
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    }
</script>
@endsection