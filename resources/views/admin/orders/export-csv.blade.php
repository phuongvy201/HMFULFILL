@extends('layouts.admin')

@section('content-admin')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <div class="mx-auto max-w-5xl">

        <!-- Breadcrumb Start -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-title-md2 font-bold text-black dark:text-white">
                Export Orders CSV
            </h2>
            <nav>
                <ol class="flex items-center gap-2">
                    <li><a class="font-medium" href="{{ route('admin.dashboard') }}">Dashboard /</a></li>
                    <li class="font-medium text-primary">Export Orders CSV</li>
                </ol>
            </nav>
        </div>
        <!-- Breadcrumb End -->

        <!-- Export Form Start -->
        <div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 xl:pb-1">
            <div class="mb-6">
                <h4 class="mb-6 text-xl font-bold text-black dark:text-white">
                    Export Criteria
                </h4>

                <form id="exportForm" action="/admin/orders/export-csv" method="POST" class="space-y-6">
                    @csrf

                    <!-- Export All Option -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="export_all" name="export_all" value="1"
                                class="mr-3 h-5 w-5 rounded border border-stroke bg-transparent text-primary focus:ring-primary dark:border-form-strokedark dark:bg-form-input">
                            <span class="text-black dark:text-white font-medium">Export All Orders (Bỏ qua bộ lọc ngày)</span>
                        </label>
                    </div>

                    <!-- Date Range -->
                    <div id="date_filters" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2.5 block text-black dark:text-white">From Date</label>
                            <input type="date" name="date_from" id="date_from"
                                class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-2.5 block text-black dark:text-white">To Date</label>
                            <input type="date" name="date_to" id="date_to"
                                class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        </div>
                    </div>

                    <!-- Quick Date Suggestions -->
                    <div class="mb-4">
                        <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Gợi ý khoảng thời gian:</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="date-suggestion px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded-md dark:bg-gray-700 dark:hover:bg-gray-600"
                                data-days="7">7 ngày gần đây</button>
                            <button type="button" class="date-suggestion px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded-md dark:bg-gray-700 dark:hover:bg-gray-600"
                                data-days="30">30 ngày gần đây</button>
                            <button type="button" class="date-suggestion px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded-md dark:bg-gray-700 dark:hover:bg-gray-600"
                                data-days="90">3 tháng gần đây</button>
                            <button type="button" class="date-suggestion px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded-md dark:bg-gray-700 dark:hover:bg-gray-600"
                                data-from="2025-05-28" data-to="2025-07-02">Tất cả đơn hàng hiện có</button>
                        </div>
                    </div>

                    <!-- Status and Warehouse -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2.5 block text-black dark:text-white">Order Status</label>
                            <select name="status"
                                class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="on hold">On Hold</option>
                                <option value="processed">Processed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2.5 block text-black dark:text-white">Warehouse</label>
                            <select name="warehouse"
                                class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                                <option value="">All Warehouses</option>
                                <option value="UK">UK</option>
                                <option value="US">US</option>
                            </select>
                        </div>
                    </div>

                    <!-- Specific Order IDs -->
                    <div>
                        <label class="mb-2.5 block text-black dark:text-white">
                            Specific Order IDs
                            <span class="text-meta-7 text-sm">(Optional - Enter comma-separated order IDs)</span>
                        </label>
                        <textarea name="order_ids_input" rows="3" placeholder="1,2,3,4..."
                            class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"></textarea>
                    </div>

                    <!-- Export Format -->
                    <div class="mb-4">
                        <label class="mb-2.5 block text-black dark:text-white">Export Format</label>
                        <div class="space-y-3">
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="export_format" value="csv" checked
                                        class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                    <span>CSV (.csv) - Phổ biến</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="export_format" value="tsv"
                                        class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                    <span>TSV (.tsv) - Tốt cho External_ID số lớn</span>
                                </label>
                            </div>

                            <div class="p-2 bg-blue-50 border border-blue-200 rounded text-xs dark:bg-blue-900/20 dark:border-blue-700">
                                <div class="flex gap-6">
                                    <div>
                                        <strong class="text-blue-800 dark:text-blue-200">CSV:</strong>
                                        <span class="text-blue-700 dark:text-blue-300">External_ID format: '577020000000000000 (forced text)</span>
                                    </div>
                                    <div>
                                        <strong class="text-blue-800 dark:text-blue-200">TSV:</strong>
                                        <span class="text-blue-700 dark:text-blue-300">External_ID format: '577020000000000000 (forced text)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Button -->
                    <div class="flex justify-end">
                        <button type="submit" style="background-color: #000000;"
                            class="inline-flex items-center justify-center rounded-md bg-primary py-4 px-10 text-center font-medium text-white hover:bg-opacity-90 lg:px-8 xl:px-10"
                            id="exportButton">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span id="exportButtonText">Export CSV</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Export Form End -->

        <!-- Help Section -->
        <div class="mt-6 rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 xl:pb-1">
            <h4 class="mb-4 text-xl font-bold text-black dark:text-white">
                CSV Export Information
            </h4>
            <div class="mb-6">
                <h5 class="mb-2 font-semibold text-black dark:text-white">Exported Columns:</h5>
                <ul class="list-disc pl-6 space-y-1 text-sm">
                    <li>External_ID - Order identifier (formatted as text)</li>
                    <li>Link_Label - Shipping label URL</li>
                    <li>Order_Status - Current order status</li>
                    <li>SKU - Product SKU</li>
                    <li>Variant - Size, Color, Sides (e.g., S,White,1 side)</li>
                    <li>Quantity - Item quantity</li>
                    <li>Price - Item price</li>
                    <li>Mockup_1 to Mockup_5 - Mockup URLs</li>
                    <li>Design_1 to Design_5 - Design URLs</li>
                    <li>Tracking_Number - Shipment tracking</li>
                    <li>Created_Date - Order creation date (YYYY-MM-DD)</li>
                    <li>Created_Time - Order creation time (HH:MM:SS)</li>
                </ul>

                <div class="mt-4 space-y-2">
                    <p class="text-sm text-meta-7">
                        <strong>Note:</strong> If an order has multiple items, each item will appear on a separate row.
                        If an item has multiple mockups/designs, they will be distributed across the Mockup_1-5 and Design_1-5 columns.
                    </p>

                    <div class="p-3 bg-green-50 border-l-4 border-green-400 dark:bg-green-900/20 dark:border-green-600">
                        <p class="text-sm text-green-800 dark:text-green-200">
                            <strong>✅ External_ID Format Fixed:</strong> Apostrophe prefix (') được thêm vào để tránh Excel convert External_ID thành scientific notation (5.76755E+17).
                            Bạn sẽ thấy <strong>'576749181815789831</strong> thay vì <strong>5.76755E+17</strong>.
                        </p>
                    </div>

                    <div class="p-3 bg-yellow-50 border-l-4 border-yellow-400 dark:bg-yellow-900/20 dark:border-yellow-600">
                        <div class="space-y-2">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <strong>📋 Hướng dẫn sử dụng Excel:</strong>
                            </p>
                            <ul class="text-sm text-yellow-800 dark:text-yellow-200 list-disc pl-4 space-y-1">
                                <li><strong>External_ID:</strong> Sử dụng apostrophe prefix (') để force Excel hiểu là text, tránh scientific notation (5.76755E+17).</li>
                                <li><strong>Apostrophe prefix:</strong> Sẽ hiển thị '576749181815789831 trong Excel, đảm bảo không bị convert thành số.</li>
                                <li><strong>Columns quá hẹp (hiển thị ####):</strong> Double-click vào divider giữa columns để auto-resize.</li>
                                <li><strong>Date/Time:</strong> Đã tách thành 2 columns riêng để tránh overflow.</li>
                                <li><strong>Khuyến nghị:</strong> Cả <strong>CSV và TSV format</strong> đều sử dụng apostrophe prefix cho External_ID.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('exportForm');
        const button = document.getElementById('exportButton');
        const exportAllCheckbox = document.getElementById('export_all');
        const dateFilters = document.getElementById('date_filters');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');

        // Handle export all checkbox
        exportAllCheckbox.addEventListener('change', function() {
            if (this.checked) {
                dateFilters.style.opacity = '0.5';
                dateFromInput.disabled = true;
                dateToInput.disabled = true;
                dateFromInput.value = '';
                dateToInput.value = '';
            } else {
                dateFilters.style.opacity = '1';
                dateFromInput.disabled = false;
                dateToInput.disabled = false;
            }
        });

        // Validate date range
        function validateDateRange() {
            if (dateFromInput.value && dateToInput.value) {
                if (dateFromInput.value > dateToInput.value) {
                    // Auto-swap dates
                    const temp = dateFromInput.value;
                    dateFromInput.value = dateToInput.value;
                    dateToInput.value = temp;

                    // Show notification
                    showNotification('Đã tự động hoán đổi ngày từ và ngày đến để hợp lệ.', 'warning');
                }
            }
        }

        // Add event listeners for date validation
        dateFromInput.addEventListener('change', validateDateRange);
        dateToInput.addEventListener('change', validateDateRange);

        // Handle export format change
        const exportFormatRadios = document.querySelectorAll('input[name="export_format"]');
        const exportButtonText = document.getElementById('exportButtonText');

        exportFormatRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'tsv') {
                    exportButtonText.textContent = 'Export TSV';
                } else {
                    exportButtonText.textContent = 'Export CSV';
                }
            });
        });

        // Handle date suggestions
        document.querySelectorAll('.date-suggestion').forEach(button => {
            button.addEventListener('click', function() {
                if (exportAllCheckbox.checked) {
                    exportAllCheckbox.checked = false;
                    dateFilters.style.opacity = '1';
                    dateFromInput.disabled = false;
                    dateToInput.disabled = false;
                }

                if (this.dataset.days) {
                    const days = parseInt(this.dataset.days);
                    const today = new Date();
                    const fromDate = new Date();
                    fromDate.setDate(today.getDate() - days);

                    dateFromInput.value = fromDate.toISOString().split('T')[0];
                    dateToInput.value = today.toISOString().split('T')[0];
                } else if (this.dataset.from && this.dataset.to) {
                    dateFromInput.value = this.dataset.from;
                    dateToInput.value = this.dataset.to;
                }

                showNotification(`Đã chọn khoảng thời gian: ${this.textContent}`, 'info');
            });
        });

        // Show notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 transition-all duration-300 ${
                type === 'warning' ? 'bg-yellow-500 text-white border-l-4 border-yellow-600' : 
                type === 'error' ? 'bg-red-500 text-white border-l-4 border-red-600' : 
                'bg-blue-500 text-white border-l-4 border-blue-600'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">${message}</div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto remove after 8 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notification.parentElement) {
                            document.body.removeChild(notification);
                        }
                    }, 300);
                }
            }, 8000);
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Disable button and show loading
            button.disabled = true;
            button.innerHTML = `
            <svg class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Exporting...
        `;

            // Validate before submit
            if (!exportAllCheckbox.checked && dateFromInput.value && dateToInput.value) {
                if (dateFromInput.value > dateToInput.value) {
                    showNotification('Ngày bắt đầu không thể lớn hơn ngày kết thúc!', 'error');
                    button.disabled = false;
                    button.innerHTML = `
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export CSV
                    `;
                    return;
                }
            }

            // Process order IDs
            const orderIdsInput = form.querySelector('input[name="order_ids_input"]') || form.querySelector('textarea[name="order_ids_input"]');
            if (orderIdsInput && orderIdsInput.value.trim()) {
                const orderIds = orderIdsInput.value.split(',').map(id => id.trim()).filter(id => id);

                // Remove existing hidden inputs
                form.querySelectorAll('input[name="order_ids[]"]').forEach(input => input.remove());

                // Add order IDs as array
                orderIds.forEach(id => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'order_ids[]';
                    hiddenInput.value = id;
                    form.appendChild(hiddenInput);
                });
            }

            // Create a temporary form to submit
            const tempForm = form.cloneNode(true);
            tempForm.style.display = 'none';
            document.body.appendChild(tempForm);
            tempForm.submit();

            // Re-enable button after a delay
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = `
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export CSV
            `;
                document.body.removeChild(tempForm);
            }, 3000);
        });
    });
</script>
@endsection