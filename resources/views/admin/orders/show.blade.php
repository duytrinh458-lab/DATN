@extends('Admin.layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/Admin/orders.css') }}">
@endpush

@section('content')
<div class="order-detail-page">
    <header class="admin-header">
        <div class="header-info">
            <h1>Chi tiết vận đơn</h1>
            <p>Mã đơn hàng: <span class="user-id">#{{ $order->id }}</span></p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.orders.index') }}" class="btn-back-text">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </header>

    <div class="detail-grid">

        {{-- STATUS BOX --}}
        <div class="card shadow-premium status-card">
            <div class="card-header">
                <h3 class="card-title">Xử lý đơn hàng</h3>
            </div>

            <div class="current-status-box">
                <span>Trạng thái hiện tại:</span>
                <span class="order-status status-{{ $order->status }}">
                    {{ strtoupper($order->status) }}
                </span>
            </div>

            <form method="POST" action="{{ route('admin.orders.update', $order->id) }}" class="uav-form-inline">
                @csrf

                <div class="form-group">
                    <select name="status">

                        <option value="pending">Chờ xử lý</option>
<option value="processing">Đang xử lý</option>
<option value="shipping">Đang giao hàng</option>
<option value="delivered">Đã giao hàng</option>

                    </select>
                </div>

                <button type="submit" class="btn btn-update">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
            </form>
        </div>

        {{-- ITEMS --}}
        <div class="card shadow-premium items-card">
            <div class="card-header">
                <h3 class="card-title">Danh mục sản phẩm</h3>
            </div>

            <div class="table-responsive">
                <table class="uav-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="center">Số lượng</th>
                            <th class="right">Đơn giá</th>
                            <th class="right">Thành tiền</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>
                                <span class="user-name">
                                    {{ $item->name }}
                                </span>
                            </td>

                            <td class="center">
                                x{{ $item->quantity }}
                            </td>

                            {{-- FIX: unit_price --}}
                            <td class="right">
                                {{ number_format($item->unit_price) }}đ
                            </td>

                            <td class="right order-price">
                                {{ number_format($item->unit_price * $item->quantity) }}đ
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

    </div>
</div>
@endsection