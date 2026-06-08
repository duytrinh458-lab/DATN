@extends('admin.layouts.admin')

@section('title', 'Quản lý đơn hàng - Vanguard UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-orders2.css') }}">
@endpush

@section('content')

<div class="admin-content">

    {{-- HEADER --}}
    <header class="admin-header">

        <div class="header-info">

            <h1>
                Quản lý đơn hàng
            </h1>

            <p>
                Theo dõi và xử lý các đơn hàng trong hệ thống
            </p>

            <div class="total-orders">

                Tổng số đơn hàng:
                <span>{{ $orders->total() }}</span>

            </div>

        </div>

    </header>


    {{-- THÔNG BÁO --}}
    @if(session('success'))

        <div class="alert success-alert">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif


    {{-- BẢNG ĐƠN HÀNG --}}
    <div class="table-card">

        <div class="table-header-box">

            <h2 class="card-title">
                Danh sách đơn hàng
            </h2>

        </div>

        <div class="table-responsive">

            <table class="uav-table">

                <thead>

                    <tr>

                        <th width="90">
                            Mã đơn
                        </th>

                        <th>
                            Khách hàng
                        </th>

                        <th>
                            Số điện thoại
                        </th>

                        <th>
                            Tổng tiền
                        </th>

                        <th class="center">
                            Trạng thái
                        </th>

                        <th>
                            Ngày đặt
                        </th>

                        <th class="center">
                            Hành động
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($orders as $order)

                        <tr>

                            {{-- ID --}}
                            <td>

                                <span class="user-id">
                                    #{{ $order->id }}
                                </span>

                            </td>


                            {{-- KHÁCH HÀNG --}}
                            <td>

                                <div class="user-info-cell">

                                    <div class="avatar">

                                        @if(!empty($order->user_avatar) && file_exists(public_path($order->user_avatar)))

                                            <img src="{{ asset($order->user_avatar) }}"
                                                 class="avatar-img"
                                                 alt="{{ $order->full_name }}">

                                        @else

                                            <div class="avatar-text">
                                                {{ strtoupper(substr($order->full_name,0,1)) }}
                                            </div>

                                        @endif

                                    </div>

                                    <span class="user-name">
                                        {{ $order->full_name }}
                                    </span>

                                </div>

                            </td>


                            {{-- SỐ ĐIỆN THOẠI --}}
                            <td>

                                <span class="user-phone">

                                    {{ $order->phone }}

                                </span>

                            </td>


                            {{-- TỔNG TIỀN --}}
                            <td>

                                <span class="order-price">

                                    {{ number_format($order->total) }}đ

                                </span>

                            </td>


                            {{-- TRẠNG THÁI --}}
                            <td class="center">

                                <span class="status-badge status-{{ $order->status }}">

                                    @switch($order->status)

                                        @case('pending')
                                            Chờ xử lý
                                            @break

                                        @case('processing')
                                            Đang xử lý
                                            @break

                                        @case('shipping')
                                            Đang giao
                                            @break

                                        @case('delivered')
                                            Đã giao
                                            @break

                                        @case('cancelled')
                                            Đã hủy
                                            @break

                                        @case('refunded')
                                            Đã hoàn tiền
                                            @break

                                        @default
                                            {{ ucfirst($order->status) }}

                                    @endswitch

                                </span>

                            </td>


                            {{-- NGÀY TẠO --}}
                            <td>

                                <span class="order-date">

                                    {{ $order->ordered_at
                                        ? \Carbon\Carbon::parse($order->ordered_at)->format('d/m/Y H:i')
                                        : '-' }}

                                </span>

                            </td>


                            {{-- HÀNH ĐỘNG --}}
                            <td class="center">

                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="btn-view-detail">

                                    <i class="fa-solid fa-eye"></i>

                                    <span>
                                        Chi tiết
                                    </span>

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="empty-table">

                                <div class="empty-box">

                                    <i class="fa-solid fa-box-open"></i>

                                    <p>
                                        Chưa có đơn hàng nào
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


     {{-- PHÂN TRANG --}}
<div class="pagination-wrapper">
    @if(method_exists($orders, 'links'))
        {{ $orders->onEachSide(0)->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
    @endif
</div>

</div>

@endsection