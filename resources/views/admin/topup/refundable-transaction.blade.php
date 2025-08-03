@extends('layouts.admin')

@section('title', 'Refund Transactions')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content-admin')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Refund Transactions</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Refund transactions and manage refund requests.</p>
        </div>

        @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded dark:bg-green-900/20 dark:border-green-800 dark:text-green-300" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded dark:bg-red-900/20 dark:border-red-800 dark:text-red-300" role="alert">
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        @endif

        <!-- Thêm CDN cho SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Tabs -->
        <div class="mb-6">
            <nav class="flex space-x-8 bg-white dark:bg-gray-800 rounded-lg p-1 shadow-sm" aria-label="Tabs">
                <button onclick="showTab('refund-transactions')" id="tab-refund-transactions" class="tab-button active flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 bg-blue-600 text-white shadow-sm">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Refund Transactions
                </button>
                <button onclick="showTab('custom-refund')" id="tab-custom-refund" class="tab-button flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Custom Refund
                </button>
            </nav>
        </div>

        <!-- Tab 1: Refund Transactions -->
        <div id="refund-transactions-tab" class="tab-content">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Refundable</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $transactions->total() }}</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($transactions->sum('amount'), 2) }}</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Topup Transactions</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $transactions->where('type', 'topup')->count() }}</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Deduct Transactions</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $transactions->where('type', 'deduct')->count() }}</dd>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Filters -->
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
                </div>
                <form method="GET" action="{{ route('admin.refund.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            User
                        </label>
                        <select name="user_id" class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Type
                        </label>
                        <select name="type" class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50">
                            <option value="">All Types</option>
                            <option value="topup" {{ request('type') == 'topup' ? 'selected' : '' }}>Topup</option>
                            <option value="deduct" {{ request('type') == 'deduct' ? 'selected' : '' }}>Deduct</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 6v6m0 0v-6m0 6h6m-6 0H6"></path>
                            </svg>
                            From Date
                        </label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 6v6m0 0v-6m0 6h6m-6 0H6"></path>
                            </svg>
                            To Date
                        </label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Transaction Code
                        </label>
                        <input type="text" name="transaction_code" value="{{ request('transaction_code') }}" placeholder="Search by code..." class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50 placeholder-gray-400 dark:placeholder-gray-400">
                    </div>
                    <div class="md:col-span-5 flex items-center space-x-3 pt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('admin.refund.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset
                        </a>
                        <a href="{{ route('admin.refund.history') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            View History
                        </a>
                    </div>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">

                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Transaction ID</p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Transaction Code</p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">User</p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Type</p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Amount</p>
                                </th>
                              
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Note</p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                            <tr class="border-t border-gray-100 dark:border-gray-800">

                                <td class="px-6 py-3.5">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $transaction->id }}</p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $transaction->transaction_code }}</p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $transaction->user->first_name ?? 'N/A' }} {{ $transaction->user->last_name ?? 'N/A' }}
                                        <br>
                                        <span class="text-xs text-gray-400">{{ $transaction->user->email ?? 'N/A' }}</span>
                                    </p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $transaction->type == 'topup' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="text-theme-sm {{ $transaction->type == 'topup' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type == 'topup' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} USD
                                    </p>
                                </td>
                            
                                <td class="px-6 py-3.5">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $transaction->created_at->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $transaction->note ?? 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <button data-transaction-id="{{ $transaction->id }}"
                                        class="refund-btn inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path>
                                        </svg>
                                        Refund
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">No refundable transactions found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                <div class="mt-4 px-6 py-3 border-t border-gray-100 dark:border-gray-800">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Tab 2: Custom Refund -->
        <div id="custom-refund-tab" class="tab-content hidden">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-6">
                    <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Create Custom Refund</h3>
                </div>

                <form id="custom-refund-form" onsubmit="submitCustomRefund(event)">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Select User *
                            </label>
                            <select id="refund-user-id" name="user_id" required class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50">
                                <option value="">Select a user...</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                Amount (USD) *
                            </label>
                            <input type="number" id="refund-amount" name="amount" step="0.01" min="0.01" required
                                class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Payment Method *
                            </label>
                            <select id="refund-method" name="method" required class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50">
                                <option value="">Select payment method...</option>
                                <option value="Bank VN">Bank VN</option>
                                <option value="Payoneer">Payoneer</option>
                                <option value="PingPong">PingPong</option>
                                <option value="LianLianPay">LianLianPay</option>
                                <option value="Worldfirst">Worldfirst</option>
                                <option value="Paypal">Paypal</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                User Info
                            </label>
                            <div id="user-info" class="p-4 bg-gradient-to-br from-blue-50 via-blue-25 to-indigo-50 dark:from-gray-800 dark:via-gray-750 dark:to-gray-700 rounded-xl text-sm border border-blue-200 dark:border-gray-600 shadow-inner transition-all duration-200">
                                <div class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Select a user to view their information
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Refund Note *
                            </label>
                            <textarea id="refund-note" name="refund_note" rows="4" required
                                class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50 placeholder-gray-400 dark:placeholder-gray-400 resize-y"
                                placeholder="Enter reason for refund..."></textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 text-white font-medium text-sm rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200 shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Create Refund
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching functionality
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active', 'bg-blue-600', 'text-white', 'shadow-sm');
            button.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
        });

        // Show selected tab
        document.getElementById(tabName + '-tab').classList.remove('hidden');

        // Add active class to selected button
        const activeButton = document.getElementById('tab-' + tabName);
        activeButton.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
        activeButton.classList.add('active', 'bg-blue-600', 'text-white', 'shadow-sm');
    }

    // Add event listeners for refund buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Refund button click handlers
        document.querySelectorAll('.refund-btn').forEach(button => {
            button.addEventListener('click', function() {
                const transactionId = this.getAttribute('data-transaction-id');
                refundTransaction(transactionId);
            });
        });
    });

    // Refund transaction function
    function refundTransaction(transactionId) {
        Swal.fire({
            title: 'Refund Transaction',
            html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-2">Refund Note (optional)</label>
                <textarea id="refund-note-input" class="w-full p-2 border border-gray-300 rounded-md" rows="3" placeholder="Enter refund note..."></textarea>
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: 'Confirm Refund',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            preConfirm: () => {
                return document.getElementById('refund-note-input').value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const refundNote = result.value;

                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we process the refund.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Make AJAX request
                fetch(`/admin/refund/transaction/${transactionId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            refund_note: refundNote
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while processing the refund.',
                            icon: 'error'
                        });
                    });
            }
        });
    }

    // Get user info when user is selected
    document.getElementById('refund-user-id').addEventListener('change', function() {
        const userId = this.value;
        if (userId) {
            fetch(`/admin/refund/user/${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('user-info').innerHTML = `
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="font-medium text-gray-700 dark:text-gray-200">${data.user.name}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">${data.user.email}</span>
                            </div>
                            <div class="bg-white dark:bg-gray-600 rounded-lg p-3 border border-gray-200 dark:border-gray-500">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Balance</span>
                                    <span class="font-semibold text-green-600 dark:text-green-400">$${data.user.balance}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Available Balance</span>
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">$${data.user.available_balance}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    } else {
                        document.getElementById('user-info').innerHTML = `
                            <div class="flex items-center text-red-500">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Error loading user info
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('user-info').innerHTML = `
                        <div class="flex items-center text-red-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Error loading user info
                        </div>
                    `;
                });
        } else {
            document.getElementById('user-info').innerHTML = `
                <div class="flex items-center text-gray-500 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Select a user to view their information
                </div>
            `;
        }
    });

    // Submit custom refund
    function submitCustomRefund(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData);

        Swal.fire({
            title: 'Confirm Refund',
            html: `
            <div class="text-left">
                <p><strong>User:</strong> ${document.getElementById('refund-user-id').selectedOptions[0].textContent}</p>
                <p><strong>Amount:</strong> $${data.amount}</p>
                <p><strong>Method:</strong> ${data.method}</p>
                <p><strong>Note:</strong> ${data.refund_note}</p>
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: 'Confirm Refund',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we process the refund.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Make AJAX request
                fetch('/admin/refund/custom', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success'
                            }).then(() => {
                                // Reset form
                                event.target.reset();
                                document.getElementById('user-info').innerHTML = `
                            <div class="flex items-center text-gray-500 dark:text-gray-400">
                                <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Select a user to view their information
                            </div>
                        `;
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while processing the refund.',
                            icon: 'error'
                        });
                    });
            }
        });
    }
</script>

@endsection