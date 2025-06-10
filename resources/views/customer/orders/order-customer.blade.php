@extends('layouts.customer')

@section('title', 'Orders List')

@section('content-customer')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Breadcrumb -->
    <div x-data="{ pageName: 'Orders List' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName"></h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName"></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistics Cards -->
    <!-- <div class="grid gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</h3>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $statistics['total_orders'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Items</h3>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $statistics['total_items'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Orders</h3>
            <p class="text-2xl font-semibold text-yellow-500">{{ $statistics['pending_orders'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Processed Orders</h3>
            <p class="text-2xl font-semibold text-green-500">{{ $statistics['processed_orders'] }}</p>
        </div>
    </div> -->

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <form id="filterForm" class="flex gap-4">
                    <input type="text" name="external_id" placeholder="External ID" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400">

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input
                                type="date"
                                name="created_at_min"
                                id="datePickerMin"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-brand-500/10 focus:outline-none cursor-pointer"
                                onclick="this.showPicker()"
                                placeholder="Từ ngày">
                            <span class="absolute top-1/2 right-3 -translate-y-1/2 pointer-events-none">
                                <svg class="fill-gray-700" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.33317 0.0830078C4.74738 0.0830078 5.08317 0.418794 5.08317 0.833008V1.24967H8.9165V0.833008C8.9165 0.418794 9.25229 0.0830078 9.6665 0.0830078C10.0807 0.0830078 10.4165 0.418794 10.4165 0.833008V1.24967L11.3332 1.24967C12.2997 1.24967 13.0832 2.03318 13.0832 2.99967V4.99967V11.6663C13.0832 12.6328 12.2997 13.4163 11.3332 13.4163H2.6665C1.70001 13.4163 0.916504 12.6328 0.916504 11.6663V4.99967V2.99967C0.916504 2.03318 1.70001 1.24967 2.6665 1.24967L3.58317 1.24967V0.833008C3.58317 0.418794 3.91896 0.0830078 4.33317 0.0830078ZM4.33317 2.74967H2.6665C2.52843 2.74967 2.4165 2.8616 2.4165 2.99967V4.24967H11.5832V2.99967C11.5832 2.8616 11.4712 2.74967 11.3332 2.74967H9.6665H4.33317ZM11.5832 5.74967H2.4165V11.6663C2.4165 11.8044 2.52843 11.9163 2.6665 11.9163H11.3332C11.4712 11.9163 11.5832 11.8044 11.5832 11.6663V5.74967Z" fill="" />
                                </svg>
                            </span>
                        </div>

                        <span class="text-gray-500">-</span>

                        <div class="relative">
                            <input
                                type="date"
                                name="created_at_max"
                                id="datePickerMax"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-brand-500/10 focus:outline-none cursor-pointer"
                                onclick="this.showPicker()"
                                placeholder="Đến ngày">
                            <span class="absolute top-1/2 right-3 -translate-y-1/2 pointer-events-none">
                                <svg class="fill-gray-700" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.33317 0.0830078C4.74738 0.0830078 5.08317 0.418794 5.08317 0.833008V1.24967H8.9165V0.833008C8.9165 0.418794 9.25229 0.0830078 9.6665 0.0830078C10.0807 0.0830078 10.4165 0.418794 10.4165 0.833008V1.24967L11.3332 1.24967C12.2997 1.24967 13.0832 2.03318 13.0832 2.99967V4.99967V11.6663C13.0832 12.6328 12.2997 13.4163 11.3332 13.4163H2.6665C1.70001 13.4163 0.916504 12.6328 0.916504 11.6663V4.99967V2.99967C0.916504 2.03318 1.70001 1.24967 2.6665 1.24967L3.58317 1.24967V0.833008C3.58317 0.418794 3.91896 0.0830078 4.33317 0.0830078ZM4.33317 2.74967H2.6665C2.52843 2.74967 2.4165 2.8616 2.4165 2.99967V4.24967H11.5832V2.99967C11.5832 2.8616 11.4712 2.74967 11.3332 2.74967H9.6665H4.33317ZM11.5832 5.74967H2.4165V11.6663C2.4165 11.8044 2.52843 11.9163 2.6665 11.9163H11.3332C11.4712 11.9163 11.5832 11.8044 11.5832 11.6663V5.74967Z" fill="" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="flex justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        Filter
                    </button>
                </form>

            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">External ID</th>
                            <th scope="col" class="px-6 py-3">Created Date</th>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Tracking Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($excelOrders as $order)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $order->external_id }}</td>
                            <td class="px-6 py-4">{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4">
                                {{ $order->first_name . ' ' . $order->last_name }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                $status = strtolower($order->status);
                                @endphp
                                @if($status == 'pending')
                                <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-300">
                                    {{ ucfirst($status) }}
                                </span>
                                @elseif($status == 'processed')
                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-300">
                                    {{ ucfirst($status) }}
                                </span>
                                @elseif($status == 'on hold')
                                <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300">
                                    {{ ucfirst($status) }}
                                </span>
                                @else
                                <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">
                                    {{ ucfirst($status) }}
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $order->tracking_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('customer.orders.detail', ['externalId' => $order->external_id]) }}"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $excelOrders->links() }}
            </div>

        </div>
    </div>


</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');

        filterForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(filterForm);
            const searchParams = new URLSearchParams();

            // Thêm các tham số tìm kiếm vào URL
            for (const [key, value] of formData.entries()) {
                if (value) searchParams.append(key, value);
            }

            // Reload trang với các tham số tìm kiếm
            window.location.href = `${window.location.pathname}?${searchParams.toString()}`;
        });

        function updateStatistics(statistics) {
            document.getElementById('total-orders').textContent = statistics.total_orders;
            document.getElementById('total-items').textContent = statistics.total_items;
            document.getElementById('pending-orders').textContent = statistics.pending_orders;
            document.getElementById('processed-orders').textContent = statistics.processed_orders;
        }

        function updateOrdersList(orders) {
            const ordersContainer = document.getElementById('orders-container');
            ordersContainer.innerHTML = ''; // Xóa danh sách cũ

            orders.forEach(order => {
                const excelOrder = order.excel_order;
                const factoryOrder = order.factory_order;

                // Tạo HTML cho mỗi đơn hàng
                const orderHtml = `
                <div class="order-item">
                    <div class="order-header">
                        <h3>Đơn hàng: ${excelOrder.external_id}</h3>
                        <span class="status ${excelOrder.status}">${excelOrder.status}</span>
                    </div>
                    <div class="order-details">
                        <p>Ngày tạo: ${order.created_at}</p>
                        <p>Số lượng: ${excelOrder.items.reduce((sum, item) => sum + item.quantity, 0)}</p>
                        <!-- Thêm các thông tin khác tùy theo thiết kế của bạn -->
                    </div>
                </div>
            `;

                ordersContainer.insertAdjacentHTML('beforeend', orderHtml);
            });
        }
    });
</script>
@endpush