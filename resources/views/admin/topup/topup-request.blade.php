@extends('layouts.admin')

@section('title', 'Topup Requests')

@section('content-admin')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Topup Requests</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">View and manage your topup requests.</p>
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

        <div class="    overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full">
                    <thead>
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Transaction Code</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Amount</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Method</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Note</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">User</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">User ID</p>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topupRequests as $request)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-6 py-3.5">
                                <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $request->transaction_code }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-theme-sm text-success-600">{{ number_format($request->amount, 2) }} USD</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $request->method }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-theme-sm {{ $request->status == 'pending' ? 'text-warning-500' : ($request->status == 'approved' ? 'text-success-600' : 'text-error-500') }}">
                                    {{ ucfirst($request->status) }}
                                </p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $request->created_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    <img src="{{ $request->note }}" alt="Proof Image" class="w-20 h-20">
                                </p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $request->user->first_name ?? 'N/A' }} {{ $request->user->last_name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $request->user->id }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($request->status == 'pending')
                                <a href="javascript:void(0);"
                                    class="text-success-600 hover:text-success-800 mr-2 approve-link"
                                    data-id="{{ $request->id }}"
                                    data-url="{{ route('admin.topup.approve', $request->id) }}">Approve</a>
                                    <a href="javascript:void(0);"
                                        class="text-error-500 hover:text-error-700 reject-link"
                                        data-id="{{ $request->id }}"
                                        data-url="{{ route('admin.topup.reject', $request->id) }}">Reject</a>
                                    @else
                                <p class="text-theme-sm {{ $request->status == 'approved' ? 'text-success-600' : 'text-error-500' }}">
                                    {{ ucfirst($request->status) }}
                                </p>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">No topup requests found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 px-6 py-3 border-t border-gray-100 dark:border-gray-800">
                {{ $topupRequests->links() }}
            </div>
        </div>

        <script>
            // Xử lý sự kiện click cho Approve
            document.querySelectorAll('.approve-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Tránh double click
                    if (this.classList.contains('processing')) {
                        return;
                    }

                    this.classList.add('processing');

                    Swal.fire({
                        title: 'Confirm approval',
                        text: 'Are you sure you want to approve this topup request?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, approve!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Hiển thị loading
                            Swal.fire({
                                title: 'Processing...',
                                text: 'Please wait while we process your request.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            window.location.href = this.getAttribute('data-url');
                        } else {
                            // Remove processing class nếu cancel
                            this.classList.remove('processing');
                        }
                    });
                });
            });

            // Xử lý sự kiện click cho Reject
            document.querySelectorAll('.reject-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Tránh double click
                    if (this.classList.contains('processing')) {
                        return;
                    }

                    this.classList.add('processing');

                    Swal.fire({
                        title: 'Confirm rejection',
                        text: 'Are you sure you want to reject this topup request?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, reject!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Hiển thị loading
                            Swal.fire({
                                title: 'Processing...',
                                text: 'Please wait while we process your request.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            window.location.href = this.getAttribute('data-url');
                        } else {
                            // Remove processing class nếu cancel
                            this.classList.remove('processing');
                        }
                    });
                });
            });
        </script>
    </div>
</div>

<style>
    .processing {
        opacity: 0.6;
        pointer-events: none;
        cursor: not-allowed;
    }
</style>
@endsection