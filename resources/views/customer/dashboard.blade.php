@extends('layouts.customer')

@php
$page = 'dashboard';
@endphp

@section('styles')
<link rel="stylesheet" href="{{ asset('css/customer-dashboard.css') }}">
@endsection

@section('content-customer')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Personal Dashboard</h1>
                    <p class="mt-2 text-sm text-gray-600">Welcome back, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}!</p>
                    @if($period == 'custom')
                    <p class="text-xs text-blue-600 font-medium">
                        Statistics from {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </p>
                    @else
                    <p class="text-xs text-blue-600 font-medium">
                        Statistics:
                        @if($period == 'day') Today
                        @elseif($period == 'week') This Week
                        @elseif($period == 'month') This Month
                        @elseif($period == 'year') This Year
                        @endif
                    </p>
                    @endif
                </div>

                <!-- Period Filter -->
                <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <label for="periodFilter" class="text-sm font-medium text-gray-900">Time Range:</label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <!-- Period Selector -->
                        <select id="periodFilter"
                            class="w-40 px-3 py-2 bg-white border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200">
                            <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>

                        <!-- Custom Date Range -->
                        <div id="customDateRange"
                            class="flex flex-col sm:flex-row items-start sm:items-center gap-2 {{ $period == 'custom' ? 'flex' : 'hidden' }} transition-opacity duration-200">
                            <div class="relative">
                                <input type="text"
                                    id="startDate"
                                    name="start_date"
                                    placeholder="dd/mm/yyyy"
                                    value="{{ $period == 'custom' && request('start_date') ? request('start_date') : '' }}"
                                    class="w-40 px-3 py-2 bg-white border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200">
                            </div>
                            <span class="text-sm text-gray-600 font-medium">to</span>
                            <div class="relative">
                                <input type="text"
                                    id="endDate"
                                    name="end_date"
                                    placeholder="dd/mm/yyyy"
                                    value="{{ $period == 'custom' && request('end_date') ? request('end_date') : '' }}"
                                    class="w-40 px-3 py-2 bg-white border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200">
                            </div>
                            <button id="applyCustomDate"
                                type="button"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                                Apply
                            </button>
                            @if($orderDateRange)
                            <div class="text-xs text-gray-500 ml-2">
                                <span>Available: {{ $orderDateRange['first_order_date'] }} - {{ $orderDateRange['last_order_date'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <script>
                    document.getElementById('periodFilter').addEventListener('change', function() {
                        const customDateRange = document.getElementById('customDateRange');
                        if (this.value === 'custom') {
                            customDateRange.classList.remove('hidden');
                            customDateRange.classList.add('flex');
                        } else {
                            customDateRange.classList.add('hidden');
                            customDateRange.classList.remove('flex');
                        }
                    });
                </script>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-8">
            <!-- Total Orders -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalOrders) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Spending -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Spending</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($totalSpending, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Items -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Items</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalItems) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Order Value -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Average Order Value</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($averageOrderValue, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Balance -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Wallet Balance</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($walletBalance, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tier Information -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 shadow rounded-lg mb-8">
            <div class="p-6">
                <div class="flex items-center justify-between text-white mb-4">
                    <div>
                        <h3 class="text-lg font-medium">Tier Information</h3>
                        <p class="text-sm opacity-90">{{ $tierInfo['tier_name'] }}
                            @if($tierInfo['expected_tier'] !== $tierInfo['tier_name'])
                            (Should be: {{ $tierInfo['expected_tier'] }})
                            @endif
                        </p>
                        @if($tierInfo['tier_month'])
                        <p class="text-xs opacity-80">Effective from {{ $tierInfo['tier_month'] }} </p>
                        @endif
                    </div>

                </div>

                <!-- Progress to next tier -->
                @if($tierInfo['next_tier'])
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-white opacity-90 mb-2">
                        <span>Progress to {{ $tierInfo['next_tier'] }}</span>
                        <span>{{ number_format($tierInfo['orders_needed']) }} orders left</span>
                    </div>
                    <div class="w-full bg-white bg-opacity-20 rounded-full h-2">
                        <div id="tierProgress" class="bg-white h-2 rounded-full transition-all duration-300" data-progress="{{ $tierInfo['progress_to_next'] }}"></div>
                    </div>
                </div>
                @else
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-white opacity-90 mb-2">
                        <span>You've reached the highest tier!</span>
                        <span>{{ number_format($tierInfo['current_month_orders']) }} orders this month</span>
                    </div>
                    <div class="w-full bg-white bg-opacity-20 rounded-full h-2">
                        <div id="tierProgress" class="bg-white h-2 rounded-full transition-all duration-300" data-progress="100"></div>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <p class="text-sm opacity-90">Spent This Month</p>
                        <p class="text-lg font-semibold">${{ number_format($tierInfo['current_month_spent'], 2) }}</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <p class="text-sm opacity-90">Orders This Month</p>
                        <p class="text-lg font-semibold">{{ number_format($tierInfo['current_month_orders']) }}</p>
                    </div>
                </div>

                <!-- Tier Benefits -->
                <div class="mt-4 pt-4 border-t border-white border-opacity-20">
                    <h4 class="text-sm font-medium text-white mb-2">Tier Benefits {{ $tierInfo['tier_name'] }}:</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs text-white opacity-90">

                        @if($tierInfo['tier_name'] !== 'Wood')
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Exclusive benefits for {{ $tierInfo['tier_name'] }} members – enjoy special discounted prices on all products
                        </div>
                        @endif
                        @if($tierInfo['tier_name'] === 'Diamond' || $tierInfo['tier_name'] === 'Gold')
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            VIP Support
                        </div>
                        @endif
                        @if($tierInfo['tier_name'] === 'Diamond')
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Free Shipping
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Daily Spending Chart -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Last 7 Days Spending</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-80">
                            <canvas id="dailySpendingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status Chart -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Order Status</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64 mb-4">
                            <canvas id="statusPieChart"></canvas>
                        </div>
                        <div class="space-y-2">
                            @foreach($orderStatusStats as $status => $count)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                                    <span class="text-gray-700">{{ ucfirst($status) }}</span>
                                </div>
                                <span class="font-medium text-gray-900">{{ number_format($count) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Orders</h3>
                </div>
                <div class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $order->external_id }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->items->count() }} items</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if($order->status == 'completed') bg-green-100 text-green-800
                                            @elseif($order->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($order->status == 'processing') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Most Bought Products</h3>
                </div>
                <div class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($topProducts as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm text-gray-500">{{ $product->part_number }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($product->total_quantity) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($product->total_spent, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Set tier progress bar width
    document.addEventListener('DOMContentLoaded', function() {
        const progressBar = document.getElementById('tierProgress');
        if (progressBar) {
            const progress = progressBar.getAttribute('data-progress');
            progressBar.style.width = progress + '%';
        }
    });

    // Chart data from PHP
    const chartData = JSON.parse('{!! json_encode($chartData) !!}');

    // Daily Spending Chart
    const dailyCtx = document.getElementById('dailySpendingChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: chartData.daily.labels,
            datasets: [{
                label: 'Spending ($)',
                data: chartData.daily.spending,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Orders',
                data: chartData.daily.orders,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Spending ($)'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Orders'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            }
        }
    });

    // Status Pie Chart
    const statusCtx = document.getElementById('statusPieChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.status.labels,
            datasets: [{
                data: chartData.status.data,
                backgroundColor: [
                    '#3B82F6', '#10B981', '#06B6D4', '#F59E0B', '#EF4444'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Period filter change
    document.getElementById('periodFilter').addEventListener('change', function() {
        const period = this.value;
        const customDateRange = document.getElementById('customDateRange');

        if (period === 'custom') {
            customDateRange.classList.remove('hidden');
        } else {
            customDateRange.classList.add('hidden');
            window.location.href = '{{ route("customer.dashboard") }}?period=' + period;
        }
    });

    // Validate dd/mm/yyyy format
    function isValidDateFormat(dateString) {
        const regex = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
        return regex.test(dateString);
    }

    // Parse dd/mm/yyyy to Date object
    function parseDate(dateString) {
        if (!isValidDateFormat(dateString)) return null;
        const parts = dateString.split('/');
        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1; // Month is 0-indexed
        const year = parseInt(parts[2], 10);
        return new Date(year, month, day);
    }

    // Format Date to dd/mm/yyyy
    function formatDate(date) {
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Auto-format date input
    function setupDateFormatting(inputId) {
        const input = document.getElementById(inputId);
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            if (value.length >= 5) {
                value = value.substring(0, 5) + '/' + value.substring(5, 9);
            }
            e.target.value = value;
        });
    }

    // Setup date formatting for both inputs
    setupDateFormatting('startDate');
    setupDateFormatting('endDate');

    // Apply custom date range
    document.getElementById('applyCustomDate').addEventListener('click', function() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!startDate || !endDate) {
            alert('Vui lòng nhập cả ngày bắt đầu và ngày kết thúc (định dạng: dd/mm/yyyy)');
            return;
        }

        if (!isValidDateFormat(startDate) || !isValidDateFormat(endDate)) {
            alert('Định dạng ngày không đúng. Vui lòng sử dụng định dạng dd/mm/yyyy');
            return;
        }

        const startDateObj = parseDate(startDate);
        const endDateObj = parseDate(endDate);

        if (!startDateObj || !endDateObj) {
            alert('Ngày không hợp lệ. Vui lòng kiểm tra lại');
            return;
        }

        if (startDateObj > endDateObj) {
            alert('Ngày bắt đầu không thể lớn hơn ngày kết thúc');
            return;
        }

        const url = '{{ route("customer.dashboard") }}?period=custom&start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate);
        window.location.href = url;
    });

    // Set default dates for custom range
    if (document.getElementById('periodFilter').value === 'custom') {
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        if (!startDateInput.value) {
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
            startDateInput.value = formatDate(thirtyDaysAgo);
        }

        if (!endDateInput.value) {
            const today = new Date();
            endDateInput.value = formatDate(today);
        }
    }
</script>
@endsection