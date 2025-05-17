@extends('layouts.admin')

@section('content-admin')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Balance Details - {{ $user->first_name }} {{ $user->last_name }}`}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2
                class="text-xl font-semibold text-gray-800 dark:text-white/90"
                x-text="pageName"></h2>

            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                            href="{{ route('admin.finance.balance-overview') }}">
                            Balance Overview
                            <svg
                                class="stroke-current"
                                width="17"
                                height="16"
                                viewBox="0 0 17 16"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                                    stroke=""
                                    stroke-width="1.2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                    </li>
                    <li
                        class="text-sm text-gray-800 dark:text-white/90"
                        x-text="pageName"></li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <div class="space-y-5 sm:space-y-6">
        <!-- User Info Card -->
        <div class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-4">Account Information</h3>
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="p-5">
                    <div class="flex flex-wrap gap-5">
                        <div class="flex-1">
                            <p class="text-gray-700 dark:text-gray-300"><strong>User ID:</strong> {{ $user->id }}</p>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Email:</strong> {{ $user->email }}</p>
                        </div>
                        <div class="flex-1 text-right">
                            @php
                            $wallet = $user->wallet;
                            $totalBalance = $wallet ? $wallet->balance : 0;
                            @endphp
                            <p class="text-gray-700 dark:text-gray-300"><strong>Total Balance:</strong> {{ number_format($totalBalance, 0, ',', '.') }} VND</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Transaction History Card -->
        <div class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-4">Transaction History</h3>
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full">
                        <thead class="border-gray-100 border-y bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            ID
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Transaction Code
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Type
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Method
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Amount (VND)
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Status
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Note
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Created At
                                        </p>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            {{ $transaction->id }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            {{ $transaction->transaction_code }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            @if($transaction->type == 'topup')
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Top Up</span>
                                            @elseif($transaction->type == 'deduct')
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Deduct</span>
                                            @elseif($transaction->type == 'payment')
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Payment</span>
                                            @elseif($transaction->type == 'refund')
                                            <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-purple-900 dark:text-purple-300">Refund</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            {{ $transaction->method ?? '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            @if($transaction->status == 'pending')
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Pending</span>
                                            @elseif($transaction->status == 'approved' || $transaction->status == 'completed')
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Approved</span>
                                            @elseif($transaction->status == 'rejected' || $transaction->status == 'cancelled')
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Rejected</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            {{ $transaction->note ?? '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                    <div class="flex items-center justify-between gap-2 px-6 py-4 sm:justify-normal">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection