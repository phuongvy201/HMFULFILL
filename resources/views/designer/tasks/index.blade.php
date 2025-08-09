@extends('layouts.customer')

@section('title', 'Design Tasks')

@section('content-customer')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 flex items-center mb-8">
        <i class="fas fa-palette mr-3"></i>Design Tasks
    </h2>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    </div>
    @endif

    <!-- Tất cả Tasks -->
    @if($allTasks->count() > 0)
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4">
            <h5 class="text-xl font-semibold flex items-center">
                <i class="fas fa-tasks mr-2"></i>
                Tất cả Design Tasks ({{ $allTasks->count() }})
            </h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($allTasks as $task)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-200 overflow-hidden
                    @if($task->status === 'pending') border-2 border-yellow-200
                    @elseif($task->designer_id === auth()->id()) border-2 border-blue-200
                    @else border border-gray-200 @endif">

                    <!-- Task Header -->
                    <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center
                        @if($task->status === 'pending') bg-yellow-50
                        @elseif($task->designer_id === auth()->id()) bg-blue-50
                        @else bg-gray-50 @endif">
                        <h6 class="font-semibold text-gray-800 truncate">{{ $task->title }}</h6>
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($task->status === 'joined') bg-blue-100 text-blue-800
                                    @elseif($task->status === 'completed') bg-green-100 text-green-800
                                    @elseif($task->status === 'approved') bg-purple-100 text-purple-800
                                    @elseif($task->status === 'cancelled') bg-gray-100 text-gray-800
                                    @else bg-red-100 text-red-800 @endif">
                            {{ $task->getStatusDisplayName() }}
                        </span>
                    </div>

                    <!-- Designer Info -->
                    @if($task->designer)
                    <div class="px-4 py-2 bg-blue-50 border-b border-blue-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600">Được nhận bởi:</span>
                            <span class="text-sm font-medium text-blue-700 flex items-center">
                                <i class="fas fa-user mr-1"></i>
                                {{ $task->designer->first_name }} {{ $task->designer->last_name }}
                                @if($task->designer_id === auth()->id())
                                <span class="ml-2 px-2 py-1 text-xs bg-blue-200 text-blue-800 rounded-full">Bạn</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Hiển thị hình ảnh mockup -->
                    @if($task->mockup_file)
                    <div class="p-4 border-b border-gray-200">
                        <div class="mb-2">
                            <p class="text-xs text-gray-500 uppercase tracking-wide flex items-center">
                                <i class="fas fa-image mr-1 text-blue-500"></i>Mockup tham khảo
                            </p>
                        </div>
                        @if($task->isMockupImage())
                        <div class="relative group">
                            <img src="{{ $task->getMockupUrl() }}"
                                alt="Mockup"
                                class="w-full h-32 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                onclick="openImageModal('{{ $task->getMockupUrl() }}', 'Mockup - {{ $task->title }}')">
                        </div>
                        @else
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <i class="fas fa-file-pdf text-2xl text-red-500 mb-2"></i>
                            <p class="text-xs text-gray-600 mb-2">File {{ strtoupper($task->getMockupFileExtension()) }}</p>
                            <a href="{{ $task->getMockupUrl() }}" target="_blank"
                                class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                <i class="fas fa-download mr-1"></i>Tải xuống
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Hiển thị design file nếu có -->
                    @if($task->design_file)
                    <div class="p-4 border-b border-gray-200">
                        <div class="mb-2">
                            <p class="text-xs text-gray-500 uppercase tracking-wide flex items-center">
                                <i class="fas fa-palette mr-1 text-green-500"></i>Thiết kế hoàn chỉnh
                            </p>
                        </div>
                        @if($task->isDesignImage())
                        <div class="relative group">
                            <img src="{{ $task->getDesignUrl() }}"
                                alt="Design"
                                class="w-full h-32 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                onclick="openImageModal('{{ $task->getDesignUrl() }}', 'Design - {{ $task->title }}')">
                        </div>
                        @else
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <i class="fas fa-file text-2xl text-blue-500 mb-2"></i>
                            <p class="text-xs text-gray-600 mb-2">File {{ strtoupper($task->getDesignFileExtension()) }}</p>
                            <a href="{{ $task->getDesignUrl() }}" target="_blank"
                                class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                <i class="fas fa-download mr-1"></i>Tải xuống
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="p-4">
                        @if($task->description)
                        <p class="text-gray-600 text-sm mb-4">
                            {{ Str::limit($task->description, 80) }}
                        </p>
                        @endif

                        <div class="grid grid-cols-2 gap-4 mb-4 text-center">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Số mặt</p>
                                <p class="font-semibold text-gray-800">{{ $task->sides_count }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Giá</p>
                                <p class="font-semibold text-blue-600">${{ number_format($task->price, 2) }}</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Khách hàng</p>
                            <p class="font-medium text-gray-800">{{ $task->customer->first_name }} {{ $task->customer->last_name }}</p>
                        </div>

                        <div class="mb-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Ngày tạo</p>
                            <p class="text-gray-800">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="px-4 py-3 bg-gray-50 space-y-2">
                        <a href="{{ route('designer.tasks.show', $task->id) }}"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-eye mr-2"></i>Xem chi tiết
                        </a>

                        @if($task->status === 'pending')
                        <button class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center join-task-btn"
                            data-task-id="{{ $task->id }}">
                            <i class="fas fa-hand-paper mr-2"></i>Nhận Task
                        </button>
                        @endif

                        @if($task->designer_id === auth()->id() && $task->status === 'joined')
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center submit-design-btn"
                            data-task-id="{{ $task->id }}"
                            onclick="openSubmitModal('{{ $task->id }}')">
                            <i class="fas fa-upload mr-2"></i>Gửi thiết kế
                        </button>
                        @endif

                        @if($task->designer_id === auth()->id() && $task->status === 'revision')
                        <a href="{{ route('designer.tasks.show', $task->id) }}"
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-edit mr-2"></i>Chỉnh sửa thiết kế
                        </a>
                        @endif

                        @if($task->designer_id === auth()->id() && $task->status === 'completed')
                        <a href="{{ route('designer.tasks.show', $task->id) }}"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-sync-alt mr-2"></i>Cập nhật thiết kế
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($allTasks->count() === 0)
    <div class="text-center py-16">
        <i class="fas fa-palette text-6xl text-gray-300 mb-6"></i>
        <h5 class="text-xl font-semibold text-gray-600 mb-2">Chưa có design task nào</h5>
        <p class="text-gray-500">Hãy chờ các yêu cầu thiết kế mới từ khách hàng!</p>
    </div>
    @endif
</div>

<!-- Modal Submit Design -->
<div id="submitDesignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Gửi thiết kế hoàn chỉnh</h3>
                <button onclick="closeSubmitModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="submitDesignForm" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            File thiết kế <span class="text-red-500">*</span>
                        </label>
                        <div id="fileUploadArea" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                            <div class="space-y-4">
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="text-lg font-medium text-gray-700">Kéo thả files hoặc click để chọn</p>
                                    <p class="text-sm text-gray-500 mt-1">Chấp nhận: JPG, JPEG, PNG, PDF, AI, PSD (tối đa 100MB)</p>
                                </div>
                                <input type="file" id="designFiles" multiple accept=".jpg,.jpeg,.png,.pdf,.ai,.psd" class="hidden">
                                <button type="button" onclick="document.getElementById('designFiles').click()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                    Chọn Files
                                </button>
                            </div>
                        </div>



                        <!-- File list -->
                        <div id="fileList" class="mt-4 space-y-2"></div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Ghi chú (tùy chọn)</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            id="notes" name="notes" rows="3"
                            placeholder="Mô tả về thiết kế, kỹ thuật sử dụng..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeSubmitModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200">
                        Hủy
                    </button>
                    <button type="button" onclick="submitDesign()"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center">
                        <i class="fas fa-upload mr-2"></i>Gửi thiết kế
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xem hình ảnh -->
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
    let currentTaskId = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Join task
        document.querySelectorAll('.join-task-btn').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.dataset.taskId;
                const button = this;
                const taskCard = button.closest('.bg-white');

                if (confirm('Bạn có chắc muốn nhận task này?')) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';

                    fetch(`/designer/tasks/${taskId}/join`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Cập nhật giao diện động
                                updateTaskCardAfterJoin(taskCard, data.designer);

                                // Hiển thị thông báo thành công
                                showNotification(data.message, 'success');
                            } else {
                                alert(data.message);
                                button.disabled = false;
                                button.innerHTML = '<i class="fas fa-hand-paper mr-2"></i>Nhận Task';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Có lỗi xảy ra. Vui lòng thử lại.');
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-hand-paper mr-2"></i>Nhận Task';
                        });
                }
            });
        });
    });

    function updateTaskCardAfterJoin(taskCard, designer) {
        // Cập nhật border và background
        taskCard.classList.remove('border-2', 'border-yellow-200');
        taskCard.classList.add('border-2', 'border-blue-200');

        // Cập nhật header background
        const header = taskCard.querySelector('.px-4.py-3');
        header.classList.remove('bg-yellow-50');
        header.classList.add('bg-blue-50');

        // Thêm thông tin designer
        const headerDiv = header.parentNode;
        const designerInfo = document.createElement('div');
        designerInfo.className = 'px-4 py-2 bg-blue-50 border-b border-blue-100';
        designerInfo.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600">Được nhận bởi:</span>
                <span class="text-sm font-medium text-blue-700 flex items-center">
                    <i class="fas fa-user mr-1"></i>
                    ${designer.first_name} ${designer.last_name}
                    <span class="ml-2 px-2 py-1 text-xs bg-blue-200 text-blue-800 rounded-full">Bạn</span>
                </span>
            </div>
        `;
        headerDiv.insertBefore(designerInfo, header.nextSibling);

        // Cập nhật status
        const statusSpan = header.querySelector('.px-2.py-1');
        statusSpan.className = 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800';
        statusSpan.textContent = 'Đã nhận';

        // Thay thế button "Nhận Task" bằng button "Gửi thiết kế"
        const actionDiv = taskCard.querySelector('.px-4.py-3.bg-gray-50');
        const joinButton = actionDiv.querySelector('.join-task-btn');
        if (joinButton) {
            const submitButton = document.createElement('button');
            submitButton.className = 'w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center submit-design-btn';
            submitButton.setAttribute('data-task-id', joinButton.dataset.taskId);
            submitButton.setAttribute('onclick', `openSubmitModal('${joinButton.dataset.taskId}')`);
            submitButton.innerHTML = '<i class="fas fa-upload mr-2"></i>Gửi thiết kế';

            joinButton.parentNode.replaceChild(submitButton, joinButton);
        }
    }

    function showNotification(message, type = 'success') {
        // Tạo notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full
            ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        // Thêm vào body
        document.body.appendChild(notification);

        // Hiển thị notification
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Tự động ẩn sau 3 giây
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    function openSubmitModal(taskId) {
        currentTaskId = taskId;
        const form = document.getElementById('submitDesignForm');
        form.action = `/designer/tasks/${taskId}/submit`;
        document.getElementById('submitDesignModal').classList.remove('hidden');

        // Reset form
        document.getElementById('fileList').innerHTML = '';
        document.getElementById('designFiles').value = '';


    }

    function closeSubmitModal() {
        document.getElementById('submitDesignModal').classList.add('hidden');
    }



    function submitDesign() {
        const fileInput = document.getElementById('designFiles');
        if (!fileInput.files || fileInput.files.length === 0) {
            showNotification('Vui lòng chọn ít nhất một file để upload', 'error');
            return;
        }

        // Submit form directly
        document.getElementById('submitDesignForm').submit();
    }



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

    // Đóng modal khi click bên ngoài
    document.addEventListener('click', function(event) {
        const imageModal = document.getElementById('imageModal');
        if (event.target === imageModal) {
            closeImageModal();
        }
    });

    // Đóng modal khi nhấn ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImageModal();
        }
    });

    function downloadImage() {
        // Function này được gọi từ onclick của download button
        // Logic đã được xử lý trong openImageModal
    }
</script>
@endpush
@endsection