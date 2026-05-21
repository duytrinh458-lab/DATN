@extends('User.layouts.app')

@section('title', 'Trang chủ | Mission Control')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/home.css') }}">
@endpush

@section('content')

{{-- HERO --}}
<section class="home-banner"
         style="background-image:url('{{ asset('images/banner-drone.jpg') }}');">

    <div class="banner-overlay"></div>

    <div class="banner-grid">

        <div class="banner-content">

            <div class="mission-tag">
                UAV STORE // NEXT GEN FLIGHT SYSTEM
            </div>

            <h1 class="glitch-title">
                TƯƠNG LAI CỦA <br>
                <span class="highlight">CÔNG NGHỆ UAV</span>
            </h1>

            <p class="hero-description">
                Hệ sinh thái thương mại điện tử dành riêng cho thiết bị bay không người lái.
                Hiệu năng cao, công nghệ hiện đại và trải nghiệm điều khiển thế hệ mới.
            </p>

            <div class="cta-group">

                <a href="{{ route('user.products') }}"
                   class="btn-primary-glow">

                    KHÁM PHÁ NGAY

                    <span class="material-symbols-outlined">
                        arrow_outward
                    </span>

                </a>

                <a href="#featured-products"
                   class="btn-outline-tech">

                    XEM BỘ SƯU TẬP

                </a>

            </div>

        </div>

        <div class="hero-panel">

            <div class="hero-card">

                <div class="hero-card-label">
                    SYSTEM STATUS
                </div>

                <div class="hero-card-value online">
                    ONLINE
                </div>

            </div>

            <div class="hero-card">

                <div class="hero-card-label">
                    UAV DATABASE
                </div>

                <div class="hero-card-value">
                    {{ number_format($productCount) }}+
                </div>

            </div>

            <div class="hero-card">

                <div class="hero-card-label">
                    NEWS UPDATE
                </div>

                <div class="hero-card-value">
                    {{ number_format($newsCount) }}+
                </div>

            </div>

        </div>

    </div>

</section>

{{-- STATS --}}
<section class="stats-matrix">

    <div class="stat-card">

        <div class="stat-value">
            {{ number_format($productCount) }}
            <span class="unit">+</span>
        </div>

        <div class="stat-label">
            SẢN PHẨM UAV
        </div>

        <div class="stat-bar"></div>

    </div>

    <div class="stat-card">

        <div class="stat-value">
            {{ number_format($newsCount) }}
            <span class="unit">+</span>
        </div>

        <div class="stat-label">
            TIN TỨC CÔNG NGHỆ
        </div>

        <div class="stat-bar"></div>

    </div>

    <div class="stat-card">

        <div class="stat-value">
            24/7
        </div>

        <div class="stat-label">
            HỖ TRỢ KỸ THUẬT
        </div>

        <div class="stat-bar"></div>

    </div>

</section>

{{-- FEATURED PRODUCTS --}}
<section class="featured-products" id="featured-products">

    <div class="section-header">

        <div>

            <div class="section-mini">
                BEST SELLER COLLECTION
            </div>

            <h2 class="tech-title">
                SẢN PHẨM NỔI BẬT
            </h2>

        </div>

        <a href="{{ route('user.products') }}"
           class="section-link">

            XEM TẤT CẢ

        </a>

    </div>

    <div class="product-grid">

        {{-- PRODUCT CARD --}}
        <div class="product-spec-card">

            <div class="card-badge">
                HOT
            </div>

            <div class="image-wrapper">

                <img src="{{ asset('images/drone-camera.jpg') }}"
                     alt="Drone Camera"
                     class="uav-img">

            </div>

            <div class="card-info">

                <div class="product-category">
                    CAMERA UAV
                </div>

                <h3>
                    DRONE CAMERA
                </h3>

                <p class="specs">
                    Quay phim 4K HDR • AI Tracking • Stabilizer Pro
                </p>

                <div class="card-footer">

                    <span class="price">
                        15.000.000đ
                    </span>

                    <button class="btn-buy-hud">

                        <span class="material-symbols-outlined">
                            add_shopping_cart
                        </span>

                    </button>

                </div>

            </div>

        </div>

        {{-- CARD 2 --}}
        <div class="product-spec-card">

            <div class="card-badge">
                NEW
            </div>

            <div class="image-wrapper">

                <img src="{{ asset('images/drone-mini.jpg') }}"
                     alt="Drone Mini"
                     class="uav-img">

            </div>

            <div class="card-info">

                <div class="product-category">
                    MINI UAV
                </div>

                <h3>
                    DRONE MINI
                </h3>

                <p class="specs">
                    Thiết kế nhỏ gọn • Pin lâu • 249g
                </p>

                <div class="card-footer">

                    <span class="price">
                        8.500.000đ
                    </span>

                    <button class="btn-buy-hud">

                        <span class="material-symbols-outlined">
                            add_shopping_cart
                        </span>

                    </button>

                </div>

            </div>

        </div>

        {{-- CARD 3 --}}
        <div class="product-spec-card">

            <div class="card-badge">
                PRO
            </div>

            <div class="image-wrapper">

                <img src="{{ asset('images/drone-pro.jpg') }}"
                     alt="Drone Pro"
                     class="uav-img">

            </div>

            <div class="card-info">

                <div class="product-category">
                    PRO SERIES
                </div>

                <h3>
                    DRONE PRO
                </h3>

                <p class="specs">
                    Hiệu suất mạnh mẽ • SDK Dev • Flight AI
                </p>

                <div class="card-footer">

                    <span class="price">
                        45.000.000đ
                    </span>

                    <button class="btn-buy-hud">

                        <span class="material-symbols-outlined">
                            add_shopping_cart
                        </span>

                    </button>

                </div>

            </div>

        </div>

        {{-- CARD 4 --}}
        <div class="product-spec-card">

            <div class="card-badge">
                ELITE
            </div>

            <div class="image-wrapper">

                <img src="{{ asset('images/drone-camera.jpg') }}"
                     alt="Drone Elite"
                     class="uav-img">

            </div>

            <div class="card-info">

                <div class="product-category">
                    ELITE UAV
                </div>

                <h3>
                    DRONE ELITE
                </h3>

                <p class="specs">
                    Công nghệ bay thế hệ mới • Vision Sensor
                </p>

                <div class="card-footer">

                    <span class="price">
                        65.000.000đ
                    </span>

                    <button class="btn-buy-hud">

                        <span class="material-symbols-outlined">
                            add_shopping_cart
                        </span>

                    </button>

                </div>

            </div>

        </div>

    </div>

</section>

{{-- NEWS --}}
<section class="latest-news">

    <div class="section-header">

        <div>

            <div class="section-mini">
                UAV TECHNOLOGY NEWS
            </div>

            <h2 class="tech-title">
                TIN TỨC MỚI NHẤT
            </h2>

        </div>

    </div>

    <div class="news-placeholder">

        <div class="news-box">
            KHU VỰC HIỂN THỊ TIN TỨC MỚI NHẤT
        </div>

        <div class="news-box">
            CLICK “XEM CHI TIẾT” ĐỂ CHUYỂN SANG TRANG BÀI VIẾT
        </div>

        <div class="news-box">
            BẠN CÓ THỂ LOAD DỮ LIỆU TỪ DATABASE SAU
        </div>

    </div>

</section>

@endsection