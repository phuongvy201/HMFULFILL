@extends('layouts.admin')

@section('title', 'Dashboard Design Tasks')

@section('content-admin')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
            Dashboard Design Tasks
        </h1>
        <a href="{{ route('admin.design.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
            <i class="fas fa-list mr-2"></i>
            Xem danh sách
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-tasks text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tổng cộng</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_tasks'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_tasks'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_tasks'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['approved_tasks'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Doanh thu</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($stats['total_revenue'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Monthly Statistics Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Thống kê theo tháng</h3>
            <div class="h-80">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Doanh thu theo tháng</h3>
            <div class="h-80">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Designers and Recent Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Top Designers -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Designers</h3>
            @if($topDesigners->count() > 0)
            <div class="space-y-4">
                @foreach($topDesigners as $designer)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $designer->designer->first_name }} {{ $designer->designer->last_name }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $designer->designer->email }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ $designer->total_tasks }} tasks</p>
                        <p class="text-xs text-green-600">${{ number_format($designer->total_earnings, 2) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">Chưa có designer nào</p>
            </div>
            @endif
        </div>

        <!-- Recent Tasks -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tasks gần đây</h3>
            @if($recentTasks->count() > 0)
            <div class="space-y-3">
                @foreach($recentTasks as $task)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-palette text-blue-600 text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ Str::limit($task->title, 25) }}</p>
                            <p class="text-xs text-gray-500">{{ $task->customer->first_name }} {{ $task->customer->last_name }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($task->status === 'joined') bg-blue-100 text-blue-800
                            @elseif($task->status === 'completed') bg-green-100 text-green-800
                            @elseif($task->status === 'approved') bg-purple-100 text-purple-800
                            @elseif($task->status === 'revision') bg-orange-100 text-orange-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $task->getStatusDisplayName() }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">{{ $task->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-tasks text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">Chưa có task nào</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Chỉ số hiệu suất</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600 mb-2">
                    @if($stats['total_tasks'] > 0)
                        {{ round(($stats['approved_tasks'] / $stats['total_tasks']) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
                <p class="text-sm text-gray-600">Tỷ lệ hoàn thành</p>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600 mb-2">
                    @if($stats['total_tasks'] > 0)
                        {{ round(($stats['completed_tasks'] / $stats['total_tasks']) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
                <p class="text-sm text-gray-600">Tỷ lệ đang làm</p>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600 mb-2">
                    @if($stats['total_tasks'] > 0)
                        {{ round(($stats['pending_tasks'] / $stats['total_tasks']) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
                <p class="text-sm text-gray-600">Tỷ lệ chờ xử lý</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Statistics Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyStats->pluck('month')) !!},
        datasets: [{
            label: 'Tổng tasks',
            data: {!! json_encode($monthlyStats->pluck('total')) !!},
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 1
        }, {
            label: 'Hoàn thành',
            data: {!! json_encode($monthlyStats->pluck('completed')) !!},
            backgroundColor: 'rgba(34, 197, 94, 0.5)',
            borderColor: 'rgba(34, 197, 94, 1)',
            borderWidth: 1
        }, {
            label: 'Đã duyệt',
            data: {!! json_encode($monthlyStats->pluck('approved')) !!},
            backgroundColor: 'rgba(168, 85, 247, 0.5)',
            borderColor: 'rgba(168, 85, 247, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthlyStats->pluck('month')) !!},
        datasets: [{
            label: 'Doanh thu ($)',
            data: {!! json_encode($monthlyStats->pluck('revenue')) !!},
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            borderColor: 'rgba(34, 197, 94, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});
</script>
@endpush
@endsection
