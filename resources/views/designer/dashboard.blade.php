@extends('layouts.admin')

@section('title', 'Designer Dashboard')

@section('content-admin')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-palette mr-3"></i>Designer Dashboard
        </h1>
        <p class="text-gray-600 mt-2">Chào mừng bạn đến với trang quản lý thiết kế</p>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-tasks text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tổng Tasks</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalTasks }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tasks Của Tôi</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $myTasks }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tasks Chờ Xử Lý</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingTasks }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tasks Hoàn Thành</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $completedTasks }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks gần đây -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Tasks Gần Đây</h2>
        </div>
        <div class="p-6">
            @if($recentTasks->count() > 0)
            <div class="space-y-4">
                @foreach($recentTasks as $task)
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-palette text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $task->title }}</h3>
                            <p class="text-sm text-gray-600">{{ Str::limit($task->description, 50) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($task->status === 'joined') bg-blue-100 text-blue-800
                                @elseif($task->status === 'completed') bg-green-100 text-green-800
                                @elseif($task->status === 'approved') bg-purple-100 text-purple-800
                                @elseif($task->status === 'cancelled') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800 @endif">
                            {{ $task->getStatusDisplayName() }}
                        </span>
                        <a href="{{ route('designer.tasks.show', $task->id) }}"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Chưa có tasks nào</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Tasks đã hoàn thành -->
    <div class="mt-8 bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>Tasks Đã Hoàn Thành
            </h2>
            <a href="{{ route('designer.tasks.index') }}"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Xem tất cả tasks
            </a>
        </div>
        <div class="p-6">
            @if($completedTasksList->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($completedTasksList as $task)
                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition duration-200">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-semibold text-gray-800 text-sm truncate">{{ $task->title }}</h3>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                            Hoàn thành
                        </span>
                    </div>

                    @if($task->description)
                    <p class="text-gray-600 text-xs mb-3">
                        {{ Str::limit($task->description, 60) }}
                    </p>
                    @endif

                    <!-- Hiển thị hình ảnh mockup nhỏ -->
                    @if($task->mockup_file)
                    <div class="mb-3">
                        <img src="{{ $task->getMockupUrl() }}"
                            alt="Mockup"
                            class="w-full h-20 object-cover rounded border border-gray-200">
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-2 text-center mb-3">
                        <div>
                            <p class="text-xs text-gray-500">Số mặt</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $task->sides_count }}</p>
                        </div>

                    </div>

                    <div class="mb-2">
                        <p class="text-xs text-gray-500">Khách hàng</p>
                        <p class="text-xs font-medium text-gray-800">{{ $task->getCustomerFullName() }}</p>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>{{ $task->created_at->format('d/m/Y') }}</span>
                        @if($task->completed_at)
                        <span>{{ $task->completed_at->format('d/m/Y') }}</span>
                        @endif
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('designer.tasks.show', $task->id) }}"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-2 px-3 rounded transition duration-200 flex items-center justify-center">
                            <i class="fas fa-eye mr-1"></i>Xem chi tiết
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $completedTasksList->links() }}
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Chưa có tasks hoàn thành nào</p>
                <p class="text-sm text-gray-400 mt-2">Bắt đầu nhận và hoàn thành tasks để thấy chúng ở đây!</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Thao Tác Nhanh</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('designer.tasks.index') }}"
                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition duration-200">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-list text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Xem Tất Cả Tasks</h3>
                        <p class="text-sm text-gray-600">Quản lý và xem danh sách tasks</p>
                    </div>
                </a>

                <div class="flex items-center p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <div class="p-3 rounded-full bg-gray-100 text-gray-600 mr-4">
                        <i class="fas fa-chart-bar text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Thống Kê</h3>
                        <p class="text-sm text-gray-600">Xem báo cáo hiệu suất</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection