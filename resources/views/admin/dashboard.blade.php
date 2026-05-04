@extends('Admin.layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/admin/dashboard.css') }}">
@endpush

@section('content')
<div class="dashboard">
    <h1>Trang quản trị</h1>

    <div class="stats">
        <div class="card">
            <h3>Sản phẩm</h3>
            <p>{{ $productCount ?? 0 }}</p>
        </div>

        <div class="card">
            <h3>Đơn hàng</h3>
            <p>{{ $orderCount ?? 0 }}</p>
        </div>

        <div class="card">
            <h3>Người dùng</h3>
            <p>{{ $userCount ?? 0 }}</p>
        </div>

        <div class="card">
            <h3>Doanh thu</h3>
            <p class="revenue">
                {{ isset($revenue) ? number_format($revenue, 0, ',', '.') . ' ₫' : '0 ₫' }}
            </p>
        </div>

        <div class="card">
    <h3>Sản phẩm bán chạy</h3>

    @if($bestProduct)
        <p style="font-size:16px;">
            {{ $bestProduct->name }}
        </p>
        <p style="color:#f59e0b;">
            {{ $bestProduct->total_sold }} đã bán
        </p>
    @else
        <p>Chưa có dữ liệu</p>
    @endif
</div>
    </div>
</div>
@endsection