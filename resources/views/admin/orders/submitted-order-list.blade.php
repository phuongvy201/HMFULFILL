@extends('layouts.admin')

@section('title', 'Submit Orders')

@section('content-admin')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Submit Orders List`}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2
                class="text-xl font-semibold text-gray-800 dark:text-white/90"
                x-text="pageName"></h2>

            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                            href="index.html">
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

    <div class="space-y-5 sm:space-y-6">

        <div
            class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            <!-- ====== Table Start -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">


                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <form id="filterForm" class="flex flex-wrap gap-3">
                            <div class="relative">
                                <input type="text" name="ids" placeholder="Order ID"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[200px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>


                            <div class="relative">
                                <input
                                    id="created_at_min"
                                    type="date"
                                    name="created_at_min"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    onclick="this.showPicker()">
                                <span class="absolute top-1/2 right-3.5 -translate-y-1/2">
                                    <svg class="fill-gray-700 dark:fill-gray-400" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.33317 0.0830078C4.74738 0.0830078 5.08317 0.418794 5.08317 0.833008V1.24967H8.9165V0.833008C8.9165 0.418794 9.25229 0.0830078 9.6665 0.0830078C10.0807 0.0830078 10.4165 0.418794 10.4165 0.833008V1.24967L11.3332 1.24967C12.2997 1.24967 13.0832 2.03318 13.0832 2.99967V4.99967V11.6663C13.0832 12.6328 12.2997 13.4163 11.3332 13.4163H2.6665C1.70001 13.4163 0.916504 12.6328 0.916504 11.6663V4.99967V2.99967C0.916504 2.03318 1.70001 1.24967 2.6665 1.24967L3.58317 1.24967V0.833008C3.58317 0.418794 3.91896 0.0830078 4.33317 0.0830078ZM4.33317 2.74967H2.6665C2.52843 2.74967 2.4165 2.8616 2.4165 2.99967V4.24967H11.5832V2.99967C11.5832 2.8616 11.4712 2.74967 11.3332 2.74967H9.6665H4.33317ZM11.5832 5.74967H2.4165V11.6663C2.4165 11.8044 2.52843 11.9163 2.6665 11.9163H11.3332C11.4712 11.9163 11.5832 11.8044 11.5832 11.6663V5.74967Z" fill="" />
                                    </svg>
                                </span>
                            </div>

                            <div class="relative">
                                <input
                                    id="created_at_max"
                                    type="date"
                                    name="created_at_max"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    onclick="this.showPicker()">
                                <span class="absolute top-1/2 right-3.5 -translate-y-1/2">
                                    <svg class="fill-gray-700 dark:fill-gray-400" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.33317 0.0830078C4.74738 0.0830078 5.08317 0.418794 5.08317 0.833008V1.24967H8.9165V0.833008C8.9165 0.418794 9.25229 0.0830078 9.6665 0.0830078C10.0807 0.0830078 10.4165 0.418794 10.4165 0.833008V1.24967L11.3332 1.24967C12.2997 1.24967 13.0832 2.03318 13.0832 2.99967V4.99967V11.6663C13.0832 12.6328 12.2997 13.4163 11.3332 13.4163H2.6665C1.70001 13.4163 0.916504 12.6328 0.916504 11.6663V4.99967V2.99967C0.916504 2.03318 1.70001 1.24967 2.6665 1.24967L3.58317 1.24967V0.833008C3.58317 0.418794 3.91896 0.0830078 4.33317 0.0830078ZM4.33317 2.74967H2.6665C2.52843 2.74967 2.4165 2.8616 2.4165 2.99967V4.24967H11.5832V2.99967C11.5832 2.8616 11.4712 2.74967 11.3332 2.74967H9.6665H4.33317ZM11.5832 5.74967H2.4165V11.6663C2.4165 11.8044 2.52843 11.9163 2.6665 11.9163H11.3332C11.4712 11.9163 11.5832 11.8044 11.5832 11.6663V5.74967Z" fill="" />
                                    </svg>
                                </span>
                            </div>

                            <div class="relative">
                                <select name="status" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[150px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                    <option value="">Status</option>
                                    <option value="created">Created</option>
                                    <option value="processing_payment">Processing Payment</option>
                                    <option value="paid">Paid</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="returned">Returned</option>
                                    <option value="in_progress">In Progress</option>
                                </select>
                            </div>


                            <button type="submit" class="text-theme-sm shadow-theme-xs inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                <svg class="stroke-current fill-white dark:fill-gray-800" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5"></path>
                                    <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5"></path>
                                </svg>
                                Filter
                            </button>
                        </form>
                    </div>
                </div>

                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full">
                        <!-- table header start -->
                        <thead class="border-gray-100 border-y bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                            id="select-all"
                                            class="h-5 w-5 rounded-md border-gray-300 cursor-pointer">
                                        <label for="select-all" class="ml-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Order ID
                                        </label>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            External ID
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Created At
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Status
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Total
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Tracking Number
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Payment
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Actions
                                        </p>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <!-- table header end -->

                        <!-- table body start -->
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($orders as $order)
                            <tr>
                                {{-- ID --}}
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                            class="order-checkbox h-5 w-5 rounded-lg border-gray-300 cursor-pointer"
                                            value="{{ $order['id'] }}">
                                        <span class="ml-3 block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $order['id'] }}
                                        </span>
                                    </div>
                                </td>

                                {{-- External ID --}}
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            {{ $order['external_id'] ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            {{ $order['created_at'] }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Trạng thái đơn hàng --}}
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $order['status'] }}
                                        </p>
                                    </div>
                                </td>

                                {{-- Tổng tiền (đã gồm shipping & VAT) --}}
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                            £{{ $order['summary']['total'] ?? '0.00' }}
                                        </p>
                                    </div>
                                </td>

                                {{-- Tracking number --}}
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $order['shipping']['trackingNumber'] ?? 'N/A' }}
                                        </p>
                                    </div>
                                </td>

                                {{-- Thanh toán --}}
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="text-theme-sm dark:text-gray-400 {{ !empty($order['payment']['paid_at']) ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }} px-2 py-1 rounded">
                                            {{ !empty($order['payment']['paid_at']) ? 'Paid' : 'Unpaid' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <a href="{{ route('admin.submitted-order-detail', ['id' => $order['id']]) }}" class="text-blue-500 hover:text-blue-700" title="View Order">
                                            <!-- Biểu tượng con mắt (Eye icon) -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>


                            </tr>
                            @endforeach
                        </tbody>

                        <!-- table body end -->
                    </table>
                </div>
                <!-- Phân trang -->
                <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                    <div class="flex items-center justify-between gap-2 px-6 py-4 sm:justify-normal">
                        @if ($orders->hasPages())
                        <!-- Hiển thị các liên kết phân trang -->
                        {{ $orders->links() }}
                        @else
                        <p class="text-gray-500 dark:text-gray-400">No data to paginate.</p>
                        @endif
                    </div>
                </div>
            </div>
            <!-- ====== Table End -->
        </div>
    </div>
</div>
@endsection

{{-- Thêm script xử lý --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const orderCheckboxes = document.querySelectorAll('.order-checkbox');

        // Xử lý chọn tất cả
        selectAllCheckbox.addEventListener('change', function() {
            orderCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Xử lý form submit
        const filterForm = document.getElementById('filterForm');
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const params = new URLSearchParams();

            // Chuyển đổi FormData thành query string
            for (let [key, value] of formData.entries()) {
                if (value) {
                    // Xử lý đặc biệt cho các trường ngày tháng
                    if (key === 'created_at_min' || key === 'created_at_max') {
                        // Chuyển đổi sang định dạng ISO
                        const date = new Date(value);
                        if (!isNaN(date.getTime())) {
                            // Format: YYYY-MM-DDTHH:mm:ss.sssZ
                            params.append(key, date.toISOString());
                        }
                    } else {
                        params.append(key, value);
                    }
                }
            }

            // Chuyển hướng với query params
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        });
    });
</script>
@endpush