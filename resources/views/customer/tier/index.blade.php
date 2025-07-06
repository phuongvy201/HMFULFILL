@extends('layouts.customer')

@section('title', 'Tier Information')

@section('content-customer')
<div class="w-full px-4">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2 sm:mb-0">Tier Information</h1>
        <nav class="flex text-sm text-gray-500">
            <a href="{{ route('customer.dashboard') }}" class="hover:text-gray-700">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">Tier</span>
        </nav>
    </div>

    <!-- Current Tier Overview -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-6 shadow-lg">
            <div class="flex flex-col md:flex-row md:items-center">
                <div class="flex items-center mb-4 md:mb-0 md:mr-8">
                    <div class="bg-white bg-opacity-20 rounded-full p-4 mr-4">
                        @if($currentTier && $currentTier->tier == 'Diamond')
                        <i class="fas fa-gem text-3xl text-yellow-300"></i>
                        @elseif($currentTier && $currentTier->tier == 'Gold')
                        <i class="fas fa-trophy text-3xl text-yellow-300"></i>
                        @elseif($currentTier && $currentTier->tier == 'Silver')
                        <i class="fas fa-medal text-3xl text-gray-200"></i>
                        @else
                        <i class="fas fa-seedling text-3xl text-green-300"></i>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold mb-1">{{ $currentTier ? $currentTier->tier : 'Wood' }}</h2>
                        <p class="text-blue-100 mb-1">Your current tier</p>
                        <p class="text-sm text-blue-100">
                            Effective from: {{ $currentTier ? $currentTier->month->format('d/m/Y') : $currentMonth->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 md:ml-auto">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold mb-1">{{ number_format($currentMonthOrders) }}</h3>
                        <p class="text-sm text-blue-100">Orders this month</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-bold mb-1">${{ number_format($currentMonthRevenue, 2) }}</h3>
                        <p class="text-sm text-blue-100">Spending this month</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress to Next Tier -->
    @php
    // Tính toán tier tiếp theo dựa trên số đơn hàng từ đầu tháng đến nay
    $currentMonthOrdersForTier = $currentMonthOrders;

    if ($currentMonthOrdersForTier < 1500) {
        $nextTier='Silver' ;
        $threshold=1500;
        } elseif ($currentMonthOrdersForTier < 4500) {
        $nextTier='Gold' ;
        $threshold=4500;
        } elseif ($currentMonthOrdersForTier < 9000) {
        $nextTier='Diamond' ;
        $threshold=9000;
        } else {
        $nextTier=null; // Đã đạt tier cao nhất
        $threshold=9000;
        }

        $ordersNeeded=$nextTier ? $threshold - $currentMonthOrdersForTier : 0;
        $progressPercent=$threshold> 0 ? ($currentMonthOrdersForTier / $threshold) * 100 : 0;
        $progressPercent = min($progressPercent, 100);
        @endphp

        @if($nextTier)
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Progress to {{ $nextTier }} Tier</h2>
                <div class="flex flex-col md:flex-row md:items-center">
                    <div class="flex-1 mb-4 md:mb-0 md:mr-6">
                        <div class="w-full bg-gray-200 rounded-full h-8 mb-3">
                            <div class="bg-green-500 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium progress-bar"
                                data-progress="{{ $progressPercent }}">
                                {{ number_format($progressPercent, 1) }}%
                            </div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Current: {{ number_format($currentMonthOrdersForTier) }} orders</span>
                            <span>Target: {{ number_format($threshold) }} orders</span>
                        </div>
                    </div>
                    <div class="text-center md:min-w-32">
                        @if($ordersNeeded > 0)
                        <h3 class="text-2xl font-bold text-blue-600 mb-1">{{ number_format($ordersNeeded) }}</h3>
                        <p class="text-sm text-gray-500">orders needed</p>
                        @else
                        <h3 class="text-2xl font-bold text-green-600 mb-1">✓ Target reached</h3>
                        <p class="text-sm text-gray-500">Congratulations!</p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <span class="text-blue-800">
                            @if($ordersNeeded > 0)
                            You need {{ number_format($ordersNeeded) }} more orders to reach {{ $nextTier }} tier
                            @else
                            Congratulations! You have met the requirements for {{ $nextTier }} tier!
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-crown text-6xl text-yellow-500 mb-4"></i>
                <h2 class="text-2xl font-bold text-yellow-600 mb-2">Congratulations! You've reached the highest tier</h2>
                <p class="text-gray-600">You have achieved Diamond tier - the highest tier in our system with {{ number_format($currentMonthOrdersForTier) }} orders this month</p>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Tier Benefits -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $currentTier ? $currentTier->tier : 'Wood' }} Tier Benefits</h2>
                </div>
                <div class="p-4">
                    <ul class="space-y-3">
                        @php
                        $currentTierName = $currentTier ? $currentTier->tier : 'Wood';
                        @endphp
            
                        @if($currentTierName === 'Wood')
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span class="text-gray-700">Basic pricing according to price list</span>
                        </li>
                        @endif

                        @if($currentTierName === 'Silver')
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span class="text-gray-700">Exclusive benefits for Silver members – enjoy special discounted prices on all products</span>
                        </li>
                        @endif

                        @if($currentTierName === 'Gold')
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3 flex-shrink-0"></i> 
                            <span class="text-gray-700">Exclusive benefits for Gold members – enjoy special discounted prices on all products</span>
                        </li>
                        
                        @endif

                        @if($currentTierName === 'Diamond')
                      
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span class="text-gray-700">Exclusive benefits for Diamond members – enjoy special discounted prices on all products</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Revenue Summary -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Spending Statistics</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Spending {{ $currentMonth->format('m/Y') }}:</span>
                        <span class="font-semibold text-blue-600">${{ number_format($currentMonthRevenue, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total spending:</span>
                        <span class="font-semibold text-green-600">${{ number_format($totalRevenue, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Orders this month:</span>
                        <span class="font-semibold text-indigo-600">{{ number_format($currentMonthOrders) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tier History -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Tier History (completed months)</h2>
                </div>
                <div class="p-4">
                    @php
                    // Temporarily show all history for debug
                    $completedTierHistory = $tierHistory;

                    // Original logic (temporarily commented)
                    // $completedTierHistory = $tierHistory->filter(function($history) use ($currentMonth) {
                    // return !$history->month->isSameMonth($currentMonth);
                    // });
                    @endphp

                    {{-- Debug info --}}
                    @if(config('app.debug'))
                    <div class="mb-4 p-3 bg-gray-100 rounded text-sm">
                        <strong>Debug Info:</strong><br>
                        User ID: {{ Auth::user()->id }}<br>
                        Current month: {{ $currentMonth->format('m/Y') }}<br>

                    </div>
                    @endif

                    @if($completedTierHistory->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Month</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Tier</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Orders</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Spending</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($completedTierHistory as $history)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-700">{{ $history->month->format('m/Y') }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($history->tier == 'Diamond') bg-yellow-100 text-yellow-800
                                        @elseif($history->tier == 'Gold') bg-yellow-100 text-yellow-800
                                        @elseif($history->tier == 'Silver') bg-gray-100 text-gray-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                            {{ $history->tier }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-700">{{ number_format($history->order_count) }}</td>
                                    <td class="py-3 px-4 text-gray-700">${{ number_format($history->revenue, 2) }}</td>
                                    <td class="py-3 px-4">
                                        @if($history->month->isSameMonth($currentMonth))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Current</span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-history text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-500 mb-2">No tier history available</h3>
                        <p class="text-gray-400">Tier history will be displayed after completing the first month</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tier Guide -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Tier Guide</h2>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-seedling text-4xl text-green-500 mb-3"></i>
                            <h3 class="font-semibold text-gray-900 mb-1">Wood</h3>
                            <p class="text-sm text-gray-600">0 - 1,499 orders/month or less than 50 orders/day</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-medal text-4xl text-gray-500 mb-3"></i>
                            <h3 class="font-semibold text-gray-900 mb-1">Silver</h3>
                            <p class="text-sm text-gray-600">1,500 - 4,499 orders/month or 50 orders/day</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-trophy text-4xl text-yellow-500 mb-3"></i>
                            <h3 class="font-semibold text-gray-900 mb-1">Gold</h3>
                            <p class="text-sm text-gray-600">4,500 - 8,999 orders/month or 150 orders/day</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-gem text-4xl text-blue-500 mb-3"></i>
                            <h3 class="font-semibold text-gray-900 mb-1">Diamond</h3>
                            <p class="text-sm text-gray-600">9,000+ orders/month or 300 orders/day</p>
                        </div>
                    </div>
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <span class="text-blue-800">
                                <strong>Note:</strong> Tiers are calculated based on monthly orders. Tiers will be automatically updated at the beginning of each month based on previous month's performance. Progress tracking shows your current month's orders towards the next tier.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            const progress = progressBar.getAttribute('data-progress');
            progressBar.style.width = progress + '%';
        }
    });
</script>