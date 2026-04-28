@extends('User.layouts.app')

@section('title', 'Trang chủ | Mission Control')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/home.css') }}">
@endpush

@section('content')
<section class="home-banner" style="background-image: url('{{ asset('images/banner-drone.jpg') }}');">
    <div class="banner-overlay"></div>
    <div class="banner-content">
        <div class="mission-tag">MISSION_01 // TECH_PIONEER</div>
        <h1 class="glitch-title">TƯƠNG LAI CỦA <br><span class="highlight">NHỮNG CHUYẾN BAY</span></h1>
        <p class="hero-description">Khám phá công nghệ UAV tiên tiến, mang lại trải nghiệm bay hiện đại và an toàn tuyệt đối.</p>
        <div class="cta-group">
            <a href="{{ route('user.products') }}" class="btn-primary-glow">
                XEM SẢN PHẨM <span class="material-symbols-outlined">arrow_outward</span>
            </a>
        </div>
    </div>
</section>

<section class="stats-matrix">
    <div class="stat-card">
        <div class="stat-value">12,000<span class="unit">+</span></div>
        <div class="stat-label">CHUYẾN BAY THÀNH CÔNG</div>
        <div class="stat-bar"></div>
    </div>
    <div class="stat-card">
        <div class="stat-value">15.4k<span class="unit">+</span></div>
        <div class="stat-label">PHI CÔNG TIN TƯỞNG</div>
        <div class="stat-bar"></div>
    </div>
</section>

<section class="featured-products">
    <div class="section-header">
        <h2 class="tech-title">ĐỘI BAY CHUYÊN DỤNG</h2>
        <div class="view-all">SQUAD_DATABASE_V1.0</div>
    </div>

    <div class="product-grid">
        <div class="product-spec-card">
            <div class="card-tag">SERIES_07</div>
            <div class="image-wrapper">
                <img src="{{ asset('images/drone-camera.jpg') }}" alt="Drone Camera" class="uav-img">
            </div>
            <div class="card-info">
                <h3>DRONE CAMERA</h3>
                <p class="specs">Quay phim, chụp ảnh chuyên nghiệp // 4K HDR</p>
                <div class="card-footer">
                    <span class="price">15.000.000đ</span>
                    <button class="btn-buy-hud">
                        <span class="material-symbols-outlined">add_shopping_cart</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="product-spec-card">
            <div class="card-tag">MINI_S2</div>
            <div class="image-wrapper">
                <img src="{{ asset('images/drone-mini.jpg') }}" alt="Drone Mini" class="uav-img">
            </div>
            <div class="card-info">
                <h3>DRONE MINI</h3>
                <p class="specs">Nhỏ gọn, dễ mang theo // 249g</p>
                <div class="card-footer">
                    <span class="price">8.500.000đ</span>
                    <button class="btn-buy-hud">
                        <span class="material-symbols-outlined">add_shopping_cart</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="product-spec-card">
            <div class="card-tag">PRO_DEV</div>
            <div class="image-wrapper">
                <img src="{{ asset('images/drone-pro.jpg') }}" alt="Drone Pro" class="uav-img">
            </div>
            <div class="card-info">
                <h3>DRONE PRO</h3>
                <p class="specs">Hiệu suất cao cho nhà phát triển // SDK v3.0</p>
                <div class="card-footer">
                    <span class="price">45.000.000đ</span>
                    <button class="btn-buy-hud">
                        <span class="material-symbols-outlined">add_shopping_cart</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection