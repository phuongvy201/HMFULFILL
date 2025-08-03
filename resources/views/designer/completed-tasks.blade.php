@extends('layouts.app')

@section('title', 'Tasks Đã Hoàn Thành')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-check-circle mr-3"></i>Tasks Đã Hoàn Thành
        </h2>
        <a href="{{ route('designer.tasks.index') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>

    @if($completedTasks->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($completedTasks as $task)
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h6 class="font-semibold text-gray-800 truncate">{{ $task->title }}</h6>
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                    Hoàn thành
                </span>
            </div>
            <div class="p-6">
                @if($task->description)
                <p class="text-gray-600 text-sm mb-4">
                    {{ Str::limit($task->description, 100) }}
                </p>
                @endif

                <!-- Hiển thị hình ảnh mockup -->
                @if($task->mockup_file)
                <div class="mb-4">
                    <img src="{{ $task->mockup_url }}"
                        alt="Mockup"
                        class="w-full rounded-lg border border-gray-200">
                </div>
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

                @if($task->customer)
                <div class="mb-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Khách hàng</p>
                    <p class="font-medium text-gray-800">{{ $task->customer->name }}</p>
                </div>
                @endif

                <div class="mb-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Ngày tạo</p>
                    <p class="text-gray-800">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                </div>

                @if($task->completed_at)
                <div class="mb-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Hoàn thành</p>
                    <p class="text-gray-800">{{ $task->completed_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
            </div>
            <div class="px-6 py-4 bg-gray-50">
                <a href="{{ route('designer.tasks.show', $task->id) }}"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-eye mr-2"></i>Xem chi tiết
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $completedTasks->links() }}
    </div>
    @else
    <div class="text-center py-16">
        <i class="fas fa-check-circle text-6xl text-gray-300 mb-6"></i>
        <h5 class="text-xl font-semibold text-gray-600 mb-2">Chưa có tasks hoàn thành nào</h5>
        <p class="text-gray-500 mb-6">Bạn chưa hoàn thành task nào. Hãy bắt đầu với các tasks đang chờ!</p>
        <a href="{{ route('designer.tasks.index') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 inline-flex items-center">
            <i class="fas fa-tasks mr-2"></i>Xem tất cả tasks
        </a>
    </div>
    @endif
</div>
@endsection