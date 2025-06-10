@extends('layouts.admin')

@section('title', 'API Order')

@section('content-admin')
<style>
    .max-w-full {
        position: relative;
    }

    .max-w-full::-webkit-scrollbar {
        height: 8px;
        position: sticky;
        bottom: 0;
        z-index: 50;
    }

    .max-w-full::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
        position: sticky;
        bottom: 0;
    }

    .max-w-full::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
        position: sticky;
        bottom: 0;
    }

    .max-w-full::-webkit-scrollbar-thumb:hover {
        background: rgb(219, 219, 219);
    }

    .table-wrapper {
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
    }

    .table-container {
        position: relative;
        margin-bottom: 20px;
    }

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

    .table-content {
        padding-bottom: 20px;
    }
</style>

<main>
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: `API Order`}">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName"></h2>

                <nav>
                    <ol class="flex items-center gap-1.5">
                        <li>
                            <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="/customer/orders">
                                Home
                                <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                        </li>
                        <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName"></li>
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
                            API Order List
                        </h3>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-wrapper">
                        <div class="table-content">
                            <table class="w-full min-w-[1102px] border-collapse bg-white">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="border-r px-6 py-4 text-left min-w-[150px]">
                                            <span class="text-sm font-semibold text-gray-900">Status</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[150px]">
                                            <span class="text-sm font-semibold text-gray-900">Order Number</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Total Amount</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Created At</span>
                                        </th>
                                        <th class="border-r px-6 py-4 text-left min-w-[120px]">
                                            <span class="text-sm font-semibold text-gray-900">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($orders as $order)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200" data-order-id="{{ $order->id }}">
                                        <td class="border-r px-6 py-4">
                                            @switch($order->status)
                                            @case('pending')
                                            <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-700">
                                                {{ $order->status }}
                                            </span>
                                            @break
                                            @case('processed')
                                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-theme-xs font-medium text-green-700">
                                                {{ $order->status }}
                                            </span>
                                            @break
                                            @case('cancelled')
                                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-theme-xs font-medium text-red-700">
                                                {{ $order->status }}
                                            </span>
                                            @break
                                            @default
                                            <span class="rounded-full bg-gray-50 px-2 py-0.5 text-theme-xs font-medium text-gray-700">
                                                {{ $order->status }}
                                            </span>
                                            @endswitch
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm font-medium text-gray-900">{{ $order->external_id }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">${{ number_format($order->total_price, 2) }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
                                        </td>
                                        <td class="border-r px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('customer.orders.show', $order->id) }}"
                                                    class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                    View
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
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
                <div class="px-4 py-3">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function cancelOrder(orderId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to cancel this order? This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                // Gọi API hủy đơn hàng
                fetch(`/api/orders/${orderId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Cancelled!',
                                'Order has been cancelled successfully.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message || 'Failed to cancel order.',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while cancelling the order.',
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
    });
</script>
@endsection