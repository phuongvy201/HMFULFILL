@extends('layouts.customer')

@section('title', 'Tạo yêu cầu thiết kế')

@section('content-customer')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-palette mr-3 text-blue-600"></i>Tạo yêu cầu thiết kế mới
        </h2>
        <p class="text-gray-600 mt-2">Mô tả chi tiết yêu cầu thiết kế của bạn</p>
    </div>

    <!-- Balance Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-wallet text-blue-600 mr-3"></i>
                <span class="text-gray-700 font-medium">Số dư hiện tại:</span>
            </div>
            <span class="text-2xl font-bold text-green-600">${{ number_format($balance, 2) }}</span>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Có lỗi xảy ra:</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Create Form -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Thông tin yêu cầu thiết kế</h3>
        </div>

        <form action="{{ route('customer.design.store') }}" method="POST" enctype="multipart/form-data" class="p-6" id="designForm">
            @csrf

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Tiêu đề yêu cầu <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title"
                    value="{{ old('title') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                    placeholder="Ví dụ: Thiết kế logo cho công ty ABC"
                    required>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Mô tả chi tiết
                </label>
                <textarea name="description" id="description" rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 resize-none"
                    placeholder="Mô tả chi tiết yêu cầu thiết kế của bạn, bao gồm màu sắc, phong cách, kích thước, v.v...">{{ old('description') }}</textarea>
            </div>

            <!-- Sides Count -->
            <div class="mb-6">
                <label for="sides_count" class="block text-sm font-medium text-gray-700 mb-2">
                    Số mặt cần thiết kế <span class="text-red-500">*</span>
                </label>
                <select name="sides_count" id="sides_count"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                    required onchange="updateFileUploads()">
                    <option value="">Chọn số mặt</option>
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ old('sides_count') == $i ? 'selected' : '' }}>
                        {{ $i }} mặt ({{ number_format($i * 20000) }} VND)
                        </option>
                        @endfor
                </select>
            </div>

            <!-- Price Display -->
            <div class="mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Giá dự kiến:</span>
                        <span class="text-2xl font-bold text-green-600" id="priceDisplay">0 VND</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Giá sẽ được tính dựa trên số mặt bạn chọn (20,000 VND/mặt)</p>
                </div>
            </div>

            <!-- Mockup File -->
            <div class="mb-6">
                <label for="mockup_file" class="block text-sm font-medium text-gray-700 mb-2">
                    File mockup tham khảo <span class="text-red-500">*</span>
                </label>
                <div id="file-upload-container">
                    <!-- File upload sẽ được tạo động bằng JavaScript -->
                </div>
                <div id="fileInfo" class="mt-3 text-sm text-gray-600 hidden">
                    <i class="fas fa-file mr-2"></i>
                    <span id="fileName"></span>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('customer.design.my-tasks') }}"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg transition duration-200">
                    Hủy
                </a>
                <button type="submit" id="submitBtn"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i>Tạo yêu cầu
                </button>
            </div>

            <!-- Upload Progress -->
            <div id="uploadProgress" class="hidden mt-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-blue-700">Đang upload files...</span>
                        <span id="progressPercent" class="text-sm font-medium text-blue-700">0%</span>
                    </div>
                    <div class="w-full bg-blue-200 rounded-full h-2">
                        <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div id="uploadDetails" class="mt-2 text-xs text-blue-600">
                        <span id="currentFile">Chuẩn bị upload...</span>
                    </div>
                </div>
            </div>

            <!-- Upload Status -->
            <div id="uploadStatus" class="hidden mt-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-sm font-medium text-green-700">Upload hoàn tất!</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/chunk-upload.js') }}"></script>
