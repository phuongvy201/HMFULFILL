@extends('layouts.admin')


@section('content-admin')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tổng quan số dư người dùng</h3>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.finance.balance-overview') }}" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                placeholder="Tìm kiếm theo ID, tên hoặc email..."
                                value="{{ $search ?? '' }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
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
                                                    Tên
                                                </p>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Email
                                                </p>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Số dư (VND)
                                                </p>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                    Hành động
                                                </p>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($users as $user)
                                    <tr>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                                    {{ $user->id }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                                    {{ $user->email }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                                    {{ number_format($user->wallet->balance, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <a href="{{ route('admin.finance.user-balance', $user->id) }}"
                                                    class="text-blue-500 hover:text-blue-700" title="Xem chi tiết">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
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
                                {{ $users->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection