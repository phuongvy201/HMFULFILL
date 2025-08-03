@extends('layouts.admin')

@section('title', 'Refund History')

@section('content-admin')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Refund History</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">View history of all refund transactions.</p>
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

        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
            </div>
            <form method="GET" action="{{ route('admin.refund.history') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        User
                    </label>
                    <select name="user_id" class="block w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500 dark:text-white dark:focus:ring-blue-900/50">
                        <option value="">All Users</option>
                        @foreach(App\Models\User::select('id', 'first_name', 'last_name', 'email')->orderBy('first_name')->get() as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                        </option>
                        @endforeach
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
                <div class="md:col-span-3 flex items-center space-x-3 pt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.refund.history') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset
                    </a>
                    <a href="{{ route('admin.refund.index') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Refunds
                    </a>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-xl transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Refunds</dt>
                        <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $refunds->total() }}</dd>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-xl transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/30 dark:to-green-800/30 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</dt>
                        <dd class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($refunds->sum('amount'), 2) }}</dd>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-xl transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 6v6m0 0v-6m0 6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">This Month</dt>
                        <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $refunds->where('created_at', '>=', now()->startOfMonth())->count() }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refunds Table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full">
                    <thead>
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Transaction ID    </p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Transaction Code</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">User</p>
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
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Approved By</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($refunds as $refund)
                        <tr class="border-t border-gray-100 dark:border-gray-800">  
                            <td class="px-6 py-3.5">
                                <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $refund->id }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $refund->transaction_code }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    {{ $refund->user->first_name ?? 'N/A' }} {{ $refund->user->last_name ?? 'N/A' }}
                                    <br>
                                    <span class="text-xs text-gray-400">{{ $refund->user->email ?? 'N/A' }}</span>
                                </p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-theme-sm text-red-600 font-medium">
                                    +${{ number_format($refund->amount, 2) }}
                                </p>
                            </td>

                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $refund->created_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $refund->note ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    @if($refund->approved_by)
                                    {{ App\Models\User::find($refund->approved_by)->first_name ?? 'N/A' }} {{ App\Models\User::find($refund->approved_by)->last_name ?? '' }}
                                    @else
                                    N/A
                                    @endif
                                </p>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">No refund transactions found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($refunds->hasPages())
            <div class="mt-4 px-6 py-3 border-t border-gray-100 dark:border-gray-800">
                {{ $refunds->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection