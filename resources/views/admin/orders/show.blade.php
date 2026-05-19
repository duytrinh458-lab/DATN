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

            <p>
                Mã đơn hàng:
                <span class="user-id">
                    #{{ $order->id }}
                </span>
            </p>
        </div>

        <div class="header-actions">
            <a href="{{ route('admin.orders.index') }}"
               class="btn-back-text">

                <i class="fas fa-arrow-left"></i>

                Quay lại danh sách
            </a>
        </div>

    </header>

    <div class="detail-grid">

    {{-- ===================================================== --}}
    {{-- STATUS WRAPPER --}}
    {{-- ===================================================== --}}
    <section class="detail-section status-section">

        <div class="section-head">
            <h2>⚡ Xử lý đơn hàng</h2>
        </div>

        <div class="card shadow-premium status-card">

            <div class="current-status-box">

                <span>
                    Trạng thái hiện tại:
                </span>

                <span class="order-status status-{{ $order->status }}">
                    {{ strtoupper($order->status) }}
                </span>

            </div>

            <form method="POST"
                  action="{{ route('admin.orders.update', $order->id) }}"
                  class="uav-form-inline">

                @csrf

                <div class="form-group">

                    <select name="status">

                        <option value="pending"
                            {{ $order->status == 'pending' ? 'selected' : '' }}>
                            Chờ xử lý
                        </option>

                        <option value="processing"
                            {{ $order->status == 'processing' ? 'selected' : '' }}>
                            Đang xử lý
                        </option>

                        <option value="shipping"
                            {{ $order->status == 'shipping' ? 'selected' : '' }}>
                            Đang giao hàng
                        </option>

                        <option value="delivered"
                            {{ $order->status == 'delivered' ? 'selected' : '' }}>
                            Đã giao hàng
                        </option>

                    </select>

                </div>

                <button type="submit"
                        class="btn btn-update">

                    <i class="fas fa-save"></i>

                    Cập nhật

                </button>

            </form>

        </div>

    </section>


    {{-- ===================================================== --}}
    {{-- SHIPPING WRAPPER --}}
    {{-- ===================================================== --}}
    <section class="detail-section shipping-section">

        <div class="section-head">
            <h2>🚚 Thông tin giao hàng</h2>
        </div>

        <div class="card shadow-premium shipping-card">

            <div class="shipping-info">

                <div class="info-row">

                    <span class="label">
                        Người nhận:
                    </span>

                    <span class="value">
                        {{ $order->shipping_full_name ?? 'Không có dữ liệu' }}
                    </span>

                </div>

                <div class="info-row">

                    <span class="label">
                        Số điện thoại:
                    </span>

                    <span class="value">
                        {{ $order->shipping_phone ?? 'Không có dữ liệu' }}
                    </span>

                </div>

                <div class="info-row">

                    <span class="label">
                        Địa chỉ:
                    </span>

                    <span class="value">

                        {{ $order->shipping_street ?? '' }},

                        {{ $order->shipping_ward ?? '' }},

                        {{ $order->shipping_district ?? '' }},

                        {{ $order->shipping_province ?? '' }}

                    </span>

                </div>

            </div>

        </div>

    </section>


    {{-- ===================================================== --}}
    {{-- PRODUCT WRAPPER --}}
    {{-- ===================================================== --}}
    <section class="detail-section product-section">

        <div class="section-head">
            <h2>📦 Danh mục sản phẩm</h2>
        </div>

        <div class="card shadow-premium items-card">

            <div class="table-responsive">

                <table class="uav-table">

                    <thead>

                        <tr>

                            <th>
                                Sản phẩm
                            </th>

                            <th class="center">
                                Số lượng
                            </th>

                            <th class="right">
                                Đơn giá
                            </th>

                            <th class="right">
                                Thành tiền
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($items as $item)

                        <tr>

                            <td>

                                <span class="user-name">

                                    {{ $item->product->name ?? 'Sản phẩm đã xóa' }}

                                </span>

                            </td>

                            <td class="center">

                                x{{ $item->quantity }}

                            </td>

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

    </section>

</div>
@endsection