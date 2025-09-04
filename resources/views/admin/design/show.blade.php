@extends('layouts.admin')

@section('title', 'Chi tiết Design Task')

@section('content-admin')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-palette mr-3 text-blue-600"></i>
            Chi tiết Design Task
        </h1>
        <div class="flex space-x-3">
            <a href="{{ route('admin.design.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại
            </a>
            <button onclick="changeStatus()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                <i class="fas fa-edit mr-2"></i>
                Thay đổi trạng thái
            </button>
        </div>
    </div>

    <!-- Task Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Task Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin cơ bản</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
                        <p class="text-gray-900 font-medium">{{ $task->title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <span class="px-3 py-1 text-sm font-medium rounded-full 
                            @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($task->status === 'joined') bg-blue-100 text-blue-800
                            @elseif($task->status === 'completed') bg-green-100 text-green-800
                            @elseif($task->status === 'approved') bg-purple-100 text-purple-800
                            @elseif($task->status === 'revision') bg-orange-100 text-orange-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $task->getStatusDisplayName() }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giá</label>
                        <p class="text-blue-600 font-semibold">${{ number_format($task->price, 2) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số mặt</label>
                        <p class="text-gray-900">{{ $task->sides_count }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <p class="text-gray-900">{{ $task->description ?: 'Không có mô tả' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày tạo</label>
                        <p class="text-gray-900">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cập nhật lần cuối</label>
                        <p class="text-gray-900">{{ $task->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin khách hàng</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên</label>
                        <p class="text-gray-900">{{ $task->customer->first_name }} {{ $task->customer->last_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <p class="text-gray-900">{{ $task->customer->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                        <p class="text-gray-900">{{ $task->customer->phone ?: 'Không có' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày tham gia</label>
                        <p class="text-gray-900">{{ $task->customer->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Designer Information -->
            @if($task->designer)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin Designer</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên</label>
                        <p class="text-gray-900">{{ $task->designer->first_name }} {{ $task->designer->last_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <p class="text-gray-900">{{ $task->designer->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                        <p class="text-gray-900">{{ $task->designer->phone ?: 'Không có' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày tham gia</label>
                        <p class="text-gray-900">{{ $task->designer->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Files Section -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Files</h3>
                
                <!-- Mockup File -->
                @if($task->mockup_file)
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-image mr-2 text-blue-500"></i>Mockup tham khảo
                    </h4>
                    @if($task->isMockupImage())
                    <div class="relative group">
                        <img src="{{ $task->getMockupUrl() }}" 
                             alt="Mockup" 
                             class="w-full max-w-md h-auto rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                             onclick="openImageModal('{{ $task->getMockupUrl() }}', 'Mockup - {{ $task->title }}')">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 rounded-lg flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <i class="fas fa-search text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <i class="fas fa-file-pdf text-3xl text-red-500 mb-2"></i>
                        <p class="text-sm text-gray-600 mb-2">File {{ strtoupper($task->getMockupFileExtension()) }}</p>
                        <a href="{{ $task->getMockupUrl() }}" target="_blank"
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            <i class="fas fa-download mr-1"></i>Tải xuống
                        </a>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Design File -->
                @if($task->design_file)
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-palette mr-2 text-green-500"></i>Thiết kế hoàn chỉnh
                    </h4>
                    @if($task->isDesignImage())
                    <div class="relative group">
                        <img src="{{ $task->getDesignUrl() }}" 
                             alt="Design" 
                             class="w-full max-w-md h-auto rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                             onclick="openImageModal('{{ $task->getDesignUrl() }}', 'Design - {{ $task->title }}')">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 rounded-lg flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <i class="fas fa-search text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <i class="fas fa-file text-3xl text-blue-500 mb-2"></i>
                        <p class="text-sm text-gray-600 mb-2">File {{ strtoupper($task->getDesignFileExtension()) }}</p>
                        <a href="{{ $task->getDesignUrl() }}" target="_blank"
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            <i class="fas fa-download mr-1"></i>Tải xuống
                        </a>
                    </div>
                    @endif
                </div>
                @endif

                @if(!$task->mockup_file && !$task->design_file)
                <div class="text-center py-8">
                    <i class="fas fa-file-upload text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">Chưa có file nào được upload</p>
                </div>
                @endif
            </div>

            <!-- Comments Section -->
            @if($task->comments && $task->comments->count() > 0)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bình luận ({{ $task->comments->count() }})</h3>
                <div class="space-y-4">
                    @foreach($task->comments->sortBy('created_at') as $comment)
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $comment->user->first_name }} {{ $comment->user->last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $comment->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full 
                                @if($comment->user->role === 'admin') bg-red-100 text-red-800
                                @elseif($comment->user->role === 'designer') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ ucfirst($comment->user->role) }}
                            </span>
                        </div>
                        <p class="text-gray-700 text-sm">{{ $comment->content }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thao tác</h3>
                <div class="space-y-3">
                    <button onclick="changeStatus()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-edit mr-2"></i>Thay đổi trạng thái
                    </button>
                    
                    @if($task->status === 'pending')
                    <button onclick="assignDesigner()" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-user-plus mr-2"></i>Gán designer
                    </button>
                    @endif

                    @if($task->status === 'completed')
                    <button onclick="approveTask()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-thumbs-up mr-2"></i>Duyệt task
                    </button>
                    @endif

                    <button onclick="deleteTask()" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i>Xóa task
                    </button>
                </div>
            </div>

            <!-- Task Timeline -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Lịch sử</h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mt-2 mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Task được tạo</p>
                            <p class="text-xs text-gray-500">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($task->designer)
                    <div class="flex items-start">
                        <div class="w-3 h-3 bg-green-500 rounded-full mt-2 mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Designer nhận task</p>
                            <p class="text-xs text-gray-500">{{ $task->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($task->design_file)
                    <div class="flex items-start">
                        <div class="w-3 h-3 bg-purple-500 rounded-full mt-2 mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Design được submit</p>
                            <p class="text-xs text-gray-500">{{ $task->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($task->notes)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ghi chú</h3>
                <p class="text-gray-700 text-sm">{{ $task->notes }}</p>
            </div>
            @endif
        </div>
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
                        <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                        <option value="joined" {{ $task->status === 'joined' ? 'selected' : '' }}>Đã nhận</option>
                        <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="approved" {{ $task->status === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="revision" {{ $task->status === 'revision' ? 'selected' : '' }}>Cần sửa</option>
                        <option value="cancelled" {{ $task->status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
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

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 overflow-y-auto h-full w-full hidden z-9999">
    <div class="relative top-5 mx-auto p-5 w-11/12 max-w-6xl">
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b border-gray-200">
                <h3 id="imageModalTitle" class="text-lg font-semibold text-gray-800"></h3>
                <div class="flex items-center space-x-3">
                    <button id="downloadBtn" onclick="downloadImage()" class="text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-download"></i>
                    </button>
                    <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <div class="text-center relative">
                    <img id="modalImage" src="" alt="" class="max-w-full max-h-[70vh] mx-auto rounded-lg shadow-lg object-contain">
                    <div id="imageLoading" class="hidden absolute inset-0 flex items-center justify-center bg-gray-100 rounded-lg">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Status change modal
function changeStatus() {
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const newStatus = document.getElementById('statusSelect').value;
    
    fetch(`/admin/design/{{ $task->id }}/status`, {
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
function deleteTask() {
    if (confirm('Bạn có chắc muốn xóa task này?')) {
        fetch(`/admin/design/{{ $task->id }}`, {
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
                    window.location.href = '{{ route("admin.design.index") }}';
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

// Image modal functions
function openImageModal(imageUrl, title) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('imageModalTitle');
    const downloadBtn = document.getElementById('downloadBtn');
    const imageLoading = document.getElementById('imageLoading');

    // Hiển thị loading
    imageLoading.classList.remove('hidden');
    modalImage.classList.add('hidden');

    // Cập nhật tiêu đề
    modalTitle.textContent = title;

    // Hiển thị modal
    modal.classList.remove('hidden');

    // Tạo link download
    downloadBtn.onclick = function() {
        const link = document.createElement('a');
        link.href = imageUrl;
        link.download = title.replace(/[^a-zA-Z0-9]/g, '_') + '.jpg';
        link.click();
    };

    // Load hình ảnh
    modalImage.onload = function() {
        imageLoading.classList.add('hidden');
        modalImage.classList.remove('hidden');
    };

    modalImage.onerror = function() {
        imageLoading.classList.add('hidden');
        alert('Không thể tải hình ảnh. Vui lòng thử lại.');
    };

    modalImage.src = imageUrl;
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const imageLoading = document.getElementById('imageLoading');

    modal.classList.add('hidden');
    modalImage.classList.add('hidden');
    imageLoading.classList.add('hidden');
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
    const imageModal = document.getElementById('imageModal');
    
    if (event.target === statusModal) {
        closeStatusModal();
    }
    if (event.target === imageModal) {
        closeImageModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeStatusModal();
        closeImageModal();
    }
});
</script>
@endpush
@endsection
