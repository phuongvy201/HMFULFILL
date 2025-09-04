@extends('layouts.admin')

@section('title', 'Quản lý Design Tasks')

@section('content-admin')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-palette mr-3 text-blue-600"></i>
            Quản lý Design Tasks
        </h1>
        <div class="flex space-x-3">
            <button onclick="exportCSV()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                <i class="fas fa-download mr-2"></i>
                Xuất CSV
            </button>
            <a href="{{ route('admin.design.dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                <i class="fas fa-chart-bar mr-2"></i>
                Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-tasks text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tổng cộng</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Đang chờ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-user-check text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Đã nhận</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['joined'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Hoàn thành</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-thumbs-up text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Đã duyệt</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <i class="fas fa-edit text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Cần sửa</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['revision'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Bộ lọc</h3>
        <form id="filterForm" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tất cả</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                    <option value="joined" {{ request('status') === 'joined' ? 'selected' : '' }}>Đã nhận</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="revision" {{ request('status') === 'revision' ? 'selected' : '' }}>Cần sửa</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>

            <!-- Designer Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Designer</label>
                <select name="designer_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">Tất cả designers</option>
                    @foreach($designers as $designer)
                        <option value="{{ $designer->id }}" {{ request('designer_id') == $designer->id ? 'selected' : '' }}>
                            {{ $designer->first_name }} {{ $designer->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Customer Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Khách hàng</label>
                <select name="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">Tất cả khách hàng</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->first_name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Từ ngày</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Đến ngày</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Price Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Giá tối thiểu ($)</label>
                <input type="number" name="price_min" value="{{ request('price_min') }}" step="0.01" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Giá tối đa ($)</label>
                <input type="number" name="price_max" value="{{ request('price_max') }}" step="0.01" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Action Buttons -->
            <div class="lg:col-span-2 flex space-x-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-search mr-2"></i>Lọc
                </button>
                <a href="{{ route('admin.design.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600">
                <label for="selectAll" class="text-sm font-medium text-gray-700">Chọn tất cả</label>
                
                <select id="bulkAction" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">Hành động hàng loạt</option>
                    <option value="delete">Xóa</option>
                    <option value="change_status">Thay đổi trạng thái</option>
                </select>

                <button onclick="executeBulkAction()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                    Thực hiện
                </button>
            </div>

            <div class="text-sm text-gray-600">
                Đã chọn <span id="selectedCount">0</span> tasks
            </div>
        </div>

        <!-- Status change for bulk action -->
        <div id="bulkStatusChange" class="hidden mt-3 flex items-center space-x-3">
            <label class="text-sm font-medium text-gray-700">Trạng thái mới:</label>
            <select id="newStatus" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="pending">Đang chờ</option>
                <option value="joined">Đã nhận</option>
                <option value="completed">Hoàn thành</option>
                <option value="approved">Đã duyệt</option>
                <option value="revision">Cần sửa</option>
                <option value="cancelled">Đã hủy</option>
            </select>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
                Danh sách Design Tasks ({{ $tasks->total() }})
            </h3>
        </div>

        @if($tasks->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAllHeader" class="rounded border-gray-300 text-blue-600">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tasks as $task)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="task-checkbox rounded border-gray-300 text-blue-600" value="{{ $task->id }}">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($task->mockup_file)
                                        <img class="h-10 w-10 rounded-lg object-cover" src="{{ $task->getMockupUrl() }}" alt="Mockup">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ Str::limit($task->title, 30) }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($task->description, 40) }}</div>
                                    <div class="text-xs text-gray-400">Số mặt: {{ $task->sides_count }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($task->status === 'joined') bg-blue-100 text-blue-800
                                @elseif($task->status === 'completed') bg-green-100 text-green-800
                                @elseif($task->status === 'approved') bg-purple-100 text-purple-800
                                @elseif($task->status === 'revision') bg-orange-100 text-orange-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $task->getStatusDisplayName() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                            ${{ number_format($task->price, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $task->customer->first_name }} {{ $task->customer->last_name }}</div>
                            <div class="text-sm text-gray-500">{{ $task->customer->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($task->designer)
                                <div class="text-sm text-gray-900">{{ $task->designer->first_name }} {{ $task->designer->last_name }}</div>
                                <div class="text-sm text-gray-500">{{ $task->designer->email }}</div>
                            @else
                                <span class="text-sm text-gray-400">Chưa có designer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $task->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.design.show', $task->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50 transition-colors action-icon">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="changeStatus({{ $task->id }})" 
                                        class="text-green-600 hover:text-green-900 p-2 rounded hover:bg-green-50 transition-colors action-icon">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteTask({{ $task->id }})" 
                                        class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 transition-colors action-icon">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $tasks->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-palette text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Không có design task nào</h3>
            <p class="text-gray-500">Không tìm thấy design task nào phù hợp với bộ lọc hiện tại.</p>
        </div>
        @endif
    </div>
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Thay đổi trạng thái</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="statusForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái mới</label>
                    <select id="statusSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pending">Đang chờ</option>
                        <option value="joined">Đã nhận</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="approved">Đã duyệt</option>
                        <option value="revision">Cần sửa</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200">
                        Hủy
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<style>
/* Đảm bảo icon hiển thị đúng */
.fas, .fa {
    display: inline-block;
    font-style: normal;
    font-variant: normal;
    text-rendering: auto;
    line-height: 1;
}

/* Hover effects cho các icon */
.action-icon {
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-icon:hover {
    transform: scale(1.1);
}
</style>
<script>
let currentTaskId = null;
let selectedTasks = new Set();

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.task-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        if (this.checked) {
            selectedTasks.add(checkbox.value);
        } else {
            selectedTasks.delete(checkbox.value);
        }
    });
    updateSelectedCount();
});

document.getElementById('selectAllHeader').addEventListener('change', function() {
    document.getElementById('selectAll').checked = this.checked;
    document.getElementById('selectAll').dispatchEvent(new Event('change'));
});

// Individual checkbox handling
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('task-checkbox')) {
        if (e.target.checked) {
            selectedTasks.add(e.target.value);
        } else {
            selectedTasks.delete(e.target.value);
        }
        updateSelectedCount();
        
        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.task-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.task-checkbox:checked');
        document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length;
        document.getElementById('selectAllHeader').checked = allCheckboxes.length === checkedCheckboxes.length;
    }
});

