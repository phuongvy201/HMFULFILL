@extends('layouts.admin')

@section('title', 'Customer Tier')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .modal-open {
        overflow: hidden;
    }
</style>
<script>
    function openEditModal(userId, currentTier, currentOrderCount, currentRevenue) {
        // Set form values
        const editTierModal = document.getElementById('editTierModal');
        const editTierForm = document.getElementById('editTierForm');
        const editTierSelect = document.getElementById('edit_tier');
        const editOrderCount = document.getElementById('edit_order_count');
        const editRevenue = document.getElementById('edit_revenue');
        const editMonth = document.getElementById('edit_month');
        const specialTierNote = document.getElementById('special_tier_note');
        const notesField = document.getElementById('edit_notes');

        editTierSelect.value = currentTier;
        editOrderCount.value = currentOrderCount;
        editRevenue.value = currentRevenue;
        editMonth.value = new Date().toISOString().slice(0, 7);
        editTierForm.action = `/admin/user-tiers/${userId}/update-tier`;

        // Toggle special tier note and notes requirement
        if (currentTier === 'Special') {
            specialTierNote.classList.remove('hidden');
            notesField.setAttribute('required', 'required');
        } else {
            specialTierNote.classList.add('hidden');
            notesField.removeAttribute('required');
        }

        // Show modal and prevent body scroll
        editTierModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
    }

    function closeEditModal() {
        const editTierModal = document.getElementById('editTierModal');
        editTierModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    // Wait for DOM to load
    document.addEventListener('DOMContentLoaded', function() {
        // Handle tier selection change
        document.getElementById('edit_tier').addEventListener('change', function(e) {
            const specialTierNote = document.getElementById('special_tier_note');
            const notesField = document.getElementById('edit_notes');
            if (e.target.value === 'Special') {
                specialTierNote.classList.remove('hidden');
                notesField.setAttribute('required', 'required');
            } else {
                specialTierNote.classList.add('hidden');
                notesField.removeAttribute('required');
            }
        });

        // Close modal when clicking outside
        document.getElementById('editTierModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Handle form submission
        document.getElementById('editTierForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const url = this.action;

            // Validate notes for Special tier
            if (formData.get('tier') === 'Special' && !formData.get('notes').trim()) {
                alert('Vui lòng nhập ghi chú khi set tier Special');
                return;
            }

            fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        closeEditModal();
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật tier');
                });
        });
    });
</script>
@endsection

@section('content-admin')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customer Tier</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Manage and track customer tiers</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form method="GET" action="{{ route('admin.user-tiers.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Search
                </label>
                <input type="text"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Name, email or phone..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="min-w-48">
                <label for="tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Filter by Tier
                </label>
                <select id="tier"
                    name="tier"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Tier</option>
                    @foreach($tiers as $key => $tier)
                    <option value="{{ $tier }}" {{ $tierFilter == $tier ? 'selected' : '' }}>
                        {{ $tier }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-32">
                <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Display
                </label>
                <select id="per_page"
                    name="per_page"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.user-tiers.index') }}"
                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Refresh
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        @php
        $totalCustomers = $customers->total();
        $woodCount = $customers->filter(fn($c) => $c['current_tier'] == 'Wood')->count();
        $silverCount = $customers->filter(fn($c) => $c['current_tier'] == 'Silver')->count();
        $goldCount = $customers->filter(fn($c) => $c['current_tier'] == 'Gold')->count();
        $diamondCount = $customers->filter(fn($c) => $c['current_tier'] == 'Diamond')->count();
        @endphp

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-700">
                    <i class="fas fa-users text-gray-600 dark:text-gray-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Customers</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalCustomers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-amber-100 dark:bg-amber-900">
                    <i class="fas fa-crown text-amber-600 dark:text-amber-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Diamond</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $diamondCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <i class="fas fa-star text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Gold</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $goldCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-700">
                    <i class="fas fa-medal text-gray-600 dark:text-gray-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Silver</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $silverCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Customer Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Email / SĐT
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Current Tier
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Revenue
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Effective Month
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tier Updated At
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ strtoupper(substr($customer['customer_name'], 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $customer['customer_name'] }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        ID: {{ $customer['id'] }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $customer['email'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $customer['phone'] ?: 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $tierColors = [
                            'Diamond' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                            'Gold' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            'Silver' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                            'Wood' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                            'Special' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200'
                            ];
                            $tierColor = $tierColors[$customer['current_tier']] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tierColor }}">
                                @if($customer['current_tier'] == 'Diamond')
                                <i class="fas fa-crown mr-1"></i>
                                @elseif($customer['current_tier'] == 'Gold')
                                <i class="fas fa-star mr-1"></i>
                                @elseif($customer['current_tier'] == 'Silver')
                                <i class="fas fa-medal mr-1"></i>
                                @elseif($customer['current_tier'] == 'Special')
                                <i class="fas fa-gem mr-1"></i>
                                @else
                                <i class="fas fa-tree mr-1"></i>
                                @endif
                                {{ $customer['current_tier'] }}
                            </span>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $customer['current_tier_order_count'] }} đơn hàng
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($customer['revenue'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $customer['tier_effective_month'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $customer['tier_updated_at'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.user-tiers.show', $customer['id']) }}"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                                <button type="button"
                                    onclick="openEditModal({{ $customer['id'] }}, '{{ $customer['current_tier'] }}', {{ $customer['current_tier_order_count'] }}, {{ $customer['revenue'] }})"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 bg-transparent border-none cursor-pointer">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No data
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            {{ $customers->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Edit Tier Modal -->
<div id="editTierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Cập Nhật Tier</h3>
            <form id="editTierForm" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="edit_tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tier
                    </label>
                    <select id="edit_tier" name="tier" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="Wood">Wood</option>
                        <option value="Silver">Silver</option>
                        <option value="Gold">Gold</option>
                        <option value="Diamond">Diamond</option>
                        <option value="Special">Special</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="edit_order_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Số Đơn Hàng
                    </label>
                    <input type="number" id="edit_order_count" name="order_count" min="0" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div class="mb-4">
                    <label for="edit_revenue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Doanh Thu ($)
                    </label>
                    <input type="number" id="edit_revenue" name="revenue" min="0" step="0.01" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div class="mb-4">
                    <label for="edit_month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tháng Hiệu Lực
                    </label>
                    <input type="month" id="edit_month" name="month" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div id="special_tier_note" class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900 rounded-md hidden">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <i class="fas fa-info-circle mr-1"></i>
                        Tier Special là tier đặc biệt được set thủ công. Khách hàng sẽ được giữ tier này cho đến khi được thay đổi bởi admin.
                    </p>
                </div>

                <div class="mb-4">
                    <label for="edit_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Ghi Chú (bắt buộc cho Tier Special)
                    </label>
                    <textarea id="edit_notes" name="notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Ghi chú về việc cập nhật tier..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection