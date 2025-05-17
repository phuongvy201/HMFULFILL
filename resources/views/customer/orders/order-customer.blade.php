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
    <div class="grid gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
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
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed Orders</h3>
            <p class="text-2xl font-semibold text-green-500">{{ $statistics['completed_orders'] }}</p>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Orders List</h3>
                <a href="/customer/orders/upload"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Create New Order
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Order ID</th>
                            <th scope="col" class="px-6 py-3">Created Date</th>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Quantity</th>
                            <th scope="col" class="px-6 py-3">Total Price</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $file)
                        @foreach($file->excelOrders as $order)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $order->external_id }}</td>
                            <td class="px-6 py-4">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">{{ $order->first_name }} {{ $order->last_name }}</td>
                            <td class="px-6 py-4">
                                {{ $order->items->sum('quantity') }}
                            </td>
                            <td class="px-6 py-4">
                                ${{ number_format($order->fulfillment->total_price ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($order->status == 'pending')
                                <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-300">
                                    Pending
                                </span>
                                @elseif($order->status == 'completed')
                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-300">
                                    Completed
                                </span>
                                @else
                                <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">
                                    Failed
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('customer.orders.detail', $order->id) }}"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection