@extends('layouts.admin')

@section('title', 'Chi tiết Design Task')

@section('content-admin')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-palette mr-3"></i>Chi tiết Design Task
        </h2>
        <a href="{{ route('designer.tasks.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <!-- Thông tin chính -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-blue-600 text-white px-6 py-4">
                    <h5 class="text-xl font-semibold">{{ $task->title }}</h5>
                </div>
                <div class="p-6">
                    @if($task->description)
                    <div class="mb-6">
                        <h6 class="font-semibold text-gray-800 mb-2">Mô tả:</h6>
                        <p class="text-gray-600">{{ $task->description }}</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h6 class="font-semibold text-gray-800 mb-3">Thông tin cơ bản:</h6>
                            <ul class="space-y-2">
                                <li class="flex justify-between">
                                    <span class="font-medium text-gray-600">Số mặt:</span>
                                    <span class="text-gray-800 font-semibold">{{ $task->sides_count }} mặt</span>
                                </li>
                                @if($task->sides_count > 1)
                                <li class="flex justify-between">
                                    <span class="font-medium text-gray-600">Yêu cầu:</span>
                                    <span class="text-gray-800 text-sm">Thiết kế đầy đủ {{ $task->sides_count }} mặt</span>
                                </li>
                                @endif
                                <li class="flex justify-between items-center">
                                    <span class="font-medium text-gray-600">Trạng thái:</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($task->status === 'joined') bg-blue-100 text-blue-800
                                        @elseif($task->status === 'completed') bg-green-100 text-green-800
                                        @elseif($task->status === 'approved') bg-purple-100 text-purple-800
                                        @elseif($task->status === 'cancelled') bg-gray-100 text-gray-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $task->getStatusDisplayName() }}
                                    </span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="font-medium text-gray-600">Ngày tạo:</span>
                                    <span class="text-gray-800">{{ $task->created_at->format('d/m/Y H:i') }}</span>
                                </li>
                                @if($task->completed_at)
                                <li class="flex justify-between">
                                    <span class="font-medium text-gray-600">Hoàn thành:</span>
                                    <span class="text-gray-800">{{ $task->completed_at->format('d/m/Y H:i') }}</span>
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div>
                            <h6 class="font-semibold text-gray-800 mb-3">Thông tin khách hàng:</h6>
                            <ul class="space-y-2">
                                <li class="flex justify-between">
                                    <span class="font-medium text-gray-600">Tên:</span>
                                    <span class="text-gray-800">{{ $task->customer->first_name }} {{ $task->customer->last_name }}</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="font-medium text-gray-600">Email:</span>
                                    <span class="text-gray-800">{{ $task->customer->email }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-6">
                            <h6 class="font-semibold text-gray-800 mb-3">Thông tin designer:</h6>
                            @if($task->designer)
                            <ul class="space-y-2">
                                <li class="flex justify-between">
                                    <span class="font-medium text-gray-600">Tên:</span>
                                    <span class="text-gray-800">{{ $task->designer->first_name }} {{ $task->designer->last_name }}</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="font-medium text-gray-600">Email:</span>
                                    <span class="text-gray-800">{{ $task->designer->email }}</span>
                                </li>
                            </ul>
                            @else
                            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-2"></i>
                                    <span>Chưa có designer nhận task</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- File mockup -->
            @if($task->mockup_file)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-image mr-2"></i>Mockup/Hình ảnh tham khảo
                        @if(count($task->getMockupUrls()) > 1)
                        <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                            {{ count($task->getMockupUrls()) }} files
                        </span>
                        @endif
                    </h6>
                </div>
                <div class="p-6">
                    @php
                    $mockupUrls = $task->getMockupUrls();
                    $sideNames = ['Mặt trước', 'Mặt sau', 'Mặt trái', 'Mặt phải', 'Mặt trên'];
                    @endphp

                    @if(count($mockupUrls) > 1)
                    <!-- Multiple files -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($mockupUrls as $index => $mockupUrl)
                        @php
                        $sideName = $sideNames[$index] ?? "Mặt " . ($index + 1);
                        $isImage = $task->isMockupImage($mockupUrl);
                        @endphp

                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="mb-2">
                                <h6 class="font-medium text-gray-800">{{ $sideName }}</h6>
                            </div>

                            @if($isImage)
                            <img src="{{ $mockupUrl }}"
                                alt="Mockup {{ $sideName }}"
                                class="w-full h-32 object-cover rounded border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                onclick="openImageModal('{{ $mockupUrl }}', 'Mockup {{ $sideName }} - {{ $task->title }}')">
                            @else
                            <div class="w-full h-32 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                                <i class="fas fa-file text-gray-400"></i>
                            </div>
                            @endif

                            <div class="mt-2 text-center">
                                <a href="{{ $mockupUrl }}"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center justify-center">
                                    <i class="fas fa-download mr-1"></i>Tải xuống
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Thông tin tổng quan -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            <span class="text-sm text-blue-700">
                                Task này có <strong>{{ count($mockupUrls) }} mặt</strong> cần thiết kế.
                                Vui lòng xem kỹ tất cả mockup để hiểu rõ yêu cầu của khách hàng.
                            </span>
                        </div>
                    </div>
                    @else
                    <!-- Single file (legacy) -->
                    @if($task->isMockupImage())
                    <div class="relative group">
                        <img src="{{ $task->getMockupUrl() }}"
                            class="w-full max-w-xl mx-auto rounded-lg shadow-md object-contain cursor-pointer hover:opacity-90 transition-opacity"
                            alt="Mockup"
                            onclick="openImageModal('{{ $task->getMockupUrl() }}', 'Mockup - {{ $task->title }}')">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 rounded-lg flex items-center justify-center pointer-events-none">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <i class="fas fa-search-plus text-white text-xl drop-shadow-lg"></i>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-file-pdf text-6xl text-red-500 mb-4"></i>
                        <p class="text-gray-600 mb-4">File {{ strtoupper($task->getMockupFileExtension()) }}</p>
                        <a href="{{ $task->getMockupUrl() }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
                            target="_blank">
                            <i class="fas fa-download mr-2"></i>Tải xuống
                        </a>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
            @endif

            <!-- File thiết kế hiện tại -->
            @if($task->getCurrentDesignUrl())
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-palette mr-2"></i>Thiết kế hiện tại
                        @if($task->sides_count > 1)
                        <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                            {{ $task->sides_count }} mặt
                        </span>
                        @endif
                    </h6>
                </div>
                <div class="p-6">
                    @if($task->latestRevision)
                    @php
                    $designUrls = $task->latestRevision->getDesignUrls();
                    @endphp

                    @if(count($designUrls) > 1)
                    <!-- Multiple design files -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($designUrls as $index => $designUrl)
                        @php
                        $sideName = $sideNames[$index] ?? "Mặt " . ($index + 1);
                        $isImage = $task->latestRevision->isDesignImage($designUrl);
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="mb-2">
                                <h6 class="font-medium text-gray-800">{{ $sideName }}</h6>
                            </div>

                            @if($isImage)
                            <img src="{{ $designUrl }}"
                                class="w-full h-32 object-cover rounded border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                alt="Design {{ $sideName }}"
                                onclick="openImageModal('{{ $designUrl }}', 'Design {{ $sideName }} - {{ $task->title }}')">
                            @else
                            <div class="w-full h-32 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                                <i class="fas fa-file text-gray-400"></i>
                            </div>
                            @endif

                            <div class="mt-2 text-center">
                                <a href="{{ $designUrl }}"
                                    target="_blank"
                                    class="text-green-600 hover:text-green-700 text-sm font-medium flex items-center justify-center">
                                    <i class="fas fa-download mr-1"></i>Tải xuống
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <!-- Single design file -->
                    @if($task->latestRevision->isDesignImage())
                    <div class="relative group">
                        <img src="{{ $task->getCurrentDesignUrl() }}"
                            class="w-full max-w-xl mx-auto rounded-lg shadow-md object-contain cursor-pointer hover:opacity-90 transition-opacity"
                            alt="Design"
                            onclick="openImageModal('{{ $task->getCurrentDesignUrl() }}', 'Design - {{ $task->title }}')">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 rounded-lg flex items-center justify-center pointer-events-none">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <i class="fas fa-search-plus text-white text-xl drop-shadow-lg"></i>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-file text-6xl text-blue-500 mb-4"></i>
                        <p class="text-gray-600 mb-4">File {{ strtoupper($task->latestRevision->getDesignFileExtension()) }}</p>
                        <a href="{{ $task->getCurrentDesignUrl() }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
                            target="_blank">
                            <i class="fas fa-download mr-2"></i>Tải xuống
                        </a>
                    </div>
                    @endif
                    @endif
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Chưa có thiết kế nào được gửi</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Lịch sử revisions -->
            @if($task->revisions->count() > 0)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-history mr-2"></i>Lịch sử chỉnh sửa
                    </h6>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($task->revisions->sortByDesc('version') as $revision)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h6 class="font-semibold text-gray-800">Phiên bản {{ $revision->version }}</h6>
                                    <p class="text-sm text-gray-500">{{ $revision->submitted_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $revision->getStatusBadgeClass() }}">
                                    {{ $revision->getStatusDisplayName() }}
                                </span>
                            </div>

                            @if($revision->revision_notes)
                            <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                <p class="text-sm text-yellow-800">
                                    <strong>Yêu cầu chỉnh sửa:</strong> {{ $revision->revision_notes }}
                                </p>
                            </div>
                            @endif

                            @if($revision->notes)
                            <div class="mb-3">
                                <p class="text-sm text-gray-700">
                                    <strong>Ghi chú designer:</strong> {{ $revision->notes }}
                                </p>
                            </div>
                            @endif

                            <div class="flex space-x-2">
                                @if($revision->isDesignImage())
                                <img src="{{ $revision->getDesignUrl() }}"
                                    alt="Design v{{ $revision->version }}"
                                    class="w-20 h-20 object-cover rounded border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                    onclick="openImageModal('{{ $revision->getDesignUrl() }}', 'Design v{{ $revision->version }}')">
                                @else
                                <div class="w-20 h-20 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                                    <i class="fas fa-file text-gray-400"></i>
                                </div>
                                @endif
                                <a href="{{ $revision->getDesignUrl() }}"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                                    <i class="fas fa-download mr-1"></i>Tải xuống
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Ghi chú chỉnh sửa -->
            @if($task->revision_notes)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-yellow-500 text-white px-6 py-4">
                    <h6 class="font-semibold flex items-center">
                        <i class="fas fa-edit mr-2"></i>Yêu cầu chỉnh sửa
                    </h6>
                </div>
                <div class="p-6">
                    <p class="text-gray-800">{{ $task->revision_notes }}</p>
                </div>
            </div>
            @endif

            <!-- Form submit design -->
            @if($task->status === 'joined' && $task->designer_id === auth()->id())
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-green-600 text-white px-6 py-4">
                    <h6 class="font-semibold flex items-center">
                        <i class="fas fa-upload mr-2"></i>Gửi thiết kế hoàn chỉnh
                        @if($task->sides_count > 1)
                        <span class="ml-2 text-sm bg-green-500 px-2 py-1 rounded">
                            {{ $task->sides_count }} mặt
                        </span>
                        @endif
                    </h6>
                </div>
                <div class="p-6">
                    <form action="{{ route('designer.tasks.submit', $task->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if($task->sides_count > 1)
                        <!-- Upload nhiều files cho nhiều mặt -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Files thiết kế <span class="text-red-500">*</span>
                                <span class="text-sm text-gray-500">({{ $task->sides_count }} files cần thiết)</span>
                            </label>

                            <div id="design-files-container" class="space-y-4">
                                @for($i = 1; $i <= $task->sides_count; $i++)
                                    @php
                                    $sideName = $sideNames[$i - 1] ?? "Mặt {$i}";
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-sm font-medium text-gray-700">
                                                {{ $sideName }} <span class="text-red-500">*</span>
                                            </label>
                                            <span class="text-xs text-gray-500">{{ $i }}/{{ $task->sides_count }}</span>
                                        </div>
                                        <input type="file"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('design_files.' . ($i-1)) border-red-500 @enderror"
                                            name="design_files[]"
                                            accept=".jpg,.jpeg,.png,.pdf,.ai,.psd" required>
                                        @error('design_files.' . ($i-1))
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @endfor
                            </div>
                        </div>
                        @else
                        <!-- Upload 1 file cho 1 mặt -->
                        <div class="mb-6">
                            <label for="design_file" class="block text-sm font-medium text-gray-700 mb-2">
                                File thiết kế <span class="text-red-500">*</span>
                            </label>
                            <input type="file"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('design_file') border-red-500 @enderror"
                                id="design_file" name="design_file"
                                accept=".jpg,.jpeg,.png,.pdf,.ai,.psd" required>
                            @error('design_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Ghi chú (tùy chọn)</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                id="notes" name="notes" rows="3"
                                placeholder="Mô tả về thiết kế, kỹ thuật sử dụng..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-upload mr-2"></i>
                            @if($task->sides_count > 1)
                            Gửi {{ $task->sides_count }} thiết kế
                            @else
                            Gửi thiết kế
                            @endif
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Form chỉnh sửa design -->
            @if($task->status === 'revision' && $task->designer_id === auth()->id())
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-yellow-600 text-white px-6 py-4">
                    <h6 class="font-semibold flex items-center">
                        <i class="fas fa-edit mr-2"></i>Chỉnh sửa thiết kế
                        @if($task->sides_count > 1)
                        <span class="ml-2 text-sm bg-yellow-500 px-2 py-1 rounded">
                            {{ $task->sides_count }} mặt
                        </span>
                        @endif
                    </h6>
                </div>
                <div class="p-6">
                    <!-- Hiển thị yêu cầu chỉnh sửa từ khách hàng -->
                    @if($task->revision_notes)
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h6 class="font-semibold text-yellow-800 mb-2 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Yêu cầu chỉnh sửa từ khách hàng:
                        </h6>
                        <p class="text-yellow-700 text-sm">{{ $task->revision_notes }}</p>
                    </div>
                    @endif

                    <form action="{{ route('designer.tasks.submit', $task->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if($task->sides_count > 1)
                        <!-- Upload nhiều files cho nhiều mặt -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Files thiết kế đã chỉnh sửa <span class="text-red-500">*</span>
                                <span class="text-sm text-gray-500">({{ $task->sides_count }} files cần thiết)</span>
                            </label>

                            <div id="design-files-container" class="space-y-4">
                                @for($i = 1; $i <= $task->sides_count; $i++)
                                    @php
                                    $sideName = $sideNames[$i - 1] ?? "Mặt {$i}";
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-sm font-medium text-gray-700">
                                                {{ $sideName }} <span class="text-red-500">*</span>
                                            </label>
                                            <span class="text-xs text-gray-500">{{ $i }}/{{ $task->sides_count }}</span>
                                        </div>
                                        <input type="file"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('design_files.' . ($i-1)) border-red-500 @enderror"
                                            name="design_files[]"
                                            accept=".jpg,.jpeg,.png,.pdf,.ai,.psd" required>
                                        @error('design_files.' . ($i-1))
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @endfor
                            </div>
                        </div>
                        @else
                        <!-- Upload 1 file cho 1 mặt -->
                        <div class="mb-6">
                            <label for="design_file" class="block text-sm font-medium text-gray-700 mb-2">
                                File thiết kế đã chỉnh sửa <span class="text-red-500">*</span>
                            </label>
                            <input type="file"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('design_file') border-red-500 @enderror"
                                id="design_file" name="design_file"
                                accept=".jpg,.jpeg,.png,.pdf,.ai,.psd" required>
                            @error('design_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Ghi chú về chỉnh sửa (tùy chọn)</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                id="notes" name="notes" rows="3"
                                placeholder="Mô tả những thay đổi đã thực hiện..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-upload mr-2"></i>
                            @if($task->sides_count > 1)
                            Gửi {{ $task->sides_count }} thiết kế đã chỉnh sửa
                            @else
                            Gửi thiết kế đã chỉnh sửa
                            @endif
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-clock mr-2"></i>Timeline
                    </h6>
                </div>
                <div class="p-6">
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        <div class="space-y-6">
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-plus text-white text-xs"></i>
                                </div>
                                <div class="ml-12">
                                    <h6 class="font-semibold text-gray-800">Tạo yêu cầu</h6>
                                    <p class="text-sm text-gray-500">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>

                            @if($task->designer)
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-xs"></i>
                                </div>
                                <div class="ml-12">
                                    <h6 class="font-semibold text-gray-800">Bạn đã nhận task</h6>
                                    <p class="text-sm text-gray-500">{{ $task->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            @endif

                            @if($task->completed_at)
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                                <div class="ml-12">
                                    <h6 class="font-semibold text-gray-800">Đã gửi thiết kế</h6>
                                    <p class="text-sm text-gray-500">{{ $task->completed_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            @endif

                            @if($task->status === 'approved')
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-star text-white text-xs"></i>
                                </div>
                                <div class="ml-12">
                                    <h6 class="font-semibold text-gray-800">Khách hàng đã phê duyệt</h6>
                                    <p class="text-sm text-gray-500">Task hoàn thành</p>
                                </div>
                            </div>
                            @endif

                            @if($task->status === 'revision')
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-edit text-white text-xs"></i>
                                </div>
                                <div class="ml-12">
                                    <h6 class="font-semibold text-gray-800">Yêu cầu chỉnh sửa</h6>
                                    <p class="text-sm text-gray-500">Cần cập nhật thiết kế</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hướng dẫn -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>Hướng dẫn
                    </h6>
                </div>
                <div class="p-6">
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Xem kỹ tất cả mockup và mô tả từ khách hàng</span>
                        </li>
                        @if($task->sides_count > 1)
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Thiết kế đầy đủ cho <strong>{{ $task->sides_count }} mặt</strong> theo yêu cầu</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Đảm bảo thiết kế nhất quán giữa các mặt</span>
                        </li>
                        @else
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Thiết kế theo đúng yêu cầu số mặt</span>
                        </li>
                        @endif
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Upload file chất lượng cao</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Thêm ghi chú nếu cần thiết</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
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

<script>
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

    // Close modal when clicking outside
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
</script>
@endsection