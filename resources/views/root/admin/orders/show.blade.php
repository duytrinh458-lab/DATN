@extends('Admin.layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-orders2.css') }}">
@endpush

@section('content')

<div class="order-detail-page">

    {{-- ========================================= --}}
    {{-- HEADER --}}
    {{-- ========================================= --}}
    <header class="admin-header">

        <div class="header-info">

            <h1>Chi tiết đơn hàng</h1>

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

                Quay lại

            </a>

        </div>

    </header>

    {{-- ========================================= --}}
    {{-- GRID --}}
    {{-- ========================================= --}}
    <div class="order-show-grid">

        {{-- LEFT --}}
        <div class="left-column">

            {{-- ================= STATUS ================= --}}
<section class="detail-section">

    <div class="info-card">

        <div class="card-header">
            <h2 class="card-title">
                Xử lý đơn hàng
            </h2>
        </div>

        <div class="status-wrapper">

            <div class="current-status-box">

                <div class="status-label">
                    Trạng thái hiện tại
                </div>

                <div>

                    <span class="order-status status-{{ $order->status }}">

                        @switch(strtolower($order->status))

                            @case('pending')
                                Chờ xử lý
                                @break

                            @case('processing')
                                Đang xử lý
                                @break

                            @case('shipping')
                                Đang giao hàng
                                @break

                            @case('delivered')
                                Đã giao hàng
                                @break

                            @case('cancelled')
                                Đã hủy
                                @break

                            @case('refunded')
                                Đã hoàn tiền
                                @break

                            @default
                                {{ $order->status }}

                        @endswitch

                    </span>

                </div>

            </div>

            <form method="POST"
                  action="{{ route('admin.orders.update', $order->id) }}"
                  class="uav-form-inline">

                @csrf

                <div class="form-group">

                    <label>
                        Cập nhật trạng thái
                    </label>

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

                        <option value="cancelled"
                            {{ $order->status == 'cancelled' ? 'selected' : '' }}>
                            Đã hủy
                        </option>

                        <option value="refunded"
                            {{ $order->status == 'refunded' ? 'selected' : '' }}>
                            Đã hoàn tiền
                        </option>

                    </select>

                </div>

                <button type="submit" class="btn-update">

                    <i class="fas fa-save"></i>

                    Cập nhật

                </button>

            </form>

        </div>

    </div>

</section>

            {{-- ================= SHIPPING ================= --}}
            <section class="detail-section">

                <div class="info-card">

                    <div class="card-header">
                        <h2 class="card-title">
                            Thông tin giao hàng
                        </h2>
                    </div>

                    <div class="shipping-info">

                        <div class="info-row">

                            <span class="label">
                                Người nhận
                            </span>

                            <span class="value">
                                {{ $order->shipping_full_name ?? '-' }}
                            </span>

                        </div>

                        <div class="info-row">

                            <span class="label">
                                Số điện thoại
                            </span>

                            <span class="value">
                                {{ $order->shipping_phone ?? '-' }}
                            </span>

                        </div>

                        <div class="info-row">

                            <span class="label">
                                Địa chỉ
                            </span>

                            <span class="value value-address">

                                {{ $order->shipping_street ?? '' }},

                                {{ $order->shipping_ward ?? '' }},

                                {{ $order->shipping_district ?? '' }},

                                {{ $order->shipping_province ?? '' }}

                            </span>

                        </div>

                    </div>

                </div>

            </section>

        </div>

        {{-- RIGHT --}}
        <div class="right-column">

            {{-- ================= PRODUCTS ================= --}}
            <section class="detail-section">

                <div class="info-card">

                    <div class="card-header">

                        <h2 class="card-title">
                            Sản phẩm trong đơn
                        </h2>

                    </div>

                    <div class="table-responsive">

                        <table class="uav-table">

                            <thead>

                                <tr>

                                    <th>Sản phẩm</th>

                                    <th class="center" width="90">
                                        SL
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

                                    {{-- PRODUCT --}}
                                    <td>

                                        <div class="product-info">

                                            <div class="product-name">

                                                {{ $item->product->name ?? 'Sản phẩm đã xóa' }}

                                            </div>

                                        </div>

                                    </td>

                                    {{-- QTY --}}
                                    <td class="center">

                                        <span class="qty-box">
                                            x{{ $item->quantity }}
                                        </span>

                                    </td>

                                    {{-- PRICE --}}
                                    <td class="right">

                                        <span class="price-text">

                                            {{ number_format($item->unit_price) }}đ

                                        </span>

                                    </td>

                                    {{-- TOTAL --}}
                                    <td class="right">

                                        <span class="order-price">

                                            {{ number_format($item->unit_price * $item->quantity) }}đ

                                        </span>

                                    </td>

                                </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    {{-- TOTAL --}}
                    <div class="total-box">

                        <div class="total-label">
                            Tổng thanh toán
                        </div>

                        <div class="total-price">

                            {{ number_format($order->total) }}đ

                        </div>

                    </div>

                </div>

            </section>

        </div>

    </div>

</div>

@endsection