function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = selectedTasks.size;
}

// Bulk action handling
document.getElementById('bulkAction').addEventListener('change', function() {
    const statusChangeDiv = document.getElementById('bulkStatusChange');
    if (this.value === 'change_status') {
        statusChangeDiv.classList.remove('hidden');
    } else {
        statusChangeDiv.classList.add('hidden');
    }
});

function executeBulkAction() {
    const action = document.getElementById('bulkAction').value;
    if (!action) {
        alert('Vui lòng chọn hành động');
        return;
    }

    if (selectedTasks.size === 0) {
        alert('Vui lòng chọn ít nhất một task');
        return;
    }

    if (action === 'delete') {
        if (confirm(`Bạn có chắc muốn xóa ${selectedTasks.size} tasks đã chọn?`)) {
            performBulkAction('delete', Array.from(selectedTasks));
        }
    } else if (action === 'change_status') {
        const newStatus = document.getElementById('newStatus').value;
        if (confirm(`Bạn có chắc muốn thay đổi trạng thái ${selectedTasks.size} tasks thành "${newStatus}"?`)) {
            performBulkAction('change_status', Array.from(selectedTasks), newStatus);
        }
    }
}

function performBulkAction(action, taskIds, newStatus = null) {
    const data = {
        action: action,
        task_ids: taskIds,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    if (newStatus) {
        data.new_status = newStatus;
    }

    fetch('{{ route("admin.design.bulk-actions") }}', {
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
            showNotification(data.message, 'success');
            // Reload page after successful bulk action
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra', 'error');
    });
}

// Status change modal
function changeStatus(taskId) {
    console.log('Change status called for task:', taskId);
    currentTaskId = taskId;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    currentTaskId = null;
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const newStatus = document.getElementById('statusSelect').value;
    
    fetch(`/customer/admin/design/${currentTaskId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeStatusModal();
            // Reload page to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra', 'error');
    });
});

// Delete task
function deleteTask(taskId) {
    console.log('Delete task called for task:', taskId);
    if (confirm('Bạn có chắc muốn xóa task này?')) {
        fetch(`/customer/admin/design/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra', 'error');
        });
    }
}

// Export CSV
function exportCSV() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const queryString = new URLSearchParams(formData).toString();
    
    window.location.href = `{{ route('admin.design.export-csv') }}?${queryString}`;
}

// Notification function
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full
        ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const statusModal = document.getElementById('statusModal');
    if (event.target === statusModal) {
        closeStatusModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeStatusModal();
    }
});
</script>
@endpush
@endsection
