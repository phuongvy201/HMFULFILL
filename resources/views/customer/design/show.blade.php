@extends('layouts.customer')

@section('title', 'Chi tiết Design Task')

@section('content-customer')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-palette mr-3 text-blue-600"></i>Chi tiết Design Task
        </h2>
        <a href="{{ route('customer.design.my-tasks') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <!-- Thông tin chính -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4">
                    <h5 class="text-xl font-semibold">{{ $task->title }}</h5>
                </div>
                <div class="p-6">
                    @if($task->description)
                    <div class="mb-6">
                        <h6 class="font-semibold text-gray-800 mb-2">Mô tả:</h6>
                        <p class="text-gray-600 leading-relaxed">{{ $task->description }}</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h6 class="font-semibold text-gray-800 mb-3">Thông tin cơ bản:</h6>
                            <ul class="space-y-3">
                                <li class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-600">Số mặt:</span>
                                    <span class="text-gray-800 font-semibold">{{ $task->sides_count }}</span>
                                </li>

                                <li class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-600">Giá:</span>
                                    <span class="text-green-600 font-bold">${{ number_format($task->price, 2) }}</span>
                                </li>

                                <li class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-600">Trạng thái:</span>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($task->status === 'joined') bg-blue-100 text-blue-800
                                        @elseif($task->status === 'completed') bg-green-100 text-green-800
                                        @elseif($task->status === 'approved') bg-purple-100 text-purple-800
                                        @elseif($task->status === 'cancelled') bg-gray-100 text-gray-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $task->getStatusDisplayName() }}
                                    </span>
                                </li>
                                <li class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-600">Ngày tạo:</span>
                                    <span class="text-gray-800">{{ $task->created_at->format('d/m/Y H:i') }}</span>
                                </li>
                                @if($task->completed_at)
                                <li class="flex justify-between items-center py-2">
                                    <span class="font-medium text-gray-600">Hoàn thành:</span>
                                    <span class="text-gray-800">{{ $task->completed_at->format('d/m/Y H:i') }}</span>
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div>
                            <h6 class="font-semibold text-gray-800 mb-3">Thông tin designer:</h6>
                            @if($task->designer)
                            <ul class="space-y-3">
                                <li class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-600">Tên:</span>
                                    <span class="text-blue-600 font-medium">{{ $task->designer->first_name }} {{ $task->designer->last_name }}</span>
                                </li>
                                <li class="flex justify-between items-center py-2">
                                    <span class="font-medium text-gray-600">Email:</span>
                                    <span class="text-gray-800">{{ $task->designer->email }}</span>
                                </li>
                            </ul>
                            @else
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
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
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-image mr-2 text-blue-600"></i>Mockup/Hình ảnh tham khảo
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
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="mb-2">
                                <h6 class="font-medium text-gray-800">{{ $sideNames[$index] ?? "Mặt " . ($index + 1) }}</h6>
                            </div>
                            @if($task->isMockupImage($mockupUrl))
                            <img src="{{ $mockupUrl }}"
                                alt="Mockup {{ $sideNames[$index] ?? ($index + 1) }}"
                                class="w-full h-32 object-cover rounded border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                onclick="openImageModal('{{ $mockupUrl }}', '{{ $sideNames[$index] ?? 'Mặt ' . ($index + 1) }}')">
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
                    @else
                    <!-- Single file (legacy) -->
                    @if($task->isMockupImage())
                    <x-image-viewer
                        :src="$task->getMockupUrl()"
                        alt="Mockup"
                        :download-url="$task->getMockupUrl()" />
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

            <!-- File thiết kế hoàn chỉnh -->
            @if($task->getCurrentDesignUrl())
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-palette mr-2 text-green-600"></i>Thiết kế hiện tại
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
                    $sideNames = ['Mặt trước', 'Mặt sau', 'Mặt trái', 'Mặt phải', 'Mặt trên'];
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
                                alt="Design {{ $sideName }}"
                                class="w-full h-32 object-cover rounded border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
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

                    <!-- Thông tin tổng quan -->
                    <div class="mt-6 p-4 bg-green-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-green-500 mr-2"></i>
                            <span class="text-sm text-green-700">
                                Designer đã hoàn thành <strong>{{ count($designUrls) }} thiết kế</strong> cho {{ $task->sides_count }} mặt.
                                Vui lòng xem xét từng thiết kế trước khi phê duyệt.
                            </span>
                        </div>
                    </div>
                    @else
                    <!-- Single design file -->
                    @if($task->latestRevision->isDesignImage())
                    <x-image-viewer
                        :src="$task->getCurrentDesignUrl()"
                        alt="Design"
                        :download-url="$task->getCurrentDesignUrl()" />
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-file text-6xl text-blue-500 mb-4"></i>
                        <p class="text-gray-600 mb-4">File {{ strtoupper($task->latestRevision->getDesignFileExtension()) }}</p>
                        <a href="{{ $task->getCurrentDesignUrl() }}"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
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
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-history mr-2 text-purple-600"></i>Lịch sử chỉnh sửa
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

                            @php
                            $designUrls = $revision->getDesignUrls();
                            $sideNames = ['Mặt trước', 'Mặt sau', 'Mặt trái', 'Mặt phải', 'Mặt trên'];
                            @endphp

                            @if(count($designUrls) > 1)
                            <!-- Multiple design files -->
                            <div class="mb-2">
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                    {{ count($designUrls) }} files thiết kế
                                </span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                @foreach($designUrls as $index => $designUrl)
                                @php
                                $sideName = $sideNames[$index] ?? "Mặt " . ($index + 1);
                                $isImage = $revision->isDesignImage($designUrl);
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-2 hover:shadow-md transition-shadow">
                                    <div class="mb-1">
                                        <h6 class="text-xs font-medium text-gray-800">{{ $sideName }}</h6>
                                    </div>

                                    @if($isImage)
                                    <img src="{{ $designUrl }}"
                                        class="w-full h-16 object-cover rounded border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                        alt="Design {{ $sideName }} v{{ $revision->version }}"
                                        onclick="openImageModal('{{ $designUrl }}', 'Design {{ $sideName }} v{{ $revision->version }}')">
                                    @else
                                    <div class="w-full h-16 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                                        <i class="fas fa-file text-gray-400 text-sm"></i>
                                    </div>
                                    @endif

                                    <div class="mt-1 text-center">
                                        <a href="{{ $designUrl }}"
                                            target="_blank"
                                            class="text-blue-600 hover:text-blue-700 text-xs font-medium flex items-center justify-center">
                                            <i class="fas fa-download mr-1"></i>Tải
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <!-- Single design file -->
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
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Ghi chú chỉnh sửa -->
            @if($task->revision_notes)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="bg-yellow-500 text-white px-6 py-4">
                    <h6 class="font-semibold flex items-center">
                        <i class="fas fa-edit mr-2"></i>Yêu cầu chỉnh sửa
                    </h6>
                </div>
                <div class="p-6">
                    <p class="text-gray-800 leading-relaxed">{{ $task->revision_notes }}</p>
                </div>
            </div>
            @endif

            <!-- Review Section -->
            @if($task->status === 'completed')
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="bg-green-600 text-white px-6 py-4">
                    <h6 class="font-semibold flex items-center">
                        <i class="fas fa-star mr-2"></i>Review thiết kế
                    </h6>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">Hãy xem xét thiết kế và đưa ra đánh giá của bạn:</p>
                    <div class="flex space-x-4">
                        <button onclick="reviewTask({{ $task->id }}, 'approve')"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>Phê duyệt
                        </button>
                        <button onclick="reviewTask({{ $task->id }}, 'revision')"
                            class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-edit mr-2"></i>Yêu cầu chỉnh sửa
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-clock mr-2 text-gray-600"></i>Timeline
                    </h6>
                </div>
                <div class="p-6">
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        <div class="space-y-6">
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-plus text-white text-xs"></i>
                                </div>
                                <div class="ml-12">
                                    <h6 class="font-semibold text-gray-800">Tạo yêu cầu</h6>
                                    <p class="text-sm text-gray-500">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>

                            @if($task->designer)
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-plus text-white text-xs"></i>
                                </div>
                                <div class="ml-12">
                                    <h6 class="font-semibold text-gray-800">Designer nhận task</h6>
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
                                    <h6 class="font-semibold text-gray-800">Hoàn thành thiết kế</h6>
                                    <p class="text-sm text-gray-500">{{ $task->completed_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            @endif

                            @if($task->status === 'approved')
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-star text-white text-xs"></i>
                                </div>
                                <div class="ml-12">
                                    <h6 class="font-semibold text-gray-800">Đã phê duyệt</h6>
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
                                    <p class="text-sm text-gray-500">Đang chờ designer cập nhật</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            @include('components.design-comments', ['taskId' => $task->id, 'currentUser' => auth()->user()])

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-bolt mr-2 text-orange-600"></i>Hành động nhanh
                    </h6>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('customer.design.create') }}"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-plus mr-2"></i>Tạo yêu cầu mới
                        </a>
                        <a href="{{ route('customer.design.my-tasks') }}"
                            class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-list mr-2"></i>Xem tất cả tasks
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hướng dẫn -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>Hướng dẫn
                    </h6>
                </div>
                <div class="p-6">
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Xem kỹ thiết kế trước khi phê duyệt</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Download file để kiểm tra chất lượng</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Gửi yêu cầu chỉnh sửa chi tiết nếu cần</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span class="text-gray-700">Phê duyệt khi hài lòng với thiết kế</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-star mr-2 text-yellow-500"></i>Review Design Task
                </h3>
            </div>
            <form id="reviewForm" method="POST">
                @csrf
                <div class="p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Hành động:</label>
                        <select name="action" id="action"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            <option value="approve">✅ Phê duyệt thiết kế</option>
                            <option value="revision">🔄 Yêu cầu chỉnh sửa</option>
                        </select>
                    </div>

                    <div class="mb-4" id="revisionNotesDiv" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Ghi chú chỉnh sửa:</label>
                        <textarea name="revision_notes" id="revision_notes" rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 resize-none"
                            placeholder="Mô tả chi tiết những gì cần chỉnh sửa trong thiết kế..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeReviewModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg transition duration-200">
                        Hủy
                    </button>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                        Gửi Review
                    </button>
                </div>
            </form>
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

    // Close image modal when clicking outside
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

    function reviewTask(taskId, action) {
        document.getElementById('reviewForm').action = `/customer/design/tasks/${taskId}/review`;
        document.getElementById('action').value = action;
        document.getElementById('reviewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Show revision notes if action is revision
        if (action === 'revision') {
            document.getElementById('revisionNotesDiv').style.display = 'block';
            document.getElementById('revision_notes').required = true;
        }
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset form
        document.getElementById('revision_notes').value = '';
        document.getElementById('revision_notes').required = false;
        document.getElementById('revisionNotesDiv').style.display = 'none';
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

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReviewModal();
            closeImageModal();
        }
    });
</script>
@endsection