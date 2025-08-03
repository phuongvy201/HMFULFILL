@extends('layouts.customer')

@section('title', 'My Design Tasks')

@section('content-customer')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-palette mr-3 text-blue-600"></i>My Design Tasks
            </h2>
            <p class="text-gray-600 mt-2">Quản lý các yêu cầu thiết kế của bạn</p>
        </div>
        <a href="{{ route('customer.design.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center shadow-lg hover:shadow-xl">
            <i class="fas fa-plus mr-2"></i>Tạo yêu cầu mới
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Tasks Grid -->
    @if($tasks->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tasks as $task)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition duration-300">
            <!-- Task Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4">
                <div class="flex justify-between items-start">
                    <h5 class="text-lg font-semibold truncate">{{ $task->title }}</h5>
                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                        @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($task->status === 'joined') bg-blue-100 text-blue-800
                        @elseif($task->status === 'completed') bg-green-100 text-green-800
                        @elseif($task->status === 'approved') bg-purple-100 text-purple-800
                        @elseif($task->status === 'cancelled') bg-gray-100 text-gray-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ $task->getStatusDisplayName() }}
                    </span>
                </div>
            </div>

            <!-- Task Content -->
            <div class="p-6">
                @if($task->description)
                <p class="text-gray-600 mb-4 text-sm leading-relaxed">{{ Str::limit($task->description, 120) }}</p>
                @endif

                <!-- Images Section -->
                <div class="mb-6">
                    <!-- Mockup Image -->
                    @if($task->mockup_file)
                    <div class="mb-4">
                        <h6 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-image mr-2 text-blue-500"></i>Mockup/Hình ảnh tham khảo
                        </h6>
                        @if($task->isMockupImage())
                        <div class="relative group">
                            <img src="{{ $task->getMockupUrl() }}"
                                alt="Mockup"
                                class="w-full h-32 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                onclick="openImageModal('{{ $task->getMockupUrl() }}', 'Mockup - {{ $task->title }}')">

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

                    <!-- Design Image -->
                    @if($task->design_file)
                    <div class="mb-4">
                        <h6 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-palette mr-2 text-green-500"></i>Thiết kế hoàn chỉnh
                        </h6>
                        @if($task->isDesignImage())
                        <div class="relative group">
                            <img src="{{ $task->getDesignUrl() }}"
                                alt="Design"
                                class="w-full h-32 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                onclick="openImageModal('{{ $task->getDesignUrl() }}', 'Design - {{ $task->title }}')">

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
                </div>

                <!-- Task Details -->
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-600 text-sm">Số mặt:</span>
                        <span class="text-gray-800 font-semibold">{{ $task->sides_count }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-600 text-sm">Giá:</span>
                        <span class="text-green-600 font-bold">${{ number_format($task->price, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-600 text-sm">Ngày tạo:</span>
                        <span class="text-gray-800">{{ $task->created_at->format('d/m/Y') }}</span>
                    </div>

                    @if($task->designer)
                    <div class="flex justify-between items-center py-2">
                        <span class="font-medium text-gray-600 text-sm">Designer:</span>
                        <span class="text-blue-600 font-medium">{{ $task->designer->first_name }} {{ $task->designer->last_name }}</span>
                    </div>
                    @endif

                    @if($task->completed_at)
                    <div class="flex justify-between items-center py-2">
                        <span class="font-medium text-gray-600 text-sm">Hoàn thành:</span>
                        <span class="text-gray-800">{{ $task->completed_at->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <a href="{{ route('customer.design.show', $task->id) }}"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-center text-sm">
                        <i class="fas fa-eye mr-2"></i>Xem chi tiết
                    </a>

                    @if($task->status === 'completed')
                    <button data-task-id="{{ $task->id }}"
                        class="review-task-btn flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                        <i class="fas fa-check mr-2"></i>Review
                    </button>
                    @endif

                    @if($task->status === 'pending')
                    <button data-task-id="{{ $task->id }}"
                        class="cancel-task-btn flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                        <i class="fas fa-times mr-2"></i>Hủy yêu cầu
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator && $tasks->hasPages())
    <div class="mt-8">
        {{ $tasks->links() }}
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="max-w-md mx-auto">
            <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-palette text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Chưa có yêu cầu thiết kế nào</h3>
            <p class="text-gray-500 mb-8 leading-relaxed">Bạn chưa tạo yêu cầu thiết kế nào. Hãy bắt đầu tạo yêu cầu đầu tiên để có được thiết kế tuyệt vời!</p>
            <a href="{{ route('customer.design.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-200 inline-flex items-center shadow-lg hover:shadow-xl">
                <i class="fas fa-plus mr-2"></i>Tạo yêu cầu đầu tiên
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="relative max-w-4xl max-h-full mx-4">
        <!-- Close button -->
        <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors">
            <i class="fas fa-times text-2xl"></i>
        </button>

        <!-- Modal content -->
        <div class="bg-white rounded-lg overflow-hidden shadow-2xl">
            <!-- Modal header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 id="imageModalTitle" class="text-lg font-semibold text-gray-800"></h3>
            </div>

            <!-- Modal body -->
            <div class="p-6">
                <img id="modalImage" src="" alt="" class="w-full h-auto max-h-96 object-contain rounded-lg">
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Đánh giá thiết kế</h3>
        </div>

        <form id="reviewForm" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hành động:</label>
                <select name="action" id="action" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="approve">Chấp nhận thiết kế</option>
                    <option value="revision">Yêu cầu chỉnh sửa</option>
                </select>
            </div>

            <div id="revisionNotesDiv" class="mb-4" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú chỉnh sửa:</label>
                <textarea name="revision_notes" id="revision_notes" rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Mô tả chi tiết những gì cần chỉnh sửa..."></textarea>
            </div>

            <div class="flex space-x-3">
                <button type="button" onclick="closeReviewModal()"
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200">
                    Hủy
                </button>
                <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                    Gửi đánh giá
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function reviewTask(taskId) {
        document.getElementById('reviewForm').action = `/customer/design/tasks/${taskId}/review`;
        document.getElementById('reviewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset form
        document.getElementById('revision_notes').value = '';
        document.getElementById('revision_notes').required = false;
        document.getElementById('revisionNotesDiv').style.display = 'none';
    }

    function openImageModal(imageUrl, title) {
        document.getElementById('modalImage').src = imageUrl;
        document.getElementById('modalImage').alt = title;
        document.getElementById('imageModalTitle').textContent = title;
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.getElementById('action').addEventListener('change', function() {
        const revisionNotesDiv = document.getElementById('revisionNotesDiv');
        const revisionNotes = document.getElementById('revision_notes');

        if (this.value === 'revision') {
            revisionNotesDiv.style.display = 'block';
            revisionNotes.required = true;
        } else {
            revisionNotesDiv.style.display = 'none';
            revisionNotes.required = false;
        }
    });

    // Close modal when clicking outside
    document.getElementById('reviewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeReviewModal();
        }
    });

    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReviewModal();
            closeImageModal();
        }
    });

    // Add event listeners for review task buttons
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.review-task-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const taskId = this.getAttribute('data-task-id');
                reviewTask(taskId);
            });
        });

        // Add event listeners for cancel task buttons
        document.querySelectorAll('.cancel-task-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const taskId = this.getAttribute('data-task-id');
                cancelTask(taskId);
            });
        });
    });

    function cancelTask(taskId) {
        Swal.fire({
            title: 'Bạn có chắc chắn muốn hủy yêu cầu thiết kế?',
            text: 'Yêu cầu thiết kế sẽ được hủy và tiền sẽ được hoàn về tài khoản của bạn.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Hủy yêu cầu',
            cancelButtonText: 'Không'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`/customer/design/tasks/${taskId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Thành công!',
                                text: data.message + (data.refund_amount ? ` Số tiền hoàn: $${data.refund_amount}` : ''),
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: data.message || 'Có lỗi xảy ra khi hủy yêu cầu thiết kế',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Cancel task error:', error);
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Có lỗi xảy ra khi hủy yêu cầu thiết kế',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    }
</script>
@endsection