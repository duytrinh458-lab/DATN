@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
<div class="banner">
    <h1>Tương lai của những chuyến bay</h1>
    <p>Tại Cửa Hàng Máy Bay UAV, chúng tôi cung cấp các thiết bị bay chuyên dụng, từ drone camera đến drone công nghiệp, đáp ứng mọi nhu cầu bay của bạn.</p>
    <div class="btn-group">
        <button>Mua ngay</button>
        <button>Chi tiết sản phẩm</button>
    </div>
</div>

<div class="section">
    <h2>Lựa chọn đội bay chuyên dụng</h2>
    <div class="products-grid">
        <div class="product-card">
            <img src="/images/drone-camera.jpg" alt="Drone Camera">
            <h3>Drone Camera</h3>
            <p>Giá: $1,200</p>
        </div>
        <div class="product-card">
            <img src="/images/drone-mini.jpg" alt="Drone Mini">
            <h3>Drone Mini</h3>
            <p>Giá: $850</p>
        </div>
        <div class="product-card">
            <img src="/images/drone-pro.jpg" alt="Drone Chuyên nghiệp">
            <h3>Drone Chuyên nghiệp</h3>
            <p>Giá: $4,200</p>
        </div>
    </div>
</div>

<div class="section">
    <h2>Thiết bị bay có sẵn</h2>
    <div class="products-grid">
        <div class="product-card">
            <img src="/images/apex.jpg" alt="Apex Stratos X">
            <h3>Apex Stratos X</h3>
            <button>Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="/images/horizon.jpg" alt="Horizon Stealth Z">
            <h3>Horizon Stealth Z</h3>
            <button>Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="/images/skyhawk.jpg" alt="Skyhawk Pro M">
            <h3>Skyhawk Pro M</h3>
            <button>Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="/images/vortex.jpg" alt="Vortex Edge FPV">
            <h3>Vortex Edge FPV</h3>
            <button>Mua ngay</button>
        </div>
    </div>
</div>

<div class="section">
    <h2>Tin tức hàng không</h2>
    <div class="products-grid">
        <div class="product-card">
            <img src="/images/news1.jpg" alt="Tin tức UAV">
            <h3>Công nghệ UAV mới nhất</h3>
            <p>Bài viết về xu hướng UAV 2026</p>
        </div>
        <div class="product-card">
            <img src="/images/news2.jpg" alt="Ứng dụng UAV">
            <h3>Ứng dụng UAV trong nông nghiệp</h3>
            <p>Giải pháp giám sát mùa vụ bằng drone</p>
        </div>
    </div>
</div>
@endsection
