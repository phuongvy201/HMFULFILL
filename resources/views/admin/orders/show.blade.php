@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->external_id)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.api-orders') }}">Danh sách đơn hàng API</a></li>
                <li class="breadcrumb-item active">Chi tiết đơn hàng #{{ $order->external_id }}</li>
            </ol>
        </nav>
    </div>

    <!-- Order Status -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Đơn hàng #{{ $order->external_id }}</h4>
                            <p class="text-muted mb-0">Tạo bởi: {{ $order->creator->email }}</p>
                        </div>
                        <div>
                            <span class="badge bg-{{ $order->status === 'processed' ? 'success' : 
                                ($order->status === 'pending' ? 'warning' : 
                                ($order->status === 'cancelled' ? 'danger' : 
                                ($order->status === 'on hold' ? 'info' : 'secondary'))) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">External ID:</th>
                            <td>{{ $order->external_id }}</td>
                        </tr>
                        <tr>
                            <th>Warehouse:</th>
                            <td>{{ $order->warehouse }}</td>
                        </tr>
                        <tr>
                            <th>Ngày tạo:</th>
                            <td>{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Cập nhật lần cuối:</th>
                            <td>{{ $order->updated_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        @if($transaction)
                        <tr>
                            <th>Giao dịch:</th>
                            <td>
                                <div>ID: {{ $transaction->id }}</div>
                                <div>Số tiền: ${{ number_format($transaction->amount, 2) }}</div>
                                <div>Trạng thái: {{ $transaction->status }}</div>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin khách hàng</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Họ tên:</th>
                            <td>{{ $order->first_name }} {{ $order->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $order->buyer_email }}</td>
                        </tr>
                        <tr>
                            <th>Điện thoại:</th>
                            <td>{{ $order->phone1 }}</td>
                        </tr>
                        <tr>
                            <th>Địa chỉ:</th>
                            <td>
                                {{ $order->address1 }}<br>
                                @if($order->address2){{ $order->address2 }}<br>@endif
                                {{ $order->city }}, {{ $order->county }}<br>
                                {{ $order->post_code }}<br>
                                {{ $order->country }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Chi tiết sản phẩm</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Part Number</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->part_number }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->print_price, 2) }}</td>
                                    <td>${{ number_format($item->print_price * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Tổng cộng:</th>
                                    <th>${{ number_format($totalAmount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th {
        white-space: nowrap;
    }

    .badge {
        font-size: 0.875rem;
    }
</style>
@endpush
@endsection