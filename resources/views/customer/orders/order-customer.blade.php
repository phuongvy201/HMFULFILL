@extends('layouts.customer')

@section('title', 'Orders List')

@section('content-customer')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Notice:</strong>
            <span class="block sm:inline">
                A 20% VAT will be applied to all FF UK orders in accordance with UK government regulations.
            </span>
        </div>

    </div>
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

    <!-- Export Form -->
    <div class="bg-white rounded-lg shadow dark:bg-gray-800 mb-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">Export Orders</h3>
            <form id="exportForm" action="{{ route('customer.orders.export') }}" method="POST" class="flex flex-wrap gap-3">
                @csrf
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input
                            type="date"
                            name="date_from"
                            id="dateFrom"
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
                            name="date_to"
                            id="dateTo"
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

                <select
                    name="status"
                    class="h-10 w-full max-w-xs rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:outline-none">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="processed">Processed</option>
                    <option value="shipped">Shipped</option>
                    <option value="on hold">On Hold</option>
                </select>

                <select
                    name="export_format"
                    class="h-10 w-full max-w-xs rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:outline-none">
                    <option value="csv">CSV</option>
                    <option value="tsv">TSV</option>
                </select>

                <button type="submit" class="flex justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    Export Data
                </button>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <form id="filterForm" class="flex gap-4">
                    <input type="text" name="external_id" placeholder="Mã đơn hàng" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400">

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input
                                type="date"
                                name="created_at_min"
                                id="datePickerMin"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-brand-500/10 focus:outline-none cursor-pointer"
                                onclick="this.showPicker()"
                                placeholder="From Date">
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
                                placeholder="To Date">
                            <span class="absolute top-1/2 right-3 -translate-y-1/2 pointer-events-none">
                                <svg class="fill-gray-700" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.33317 0.0830078C4.74738 0.0830078 5.08317 0.418794 5.08317 0.833008V1.24967H8.9165V0.833008C8.9165 0.418794 9.25229 0.0830078 9.6665 0.0830078C10.0807 0.0830078 10.4165 0.418794 10.4165 0.833008V1.24967L11.3332 1.24967C12.2997 1.24967 13.0832 2.03318 13.0832 2.99967V4.99967V11.6663C13.0832 12.6328 12.2997 13.4163 11.3332 13.4163H2.6665C1.70001 13.4163 0.916504 12.6328 0.916504 11.6663V4.99967V2.99967C0.916504 2.03318 1.70001 1.24967 2.6665 1.24967L3.58317 1.24967V0.833008C3.58317 0.418794 3.91896 0.0830078 4.33317 0.0830078ZM4.33317 2.74967H2.6665C2.52843 2.74967 2.4165 2.8616 2.4165 2.99967V4.24967H11.5832V2.99967C11.5832 2.8616 11.4712 2.74967 11.3332 2.74967H9.6665H4.33317ZM11.5832 5.74967H2.4165V11.6663C2.4165 11.8044 2.52843 11.9163 2.6665 11.9163H11.3332C11.4712 11.9163 11.5832 11.8044 11.5832 11.6663V5.74967Z" fill="" />
                                </svg>
                            </span>
                        </div>
                    </div>
                    <select name="status" class="h-10 w-full max-w-xs rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processed">Processed</option>
                        <option value="shipped">Shipped</option>
                        <option value="on hold">On Hold</option>
                    </select>

                    <button type="submit" class="flex justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Order ID</th>
                            <th scope="col" class="px-6 py-3">Created At</th>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Tracking Number</th>
                            <th scope="col" class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($excelOrders as $order)
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
                                <div class="flex items-center gap-2">
                                    @if($order->external_id)
                                    <a href="{{ route('customer.orders.detail', ['externalId' => urlencode($order->external_id)]) }}"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        View Details
                                    </a>
                                    @else
                                    <span class="text-gray-400">No Details</span>
                                    @endif

                                    @if($order->status === 'on hold')
                                    <button class="cancel-order-btn px-3 py-1 text-sm font-medium text-red-600 bg-red-100 border border-red-200 rounded-md hover:bg-red-200 hover:text-red-800 transition-colors"
                                        data-order-id="{{ $order->id }}"
                                        data-external-id="{{ $order->external_id }}">
                                        Cancel
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No orders found matching the selected filters.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
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
        const exportForm = document.getElementById('exportForm');

        // Handle filter form submission
        filterForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(filterForm);
            const searchParams = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value) searchParams.append(key, value);
            }

            window.location.href = `${window.location.pathname}?${searchParams.toString()}`;
        });

        // Handle export form submission
        exportForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(exportForm);
            const response = await fetch(exportForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok && response.headers.get('content-type').includes('text/csv')) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = response.headers.get('content-disposition')?.split('filename=')[1] || 'orders_export.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } else {
                const data = await response.json();
                alert(data.message || 'An error occurred while exporting orders.');
            }
        });

        // Handle cancel order button clicks
        document.querySelectorAll('.cancel-order-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                const externalId = this.dataset.externalId;

                Swal.fire({
                    title: 'Bạn có chắc chắn muốn hủy đơn hàng?',
                    text: `Đơn hàng ${externalId} sẽ được hủy và hoàn tiền về tài khoản của bạn.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Hủy đơn hàng',
                    cancelButtonText: 'Không'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Đang xử lý...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch(`/customer/orders/${orderId}/cancel`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Thành công!',
                                        text: data.message + (data.refund_amount ? ` Số tiền hoàn: $${data.refund_amount}` : ''),
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Lỗi!',
                                        text: data.message || 'Có lỗi xảy ra khi hủy đơn hàng',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: 'Có lỗi xảy ra khi hủy đơn hàng',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            });
                    }
                });
            });
        });
    });
</script>
@endpush