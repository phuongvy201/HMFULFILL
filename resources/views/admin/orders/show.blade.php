@extends('layouts.admin')

@section('title', 'Order Details #' . $order->external_id)

@section('content-admin')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between mb-6 mt-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                <li>
                    <a href="{{ route('admin.statistics.dashboard') }}" class="text-gray-400 hover:text-gray-500">
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ route('admin.api-orders') }}" class="ml-4 text-gray-400 hover:text-gray-500">
                            API Orders List
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-4 text-gray-500">Order Details #{{ $order->external_id }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Order Status -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->external_id }}</h1>
                        <p class="text-sm text-gray-500 mt-1">Created by: {{ $order->creator->email }}</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($order->status === 'processed') bg-green-100 text-green-800
                            @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                            @elseif($order->status === 'on hold') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Order Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Order Information</h3>
            </div>
            <div class="px-6 py-4">
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">External ID:</dt>
                        <dd class="text-sm text-gray-900">{{ $order->external_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Warehouse:</dt>
                        <dd class="text-sm text-gray-900">{{ $order->warehouse }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Created Date:</dt>
                        <dd class="text-sm text-gray-900">{{ $order->created_at->format('m/d/Y H:i:s') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Last Updated:</dt>
                        <dd class="text-sm text-gray-900">{{ $order->updated_at->format('m/d/Y H:i:s') }}</dd>
                    </div>
                    @if($transaction)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Transaction:</dt>
                        <dd class="text-sm text-gray-900">
                            <div>ID: {{ $transaction->id }}</div>
                            <div>Amount: ${{ number_format($transaction->amount, 2) }}</div>
                            <div>Status: {{ $transaction->status }}</div>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Customer Information</h3>
            </div>
            <div class="px-6 py-4">
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Full Name:</dt>
                        <dd class="text-sm text-gray-900">{{ $order->first_name }} {{ $order->last_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Email:</dt>
                        <dd class="text-sm text-gray-900">{{ $order->buyer_email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Phone:</dt>
                        <dd class="text-sm text-gray-900">{{ $order->phone1 }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Address:</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $order->address1 }}<br>
                            @if($order->address2){{ $order->address2 }}<br>@endif
                            {{ $order->city }}, {{ $order->county }}<br>
                            {{ $order->post_code }}<br>
                            {{ $order->country }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Label:</dt>
                        <dd class="text-sm text-gray-900">
                            <a href="{{ $order->link }}" target="_blank">{{ $order->comment ?? $order->comment }}</a>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Product Details</h3>
        </div>
        <div class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Part Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->part_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($item->print_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($item->print_price * $item->quantity, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                Total Amount:
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">
                                ${{ number_format($totalAmount, 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Designs & Mockups -->
    @php
    $allDesigns = collect();
    $allMockups = collect();

    foreach($order->items as $item) {
    foreach($item->designs as $design) {
    $allDesigns->push([
    'title' => $design->title,
    'url' => $design->url,
    'part_number' => $item->part_number,
    'item_title' => $item->title,
    ]);
    }

    foreach($item->mockups as $mockup) {
    $allMockups->push([
    'title' => $mockup->title,
    'url' => $mockup->url,
    'part_number' => $item->part_number,
    'item_title' => $item->title,
    ]);
    }
    }
    @endphp

    @if($allDesigns->count() > 0 || $allMockups->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Designs & Mockups</h3>
        </div>
        <div class="px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Designs Section -->
                @if($allDesigns->count() > 0)
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 21h12a2 2 0 002-2v-4a2 2 0 00-2-2H7m0 8V9a2 2 0 012-2h6a2 2 0 012 2V21a4 4 0 01-4 4H7z" />
                        </svg>
                        Design Files ({{ $allDesigns->count() }})
                    </h4>
                    <div class="space-y-4">
                        @foreach($allDesigns as $design)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ $design['url'] }}"
                                        alt="{{ $design['title'] }}"
                                        class="w-20 h-20 object-cover rounded-lg border-2 border-gray-100 cursor-pointer hover:border-blue-300 transition-colors image-modal-trigger"
                                        data-url="{{ $design['url'] }}"
                                        data-title="{{ $design['title'] }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-sm font-medium text-gray-900 truncate">{{ $design['title'] }}</h5>
                                    <p class="text-xs text-gray-500 mt-1">{{ $design['part_number'] }}</p>
                                    @if($design['item_title'])
                                    <p class="text-xs text-gray-400 mt-1">{{ $design['item_title'] }}</p>
                                    @endif
                                    <a href="{{ $design['url'] }}"
                                        target="_blank"
                                        class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 mt-2">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        View Original
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Mockups Section -->
                @if($allMockups->count() > 0)
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Mockup Files ({{ $allMockups->count() }})
                    </h4>
                    <div class="space-y-4">
                        @foreach($allMockups as $mockup)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ $mockup['url'] }}"
                                        alt="{{ $mockup['title'] }}"
                                        class="w-20 h-20 object-cover rounded-lg border-2 border-gray-100 cursor-pointer hover:border-green-300 transition-colors image-modal-trigger"
                                        data-url="{{ $mockup['url'] }}"
                                        data-title="{{ $mockup['title'] }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-sm font-medium text-gray-900 truncate">{{ $mockup['title'] }}</h5>
                                    <p class="text-xs text-gray-500 mt-1">{{ $mockup['part_number'] }}</p>
                                    @if($mockup['item_title'])
                                    <p class="text-xs text-gray-400 mt-1">{{ $mockup['item_title'] }}</p>
                                    @endif
                                    <a href="{{ $mockup['url'] }}"
                                        target="_blank"
                                        class="inline-flex items-center text-xs text-green-600 hover:text-green-800 mt-2">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        View Original
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
    @endif
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-medium text-gray-900"></h3>
            <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="text-center">
            <img id="modalImage" src="" alt="" class="max-w-full h-auto rounded-lg">
        </div>
    </div>
</div>

<script>
    function openImageModal(url, title) {
        document.getElementById('modalImage').src = url;
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }

    // Event delegation for image modal triggers
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('image-modal-trigger')) {
            const url = e.target.getAttribute('data-url');
            const title = e.target.getAttribute('data-title');
            openImageModal(url, title);
        }
    });

    // Close modal when clicking outside
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
</script>
@endsection