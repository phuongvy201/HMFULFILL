@extends('layouts.admin')

@section('title', 'Customer Tier Detail')

@section('content-admin')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customer Tier Detail</h1>
        <a href="{{ route('admin.user-tiers.index') }}" class="text-blue-600 hover:text-blue-800">← Back</a>
    </div>

    @if($customerDetails)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Customer Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="font-medium">Name:</label>
                <p>{{ $customerDetails['user']->first_name }} {{ $customerDetails['user']->last_name }}</p>
            </div>
            <div>
                <label class="font-medium">Email:</label>
                <p>{{ $customerDetails['user']->email }}</p>
            </div>
            <div>
                <label class="font-medium">Phone:</label>
                <p>{{ $customerDetails['user']->phone ?: 'N/A' }}</p>
            </div>
            <div>
                <label class="font-medium">Current Tier:</label>
                <p class="font-bold text-blue-600">{{ $customerDetails['current_tier'] ? $customerDetails['current_tier']->tier : 'Wood' }}</p>
            </div>
        </div>

        <!-- Current Tier Information -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold mb-3">Current Tier Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="font-medium text-sm text-gray-600">Tier:</label>
                    <p class="text-lg font-bold">{{ $customerDetails['current_tier'] ? $customerDetails['current_tier']->tier : 'Wood' }}</p>
                </div>
                <div>
                    <label class="font-medium text-sm text-gray-600">Order Count:</label>
                    <p class="text-lg">{{ $customerDetails['current_tier'] ? $customerDetails['current_tier']->order_count : 0 }}</p>
                </div>
                <div>
                    <label class="font-medium text-sm text-gray-600">Revenue:</label>
                    <p class="text-lg font-bold text-green-600">${{ number_format($customerDetails['current_tier'] ? $customerDetails['current_tier']->revenue : 0, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Previous Month Statistics -->
        <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold mb-3">Previous Month Statistics ({{ $customerDetails['previous_month'] }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="font-medium text-sm text-gray-600">Order Count:</label>
                    <p class="text-lg">{{ $customerDetails['previous_month_order_count'] }}</p>
                </div>
                <div>
                    <label class="font-medium text-sm text-gray-600">Revenue:</label>
                    <p class="text-lg font-bold text-green-600">${{ number_format($customerDetails['previous_month_revenue'], 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Order Statistics by Status -->
        @if($customerDetails['order_stats']->count() > 0)
        <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold mb-3">Order Statistics by Status ({{ $customerDetails['previous_month'] }})</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-600">
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Count</th>
                            <th class="px-4 py-2 text-left">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customerDetails['order_stats'] as $stat)
                        <tr class="border-b">
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    @if($stat->status == 'processed') bg-green-100 text-green-800
                                    @elseif($stat->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($stat->status == 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($stat->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $stat->count }}</td>
                            <td class="px-4 py-2 font-bold">${{ number_format($stat->total_revenue ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Tier History -->
        <h3 class="text-lg font-semibold mb-3">Tier History</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-600">
                        <th class="px-4 py-2 text-left">Month</th>
                        <th class="px-4 py-2 text-left">Tier</th>
                        <th class="px-4 py-2 text-left">Order Count</th>
                        <th class="px-4 py-2 text-left">Revenue</th>
                        <th class="px-4 py-2 text-left">Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customerDetails['tier_history'] as $history)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $history->month->format('m/Y') }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                @if($history->tier == 'Diamond') bg-purple-100 text-purple-800
                                @elseif($history->tier == 'Gold') bg-yellow-100 text-yellow-800
                                @elseif($history->tier == 'Silver') bg-gray-100 text-gray-800
                                @else bg-amber-100 text-amber-800
                                @endif">
                                {{ $history->tier }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ $history->order_count }}</td>
                        <td class="px-4 py-2 font-bold text-green-600">${{ number_format($history->revenue, 2) }}</td>
                        <td class="px-4 py-2">{{ $history->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        No customer information found
    </div>
    @endif
</div>
@endsection