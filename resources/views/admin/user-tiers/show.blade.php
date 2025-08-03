@extends('layouts.admin')

@section('title', 'Chi tiết Tier Khách hàng')

@section('content-admin')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Chi tiết Tier Khách hàng</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Thông tin chi tiết về tier và lịch sử của khách hàng</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="openEditModal()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-edit mr-2"></i>Chỉnh sửa Tier
                </button>
                <a href="{{ route('admin.user-tiers.index') }}"
                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Quay lại
                </a>
            </div>
        </div>
    </div>

    <!-- Thông tin khách hàng -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Thông tin khách hàng</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600 dark:text-gray-400">Tên khách hàng:</p>
                <p class="text-gray-900 dark:text-white font-medium">
                    {{ $tierDetails['user']->first_name }} {{ $tierDetails['user']->last_name }}
                </p>
            </div>
            <div>
                <p class="text-gray-600 dark:text-gray-400">Email:</p>
                <p class="text-gray-900 dark:text-white font-medium">{{ $tierDetails['user']->email }}</p>
            </div>
            <div>
                <p class="text-gray-600 dark:text-gray-400">Số điện thoại:</p>
                <p class="text-gray-900 dark:text-white font-medium">{{ $tierDetails['user']->phone ?: 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 dark:text-gray-400">Ngày tham gia:</p>
                <p class="text-gray-900 dark:text-white font-medium">
                    {{ $tierDetails['user']->created_at->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Thông tin tier hiện tại -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Tier hiện tại</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600 dark:text-gray-400">Tier:</p>
                @php
                $tierColors = [
                'Diamond' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                'Gold' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                'Silver' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                'Wood' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                'Special' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200'
                ];
                $currentTier = $tierDetails['current_tier'] ? $tierDetails['current_tier']->tier : 'Wood';
                $tierColor = $tierColors[$currentTier] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tierColor }}">
                    @if($currentTier == 'Diamond')
                    <i class="fas fa-crown mr-1"></i>
                    @elseif($currentTier == 'Gold')
                    <i class="fas fa-star mr-1"></i>
                    @elseif($currentTier == 'Silver')
                    <i class="fas fa-medal mr-1"></i>
                    @elseif($currentTier == 'Special')
                    <i class="fas fa-gem mr-1"></i>
                    @else
                    <i class="fas fa-tree mr-1"></i>
                    @endif
                    {{ $currentTier }}
                </span>
            </div>
            <div>
                <p class="text-gray-600 dark:text-gray-400">Số đơn hàng:</p>
                <p class="text-gray-900 dark:text-white font-medium">
                    {{ $tierDetails['current_tier'] ? $tierDetails['current_tier']->order_count : 0 }}
                </p>
            </div>
            <div>
                <p class="text-gray-600 dark:text-gray-400">Doanh thu:</p>
                <p class="text-gray-900 dark:text-white font-medium">
                    ${{ number_format($tierDetails['current_tier'] ? $tierDetails['current_tier']->revenue : 0, 2) }}
                </p>
            </div>
            <div>
                <p class="text-gray-600 dark:text-gray-400">Tháng hiệu lực:</p>
                <p class="text-gray-900 dark:text-white font-medium">
                    {{ $tierDetails['current_month'] }}
                </p>
            </div>
        </div>
    </div>

    <!-- Thống kê đơn hàng -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Thống kê đơn hàng</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Trạng thái
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Số lượng
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Doanh thu
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($tierDetails['order_stats'] as $stat)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $stat->status }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $stat->count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($stat->total_revenue, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lịch sử tier -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Lịch sử tier</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tháng
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tier
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Số đơn hàng
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Doanh thu
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($tierDetails['tier_history'] as $history)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $history->month->format('m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $tierColor = $tierColors[$history->tier] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tierColor }}">
                                @if($history->tier == 'Diamond')
                                <i class="fas fa-crown mr-1"></i>
                                @elseif($history->tier == 'Gold')
                                <i class="fas fa-star mr-1"></i>
                                @elseif($history->tier == 'Silver')
                                <i class="fas fa-medal mr-1"></i>
                                @elseif($history->tier == 'Special')
                                <i class="fas fa-gem mr-1"></i>
                                @else
                                <i class="fas fa-tree mr-1"></i>
                                @endif
                                {{ $history->tier }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $history->order_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($history->revenue, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Tier Modal -->
    <div id="editTierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Cập Nhật Tier</h3>
                <form id="editTierForm" method="POST" action="{{ route('admin.user-tiers.update-tier', $tierDetails['user']->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="edit_tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tier
                        </label>
                        <select id="edit_tier" name="tier" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="Wood" {{ $currentTier == 'Wood' ? 'selected' : '' }}>Wood</option>
                            <option value="Silver" {{ $currentTier == 'Silver' ? 'selected' : '' }}>Silver</option>
                            <option value="Gold" {{ $currentTier == 'Gold' ? 'selected' : '' }}>Gold</option>
                            <option value="Diamond" {{ $currentTier == 'Diamond' ? 'selected' : '' }}>Diamond</option>
                            <option value="Special" {{ $currentTier == 'Special' ? 'selected' : '' }}>Special</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="edit_order_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Số Đơn Hàng
                        </label>
                        <input type="number" id="edit_order_count" name="order_count" min="0" required
                            value="{{ $tierDetails['current_tier'] ? $tierDetails['current_tier']->order_count : 0 }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="mb-4">
                        <label for="edit_revenue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Doanh Thu ($)
                        </label>
                        <input type="number" id="edit_revenue" name="revenue" min="0" step="0.01" required
                            value="{{ $tierDetails['current_tier'] ? $tierDetails['current_tier']->revenue : 0 }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="mb-4">
                        <label for="edit_month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tháng Hiệu Lực
                        </label>
                        <input type="month" id="edit_month" name="month" required
                            value="{{ $tierDetails['current_month'] }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div id="special_tier_note" class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900 rounded-md {{ $currentTier == 'Special' ? '' : 'hidden' }}">
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
</div>

@push('scripts')
<script>
    function openEditModal() {
        document.getElementById('editTierModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editTierModal').classList.add('hidden');
    }

    // Xử lý khi thay đổi tier
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

    // Đóng modal khi click bên ngoài
    document.getElementById('editTierModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    // Xử lý form submit
    document.getElementById('editTierForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const url = this.action;

        // Kiểm tra nếu là Special tier thì phải có ghi chú
        if (formData.get('tier') === 'Special' && !formData.get('notes').trim()) {
            alert('Vui lòng nhập ghi chú khi set tier Special');
            return;
        }

        fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật tier');
            });
    });
</script>
@endpush
@endsection