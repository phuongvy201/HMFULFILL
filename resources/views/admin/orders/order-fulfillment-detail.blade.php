@extends('layouts.admin')

@section('title', 'Order Fulfillment Detail')

@section('content-admin')
<style>
    /* Tùy chỉnh thanh cuộn ngang */
    .max-w-full {
        position: relative;
        /* Thêm position relative cho container */
    }

    .max-w-full::-webkit-scrollbar {
        height: 8px;
        position: sticky;
        /* Thêm position sticky */
        bottom: 0;
        z-index: 50;
        /* Đảm bảo thanh cuộn hiển thị trên các phần tử khác */
    }

    .max-w-full::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
        position: sticky;
        /* Thêm position sticky */
        bottom: 0;
    }

    .max-w-full::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
        position: sticky;
        /* Thêm position sticky */
        bottom: 0;
    }

    .max-w-full::-webkit-scrollbar-thumb:hover {
        background: rgb(219, 219, 219);
    }

    /* Thêm container wrapper để fix thanh cuộn */
    .table-wrapper {
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
    }

    /* Container cho bảng và thanh cuộn */
    .table-container {
        position: relative;
        margin-bottom: 20px;
    }

    /* Thanh cuộn cố định */
    .fixed-scrollbar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 12px;
        background: #f1f1f1;
        z-index: 1000;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .fixed-scrollbar::-webkit-scrollbar {
        height: 12px;
    }

    .fixed-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .fixed-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    .fixed-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Thêm padding bottom để tránh che phủ nội dung */
    .table-content {
        padding-bottom: 20px;
    }
</style>
<main>
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: `Order Fulfillment Detail`}">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h2
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    x-text="pageName"></h2>

                <nav>
                    <ol class="flex items-center gap-1.5">
                        <li>
                            <a
                                class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                href="/admin/order-fulfillment-list">
                            Home
                                <svg
                                    class="stroke-current"
                                    width="17"
                                    height="16"
                                    viewBox="0 0 17 16"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                                        stroke=""
                                        stroke-width="1.2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </a>
                        </li>
                        <li
                            class="text-sm text-gray-800 dark:text-white/90"
                            x-text="pageName"></li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Breadcrumb End -->
        <div class="border-t border-gray-100 p-5 dark:border-gray-800 sm:p-6">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <!-- Search Controls -->
                <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            Order List
                        </h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="uploadSelected" class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Upload Selected
                        </button>

                    </div>
                </div>

                <div class="table-container">
                    <div class="table-wrapper">
                        <div class="table-content">
                            <table class="w-full min-w-[1102px] border-collapse bg-white">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="border-r px-6 py-4 text-left w-12">
                                            <input type="checkbox" id="select-all" class="form-checkbox rounded h-5 w-5 text-blue-600 transition duration-150 ease-in-out" />
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[150px]">
                                            <span class="text-sm font-semibold text-gray-900">Status</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[150px]">
                                            <span class="text-sm font-semibold text-gray-900">External ID</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Brand</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Channel</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Buyer Email</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">First Name</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Last Name</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Company</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Address 1</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Address 2</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">City</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">County</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Post Code</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Country</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Phone 1</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Phone 2</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Comment</span>
                                        </th>

                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Shipping Method</span>
                                        </th>
                                        <!-- <th class="border-r px-6 py-4 text-left min-w-[100px]">
                                            <span class="text-sm font-semibold text-gray-900">Actions</span>
                                        </th> -->
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($order->excelOrders as $excelOrder)
                                    <!-- Order Row -->
                                    <tr class="hover:bg-gray-50 transition-colors duration-200" data-order-id="{{ $excelOrder->id }}">
                                        <td class="border-r px-6 py-4">
                                            <input type="checkbox" class="item-checkbox form-checkbox rounded h-5 w-5 text-blue-600 transition duration-150 ease-in-out" data-order-id="{{ $excelOrder->id }}" />
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            @switch($excelOrder->status)
                                            @case('pending')
                                            <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-700">
                                                {{ $excelOrder->status }}
                                            </span>
                                            @break
                                            @case('processed')
                                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-theme-xs font-medium text-green-700">
                                                {{ $excelOrder->status }}
                                            </span>
                                            @break
                                            @case('failed')
                                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-theme-xs font-medium text-red-700">
                                                {{ $excelOrder->status }}
                                            </span>
                                            @break
                                            @default
                                            <span class="rounded-full bg-gray-50 px-2 py-0.5 text-theme-xs font-medium text-gray-700">
                                                {{ $excelOrder->status }}
                                            </span>
                                            @endswitch
                                            <br>
                                            <span class="text-xs text-gray-700 text-red-500">
                                                @if(is_array($excelOrder->api_response))
                                                @if(isset($excelOrder->api_response['error']))
                                                {{ $excelOrder->api_response['error'] }}
                                                @else
                                                {{ json_encode($excelOrder->api_response) }}
                                                @endif
                                                @else
                                                {{ $excelOrder->api_response }}
                                                @endif
                                            </span>
                                        </td>

                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm font-medium text-gray-900">{{ $excelOrder->external_id }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->brand }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->channel }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->buyer_email }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->first_name }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->last_name }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->company }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->address1 }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->address2 }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->city }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->county }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->post_code }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->country }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->phone1 }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->phone2 }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->comment }}</span>
                                        </td>

                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $excelOrder->shipping_method }}</span>
                                        </td>
                                        <!-- <td class="border-r px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <button onclick="deleteOrder('{{ $excelOrder->id }}')"
                                                    class="p-1 rounded-full hover:bg-gray-100 text-gray-500 hover:text-red-600 transition-colors duration-200">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                                <button class="p-1 rounded-full hover:bg-gray-100 text-gray-500 hover:text-blue-600 transition-colors duration-200">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td> -->
                                    </tr>

                                    <!-- Order Items Rows -->
                                    @foreach($excelOrder->items as $item)
                                    <tr class="bg-gray-50">
                                        <td colspan="18" class="px-8 py-4">
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
                                                    </div>


                                                </div>

                                                <!-- Designs -->
                                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                                    <h4 class="font-semibold text-gray-900 mb-2">Designs</h4>
                                                    <div class="flex space-x-4">
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
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="fixed-scrollbar">
                        <div style="width: 100%; height: 1px;"></div>
                    </div>
                </div>

                <!-- Pagination -->

            </div>
        </div>
    </div>
</main>
<script>
    // JavaScript to toggle accordion content
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', () => {
            const content = button.nextElementSibling;
            content.classList.toggle('hidden');
        });
    });

    // Checkbox handling
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');

    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update select-all checkbox state based on individual checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        });
    });

    // Log khi checkbox được chọn/bỏ chọn
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const orderId = this.dataset.orderId;
            const row = this.closest('tr');
            const externalId = row.querySelector('td:nth-child(2) span').textContent;
            console.log(`Checkbox ${this.checked ? 'checked' : 'unchecked'} for Order:`, {
                orderId,
                externalId,
                row: row
            });
        });
    });

    // Log khi "Select All" được thay đổi
    document.getElementById('select-all').addEventListener('change', function() {
        const isChecked = this.checked;
        const totalItems = document.querySelectorAll('.item-checkbox').length;
        console.log(`Select All ${isChecked ? 'checked' : 'unchecked'}:`, {
            totalItems,
            status: isChecked
        });
    });

    // Upload selected orders
    document.getElementById('uploadSelected').addEventListener('click', async function() {
        const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        const selectedOrders = Array.from(selectedCheckboxes).map(checkbox => {
            const row = checkbox.closest('tr');
            return {
                orderId: checkbox.dataset.orderId,
                externalId: row.querySelector('td:nth-child(2) span').textContent,
                brand: row.querySelector('td:nth-child(3) span').textContent,
                channel: row.querySelector('td:nth-child(4) span').textContent
            };
        });

        console.log('Selected Orders for Upload:', {
            count: selectedOrders.length,
            orders: selectedOrders
        });

        if (selectedOrders.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select at least one order to upload'
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

            const response = await fetch('/admin/order-fulfillment-upload', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    order_ids: selectedOrders.map(order => order.orderId)
                })
            });

            const result = await response.json();

            if (result.success) {
                // Kiểm tra từng order
                const failedOrders = result.results.filter(r => !r.success);
                if (failedOrders.length > 0) {
                    const errorMessages = failedOrders.map(o =>
                        `Order ${o.external_id}: ${o.message}`
                    ).join('\n');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Partial Success',
                        text: `Some orders failed to upload:\n${errorMessages}`,
                        html: true
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'All orders uploaded successfully!'
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'An error occurred while uploading orders'
                });
            }
        } catch (error) {
            console.error('Upload error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while uploading orders'
            });
        } finally {
            this.disabled = false;
            this.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload selected
            `;
        }
    });

    function deleteOrder(orderId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/order-fulfillment/${orderId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Xóa row khỏi table
                            const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                            if (row) {
                                // Xóa cả row items nếu có
                                const nextRow = row.nextElementSibling;
                                if (nextRow && nextRow.classList.contains('bg-gray-50')) {
                                    nextRow.remove();
                                }
                                row.remove();
                            }

                            Swal.fire(
                                'Deleted!',
                                'Order deleted successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message || 'An error occurred while deleting the order.',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the order.',
                            'error'
                        );
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tableWrapper = document.querySelector('.table-wrapper');
        const fixedScrollbar = document.querySelector('.fixed-scrollbar');
        const scrollContent = fixedScrollbar.querySelector('div');

        // Đồng bộ chiều rộng của thanh cuộn với bảng
        function updateScrollbarWidth() {
            scrollContent.style.width = tableWrapper.scrollWidth + 'px';
        }

        // Đồng bộ vị trí cuộn
        fixedScrollbar.addEventListener('scroll', function() {
            tableWrapper.scrollLeft = fixedScrollbar.scrollLeft;
        });

        tableWrapper.addEventListener('scroll', function() {
            fixedScrollbar.scrollLeft = tableWrapper.scrollLeft;
        });

        // Cập nhật khi trang được tải và khi thay đổi kích thước
        updateScrollbarWidth();
        window.addEventListener('resize', updateScrollbarWidth);
    });
</script>
@endsection