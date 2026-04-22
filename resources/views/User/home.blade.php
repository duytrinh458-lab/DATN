@extends('User.layouts.app')

@section('title', 'Trang chủ')

@push('styles')
{{-- Đã đảm bảo đường dẫn Css viết hoa chữ C khớp với thư mục public/Css --}}
<link rel="stylesheet" href="{{ asset('Css/User/home.css') }}">
@endpush

@section('content')
<div class="home-banner">
    <div class="banner-text">
        <h1>Tương lai của những chuyến bay</h1>
        <p>Khám phá công nghệ UAV tiên tiến, mang lại trải nghiệm bay hiện đại và an toàn.</p>
        {{-- Link này sẽ trỏ đến route user.products mà mình đã đặt tên trong web.php --}}
        <a href="{{ route('user.products') }}" class="btn-primary">Xem sản phẩm</a>
    </div>
</div>

<div class="stats-section">
    <div class="stat-box">
        <h2>12,000+</h2>
        <p>Chuyến bay đã thực hiện</p>
    </div>
    <div class="stat-box">
        <h2>15.4k+</h2> {{-- Mình thêm chữ k cho hợp lý về con số khách hàng --}}
        <p>Khách hàng tin tưởng</p>
    </div>
</div>

<div class="featured-products">
    <h2>Lựa chọn đội bay chuyên dụng</h2>
    <div class="product-grid">
        <div class="product-card">
            <img src="{{ asset('images/drone-camera.jpg') }}" alt="Drone Camera">
            <h3>Drone Camera</h3>
            <p>Quay phim, chụp ảnh chuyên nghiệp</p>
            <button class="btn-buy">Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="{{ asset('images/drone-mini.jpg') }}" alt="Drone Mini">
            <h3>Drone Mini</h3>
            <p>Nhỏ gọn, dễ mang theo</p>
            <button class="btn-buy">Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="{{ asset('images/drone-pro.jpg') }}" alt="Drone Chuyên nghiệp">
            <h3>Drone Chuyên nghiệp</h3>
            <p>Hiệu suất cao cho nhà phát triển</p>
            <button class="btn-buy">Mua ngay</button>
        </div>
    </div>
</div>
@endsection