@extends('Admin.layouts.admin')

@section('title', 'Quản lý đơn hàng - Vanguard UAV')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/admin/users.css') }}">
    <link rel="stylesheet" href="{{ asset('Css/admin/orders.css') }}">
@endpush

@section('content')
    <header class="admin-header">
        <div class="header-info">
            <h1>Quản lý đơn hàng</h1>
            <p>Theo dõi và xử lý các giao dịch hệ thống</p>
        </div>
    </header>

    @if(session('success'))
        <div class="alert success-alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="table-container shadow-premium">
        <div class="table-header-box">
            <h2 class="card-title">Danh sách vận đơn</h2>
        </div>
        
        <div class="table-responsive">
            <table class="uav-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Tổng tiền</th>
                        <th class="center">Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="center">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td><span class="user-id">#{{ $order->id }}</span></td>
                        <td><span class="user-name">{{ $order->full_name }}</span></td>
                        <td><span class="user-phone">{{ $order->phone }}</span></td>
                        <td><span class="order-price">{{ number_format($order->total) }}đ</span></td>

                        <td class="center">
                            <span class="order-status status-{{ $order->status }}">
                                {{ strtoupper($order->status) }}
                            </span>
                        </td>

                        {{-- 🔥 FIX: created_at → ordered_at --}}
                        <td>
                            <span class="order-date">
                                {{ $order->ordered_at 
                                    ? \Carbon\Carbon::parse($order->ordered_at)->format('d/m/Y H:i') 
                                    : '-' }}
                            </span>
                        </td>

                        <td class="center">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-view-detail">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
@endsection