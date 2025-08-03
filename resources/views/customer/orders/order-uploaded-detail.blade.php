@extends('layouts.customer')

@section('title', 'Order Detail')

@section('content-customer')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    
    <!-- VAT Warning Start -->
   
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
    <div class="bg-white rounded-lg shadow dark:bg-gray-800 mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Items</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $orderStatistics['total_items'] }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Price</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($orderStatistics['total_amount'], 2) }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <p class="text-2xl font-semibold">
                        @php
                        $status = strtolower($orderStatistics['status']);
                        @endphp
                        @if($status == 'pending')
                        <span class="text-yellow-500">{{ ucfirst($status) }}</span>
                        @elseif($status == 'processed')
                        <span class="text-green-500">{{ ucfirst($status) }}</span>
                        @elseif($status == 'on hold')
                        <span class="text-blue-500">{{ ucfirst($status) }}</span>
                        @else
                        <span class="text-red-500">{{ ucfirst($status) }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="bg-white rounded-lg shadow dark:bg-gray-800 mb-6">
        <div class="p-6">

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
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->buyer_email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->phone1 ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tracking Number</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->tracking_number ?? 'N/A' }}</p>
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
                            <th scope="col" class="px-6 py-3">Price</th>
                            <th scope="col" class="px-6 py-3">Description</th>
                            <th scope="col" class="px-6 py-3">Mockup</th>
                            <th scope="col" class="px-6 py-3">Design</th>
                            <th scope="col" class="px-6 py-3">Product</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $item->part_number }}</td>
                            <td class="px-6 py-4">{{ $item->title }}</td>
                            <td class="px-6 py-4">{{ $item->quantity }}</td>
                            <td class="px-6 py-4">
                                @if(isset($item->print_price))
                                ${{ number_format($item->print_price, 2) }}
                                @else
                                <span class="text-red-500">Price not available</span>
                                @endif
                            </td>
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
                            <td class="px-6 py-4">
                                @if($item->product)
                                <b>{{ $item->product->name }}</b>
                                @else
                                <b>Product not found</b>
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

</div>
@endsection