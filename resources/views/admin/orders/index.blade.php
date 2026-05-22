@extends('Admin.layouts.admin')

@section('title', 'Quản lý đơn hàng - Vanguard UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-orders.css') }}">
@endpush

@section('content')

<header class="admin-header">

    <div class="header-info">

        <h1>Quản lý đơn hàng</h1>

        <p>
            Theo dõi và xử lý các giao dịch hệ thống
        </p>

        {{-- TOTAL ORDERS --}}
        <div class="total-orders">
            Tổng đơn hàng:
            <span>{{ $orders->total() }}</span>
        </div>

    </div>

</header>

@if(session('success'))

    <div class="alert success-alert">

        <i class="fas fa-check-circle"></i>

        {{ session('success') }}

    </div>

@endif


<div class="table-container shadow-premium">

    <div class="table-header-box">

        <h2 class="card-title">
            Danh sách vận đơn
        </h2>

    </div>

    <div class="table-responsive">

        <table class="uav-table">

            <thead>

                <tr>
                    <th width="90">ID</th>
                    <th>Khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Tổng tiền</th>
                    <th class="center">Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th class="center">Hành động</th>
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


                    {{-- CUSTOMER --}}
                    <td>

                        <div class="user-box">

                            <span class="user-name">
                                {{ $order->full_name }}
                            </span>

                        </div>

                    </td>


                    {{-- PHONE --}}
                    <td>

                        <span class="user-phone">
                            {{ $order->phone }}
                        </span>

                    </td>


                    {{-- TOTAL --}}
                    <td>

                        <span class="order-price">
                            {{ number_format($order->total) }}đ
                        </span>

                    </td>


                    {{-- STATUS --}}
                    <td class="center">

                        <span class="order-status status-{{ $order->status }}">

                            {{ strtoupper($order->status) }}

                        </span>

                    </td>


                    {{-- DATE --}}
                    <td>

                        <span class="order-date">

                            {{ $order->ordered_at
                                ? \Carbon\Carbon::parse($order->ordered_at)->format('d/m/Y H:i')
                                : '-' }}

                        </span>

                    </td>


                    {{-- ACTION --}}
                    <td class="center">

                        <a href="{{ route('admin.orders.show', $order->id) }}"
                           class="btn-view-detail">

                            <i class="fas fa-eye"></i>

                            Chi tiết

                        </a>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="7" class="empty-table">

                        <div class="empty-box">

                            <i class="fas fa-box-open"></i>

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


{{-- PAGINATION --}}
<div class="pagination-wrapper">

    @if ($orders->lastPage() > 1)

        {{ $orders->onEachSide(1)->links() }}

    @else

        <ul>
            <li class="active">
                <span>1</span>
            </li>
        </ul>

    @endif

</div>

@endsection