<script>
    let uploadManager = null;
    let uploadedFiles = [];

    function updatePrice() {
        const sidesCount = parseInt(document.getElementById('sides_count').value) || 0;
        const price = sidesCount * 20000; // 20,000 VND per side (khớp với DesignTask::PRICE_PER_SIDE_VND)
        document.getElementById('priceDisplay').textContent = `${price.toLocaleString('vi-VN')} VND`;
    }

    function updateFileUploads() {
        const sidesCount = parseInt(document.getElementById('sides_count').value) || 0;
        const container = document.getElementById('file-upload-container');

        // Xóa nội dung cũ
        container.innerHTML = '';

        if (sidesCount === 0) {
            container.innerHTML = `
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <p class="text-gray-500">Vui lòng chọn số mặt trước</p>
                </div>
            `;
            return;
        }

        // Tạo các input file cho từng mặt
        for (let i = 1; i <= sidesCount; i++) {
            const sideName = getSideName(i);
            const uploadHtml = `
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        ${sideName} <span class="text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition duration-200">
                        <input type="file" name="mockup_files[]" id="mockup_file_${i}"
                            class="hidden"
                            accept=".jpg,.jpeg,.png,.pdf"
                            required
                            onchange="updateFileInfo(${i})">
                        <div class="space-y-3">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                            <div>
                                <p class="text-md font-medium text-gray-700">Tải lên ${sideName.toLowerCase()}</p>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF (tối đa 100MB - upload tự động chia nhỏ)</p>
                            </div>
                            <button type="button" onclick="document.getElementById('mockup_file_${i}').click()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                Chọn file
                            </button>
                        </div>
                    </div>
                    <div id="fileInfo_${i}" class="mt-2 text-sm text-gray-600 hidden">
                        <p class="text-green-600"><i class="fas fa-check mr-1"></i>File đã được chọn</p>
                    </div>
                </div>
            `;
            container.innerHTML += uploadHtml;
        }
    }

    function getSideName(sideNumber) {
        const sideNames = {
            1: 'Mặt trước',
            2: 'Mặt sau',
            3: 'Mặt trái',
            4: 'Mặt phải',
            5: 'Mặt trên'
        };
        return sideNames[sideNumber] || `Mặt ${sideNumber}`;
    }

    function updateFileInfo(sideNumber) {
        const fileInput = document.getElementById(`mockup_file_${sideNumber}`);
        const fileInfo = document.getElementById(`fileInfo_${sideNumber}`);

        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileInfo.innerHTML = `
                <p class="text-green-600">
                    <i class="fas fa-check mr-1"></i>${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                </p>
            `;
            fileInfo.classList.remove('hidden');
        } else {
            fileInfo.classList.add('hidden');
        }
    }

    // Khởi tạo upload manager
    function initUploadManager() {
        uploadManager = new FileUploadManager({
            uploadUrl: '{{ route("customer.design.upload-chunk") }}',
            statusUrl: '{{ route("customer.design.upload-status", ["uploadId" => "UPLOAD_ID"]) }}'.replace('UPLOAD_ID', '{uploadId}'),
            cancelUrl: '{{ route("customer.design.upload-cancel") }}',
            chunkSize: 1024 * 1024, // 1MB per chunk
            maxFileSize: 100 * 1024 * 1024, // 100MB
            onFileProgress: function(data) {
                updateUploadProgress(data);
            },
            onFileComplete: function(data) {
                uploadedFiles.push(data.filePath);
                console.log('File uploaded:', data.filePath);
            },
            onFileError: function(data) {
                console.error('Upload error:', data.error);
                showNotification('Lỗi upload file: ' + data.error, 'error');
            },
            onAllComplete: function(data) {
                console.log('All uploads completed:', data);
                showUploadStatus();
            }
        });
    }

    // Cập nhật progress
    function updateUploadProgress(data) {
        const progressBar = document.getElementById('progressBar');
        const progressPercent = document.getElementById('progressPercent');
        const currentFile = document.getElementById('currentFile');

        progressBar.style.width = data.progress + '%';
        progressPercent.textContent = Math.round(data.progress) + '%';
        currentFile.textContent = `Đang upload: ${data.file.name} (${data.chunkIndex}/${data.totalChunks} chunks)`;
    }

    // Hiển thị trạng thái upload
    function showUploadStatus() {
        document.getElementById('uploadProgress').classList.add('hidden');
        document.getElementById('uploadStatus').classList.remove('hidden');
    }

    // Xử lý form submission
    document.getElementById('designForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const sidesCount = parseInt(document.getElementById('sides_count').value) || 0;
        if (sidesCount === 0) {
            showNotification('Vui lòng chọn số mặt', 'error');
            return;
        }

        // Kiểm tra files
        const files = [];
        for (let i = 1; i <= sidesCount; i++) {
            const fileInput = document.getElementById(`mockup_file_${i}`);
            if (fileInput.files.length === 0) {
                showNotification(`Vui lòng chọn file cho ${getSideName(i)}`, 'error');
                return;
            }
            files.push(fileInput.files[0]);
        }

        // Reset uploaded files array
        uploadedFiles = [];

        // Khởi tạo upload manager nếu chưa có
        if (!uploadManager) {
            initUploadManager();
        }

        // Thêm files vào queue
        uploadManager.clearQueue();
        uploadManager.addFiles(files);

        // Hiển thị progress
        document.getElementById('uploadProgress').classList.remove('hidden');
        document.getElementById('uploadStatus').classList.add('hidden');

        try {
            // Bắt đầu upload
            await uploadManager.startUpload();

            // Kiểm tra lại uploaded files sau khi upload
            const completedFiles = uploadManager.getCompletedFiles();
            console.log('Completed files:', completedFiles);

            if (completedFiles.length === sidesCount) {
                // Thêm uploaded files vào form
                completedFiles.forEach((filePath, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'uploaded_files[]';
                    input.value = filePath;
                    document.getElementById('designForm').appendChild(input);
                });

                // Submit form
                document.getElementById('designForm').submit();
            } else {
                showNotification(`Có lỗi xảy ra khi upload files. Đã upload ${completedFiles.length}/${sidesCount} files.`, 'error');
            }

        } catch (error) {
            console.error('Upload error:', error);
            showNotification('Lỗi upload: ' + error.message, 'error');
        }
    });

    // Hiển thị notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${bgColor}`;
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas ${icon} mr-2"></i>
                    <span>${message}</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }

    // Cập nhật giá và file upload khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        updatePrice();
        updateFileUploads();
        initUploadManager();
    });

    // Cập nhật giá khi thay đổi số mặt
    document.getElementById('sides_count').addEventListener('change', function() {
        updatePrice();
        updateFileUploads();
    });

    // Upload Progress Handler
    function handleUploadProgress(form, progressId, progressBarId, progressPercentId, submitBtnId) {
        const progressDiv = document.getElementById(progressId);
        const progressBar = document.getElementById(progressBarId);
        const progressPercent = document.getElementById(progressPercentId);
        const submitBtn = document.getElementById(submitBtnId);

        if (form && progressDiv && progressBar && progressPercent && submitBtn) {
            form.addEventListener('submit', function(e) {
                // Show progress bar
                progressDiv.classList.remove('hidden');
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

                // Simulate progress (since we can't get real upload progress without AJAX)
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90; // Don't go to 100% until upload completes

                    progressBar.style.width = progress + '%';
                    progressPercent.textContent = Math.round(progress) + '%';
                }, 200);

                // Reset progress when form is actually submitted
                setTimeout(() => {
                    clearInterval(interval);
                    progressBar.style.width = '100%';
                    progressPercent.textContent = '100%';
                }, 1000);
            });
        }
    }

    // Thêm validation trước khi submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const sidesCount = parseInt(document.getElementById('sides_count').value) || 0;
        const fileInputs = document.querySelectorAll('input[name="mockup_files[]"]');
        const selectedFiles = [];

        // Kiểm tra từng input file
        fileInputs.forEach((input, index) => {
            if (input.files.length > 0) {
                const file = input.files[0];
                selectedFiles.push({
                    index: index + 1,
                    name: file.name,
                    size: file.size,
                    type: file.type
                });
            }
        });

        // Debug log
        console.log('Form submission debug:', {
            sidesCount: sidesCount,
            fileInputsCount: fileInputs.length,
            selectedFilesCount: selectedFiles.length,
            selectedFiles: selectedFiles
        });

        // Kiểm tra số lượng files
        if (selectedFiles.length !== sidesCount) {
            e.preventDefault();
            alert(`Lỗi: Bạn đã chọn ${selectedFiles.length} file nhưng cần ${sidesCount} file cho ${sidesCount} mặt. Vui lòng kiểm tra lại.`);
            return false;
        }

        // Kiểm tra định dạng file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        for (let file of selectedFiles) {
            if (!allowedTypes.includes(file.type)) {
                e.preventDefault();
                alert(`Lỗi: File "${file.name}" không đúng định dạng. Chỉ chấp nhận JPG, PNG, PDF.`);
                return false;
            }
        }

        // Kiểm tra kích thước file (50MB)
        const maxSize = 50 * 1024 * 1024; // 50MB in bytes
        for (let file of selectedFiles) {
            if (file.size > maxSize) {
                e.preventDefault();
                alert(`Lỗi: File "${file.name}" quá lớn (${(file.size / 1024 / 1024).toFixed(2)}MB). Tối đa 50MB.`);
                return false;
            }
        }

        console.log('Form validation passed, submitting...');
    });

    // Initialize progress handler
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            handleUploadProgress(
                form,
                'uploadProgress',
                'progressBar',
                'progressPercent',
                'submitBtn'
            );
        }
    });
</script>
@endsection