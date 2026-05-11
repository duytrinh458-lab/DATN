@extends('Admin.layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/admin/dashboard.css') }}">
@endpush

@section('content')
<div class="dashboard">
    <div class="header-action">
        <h1>Trang quản trị UAV Shop</h1>
        
        <a href="{{ route('admin.qr.index') }}" class="btn-qr" style="text-decoration: none; display: inline-block;">
            ⚙️ Cấu Hình QR Nạp Tiền
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success">
            ✔️ {{ session('success') }}
        </div>
    @endif

    <div class="stats">

        <div class="card">
            <h3>Sản phẩm (UAV)</h3>
            <p>{{ $productCount ?? 0 }}</p>
        </div>

        <div class="card">
            <h3>Tổng Đơn hàng</h3>
            <p>{{ $orderCount ?? 0 }}</p>
        </div>

        <div class="card">
            <h3>Khách hàng (B2C)</h3>
            <p>{{ $userCount ?? 0 }}</p>
        </div>

        <div class="card">
            <h3>Tổng Doanh thu</h3>
            <p class="revenue">
                {{ isset($revenue) ? number_format($revenue, 0, ',', '.') . ' ₫' : '0 ₫' }}
            </p>
        </div>

        {{-- 💬 BÌNH LUẬN --}}
        <div class="card">
            <h3>Lượt Đánh giá</h3>
            <p>{{ $commentCount ?? 0 }}</p>
        </div>

        {{-- ⭐ SẢN PHẨM BÁN CHẠY --}}
        <div class="card">
            <h3>UAV Bán Chạy Nhất</h3>

            @if(isset($bestProduct) && $bestProduct)
                <p style="font-size:16px; margin-bottom: 5px; color: #111827; font-weight: 700;">
                    {{ $bestProduct->name }}
                </p>
                <p style="color:#f59e0b; font-size: 20px;">
                    {{ $bestProduct->total_sold }} chiếc
                </p>
            @else
                <p style="font-size: 16px; color: #6b7280;">Chưa có dữ liệu giao dịch</p>
            @endif
        </div>

    </div>
</div>
@endsection