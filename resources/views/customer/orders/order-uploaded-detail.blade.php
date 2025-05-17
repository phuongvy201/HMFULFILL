@extends('layouts.customer')

@section('title', 'Order Detail')

@section('content-customer')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Breadcrumb -->
    <div x-data="{ pageName: 'Order Detail' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName"></h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li class="text-sm text-gray-800 dark:text-white/90">
                        <a href="/customer/orders" class="hover:text-blue-500">Orders List</a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName"></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="grid gap-4 mb-6 md:grid-cols-2 lg:grid-cols-3">
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Items</h3>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $orderStatistics['total_items'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Price</h3>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($orderStatistics['total_price'], 2) }}</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
            <p class="text-2xl font-semibold 
                @if($orderStatistics['status'] == 'pending') text-yellow-500 
                @elseif($orderStatistics['status'] == 'completed') text-green-500 
                @else text-red-500 @endif">
                {{ ucfirst($orderStatistics['status']) }}
            </p>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="bg-white rounded-lg shadow dark:bg-gray-800 mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Order ID</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->external_id }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->first_name }} {{ $order->last_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->phone ?? 'N/A' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Address</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ $order->address1 }}
                        @if($order->address2)
                        , {{ $order->address2 }}
                        @endif
                        , {{ $order->city }}
                        @if($order->county)
                        , {{ $order->county }}
                        @endif
                        @if($order->country)
                        , {{ $order->country }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Items List -->
    <div class="bg-white rounded-lg shadow dark:bg-gray-800 mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Part Number</th>
                            <th scope="col" class="px-6 py-3">Title</th>
                            <th scope="col" class="px-6 py-3">Quantity</th>
                            <th scope="col" class="px-6 py-3">Description</th>
                            <th scope="col" class="px-6 py-3">Mockup</th>
                            <th scope="col" class="px-6 py-3">Design</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $item->part_number }}</td>
                            <td class="px-6 py-4">{{ $item->title }}</td>
                            <td class="px-6 py-4">{{ $item->quantity }}</td>
                            <td class="px-6 py-4">{{ $item->description ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @if($item->mockups->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($item->mockups as $mockup)
                                    <a href="{{ $mockup->url }}" target="_blank" class="group">
                                        <div class="relative w-16 h-16 overflow-hidden rounded-lg border border-gray-200 hover:border-blue-500 transition-colors duration-200">
                                            @if(str_contains(strtolower($mockup->url), 'https://drive'))
                                            <img src="{{ asset('assets/images/google-drive.png') }}"
                                                alt="{{ $mockup->title }}"
                                                class="w-full h-full object-cover" />
                                            @else
                                            <img src="{{ $mockup->url }}"
                                                alt="{{ $mockup->title }}"
                                                class="w-full h-full object-cover" />
                                            @endif
                                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-1">
                                                <span class="text-xs text-white font-medium block truncate group-hover:whitespace-normal group-hover:overflow-visible group-hover:truncate-none">
                                                    {{ $mockup->title }}
                                                </span>

                                            </div>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                                @else
                                N/A
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item->designs->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($item->designs as $design)
                                    <a href="{{ $design->url }}" target="_blank" class="group">
                                        <div class="relative w-16 h-16 overflow-hidden rounded-lg border border-gray-200 hover:border-blue-500 transition-colors duration-200">
                                            @if(str_contains(strtolower($design->url), 'https://drive'))
                                            <img src="{{ asset('assets/images/google-drive.png') }}"
                                                alt="{{ $design->title }}"
                                                class="w-full h-full object-cover" />
                                            @else
                                            <img src="{{ $design->url }}"
                                                alt="{{ $design->title }}"
                                                class="w-full h-full object-cover" />
                                            @endif
                                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-1">
                                                <span class="text-xs text-white font-medium block truncate group-hover:whitespace-normal group-hover:overflow-visible group-hover:truncate-none">
                                                    {{ $design->title }}
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                                @else
                                N/A
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Fulfillment Information -->
    @if($order->fulfillment)
    <div class="bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Fulfillment Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Quantity</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->fulfillment->total_quantity }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Price</p>
                    <p class="font-medium text-gray-900 dark:text-white">${{ number_format($order->fulfillment->total_price, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->fulfillment->status }}</p>
                </div>
                @if($order->fulfillment->factory_response)
                <div class="col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Factory Response</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->fulfillment->factory_response }}</p>
                </div>
                @endif
                @if($order->fulfillment->error_message)
                <div class="col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Error Message</p>
                    <p class="font-medium text-red-500 dark:text-red-400">{{ $order->fulfillment->error_message }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection