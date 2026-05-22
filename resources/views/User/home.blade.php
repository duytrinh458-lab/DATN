@extends('User.layouts.app')

@section('title', 'Trang chủ | Vanguard Command')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/home.css') }}">
@endpush

@section('content')

{{-- HERO --}}
<section class="home-banner" style="background-image:url('{{ asset('images/banner-drone.jpg') }}');">
    <div class="banner-overlay"></div>
    <div class="banner-content">
        <h1 class="glitch-title">
            TƯƠNG LAI CỦA <br>
            <span class="highlight">CÔNG NGHỆ UAV</span>
        </h1>
        <p class="hero-description">
            Hệ sinh thái thương mại điện tử dành riêng cho thiết bị bay không người lái.
            Hiệu năng cao, công nghệ hiện đại và trải nghiệm điều khiển thế hệ mới.
        </p>
        <div class="cta-group">
            <a href="{{ route('user.products') }}" class="btn-primary-glow">
                KHÁM PHÁ NGAY
                <span class="material-symbols-outlined">arrow_outward</span>
            </a>
            <a href="#featured-products" class="btn-outline-tech">
                XEM BỘ SƯU TẬP
            </a>
        </div>
    </div>

    <div class="hero-panel">
        <div class="hero-card">
            <div class="hero-card-label">SYSTEM STATUS</div>
            <div class="hero-card-value online">ONLINE</div>
        </div>
        <div class="hero-card">
            <div class="hero-card-label">UAV DATABASE</div>
            <div class="hero-card-value">{{ number_format($productCount) }}+</div>
        </div>
        <div class="hero-card">
            <div class="hero-card-label">NEWS UPDATE</div>
            <div class="hero-card-value">{{ number_format($newsCount) }}+</div>
        </div>
    </div>
</section>



{{-- FEATURED PRODUCTS --}}
<section class="featured-products" id="featured-products">
    <div class="section-header">
        <div>
            <h2 class="tech-title">HẠM ĐỘI NỔI BẬT</h2>
        </div>
        <a href="{{ route('user.products') }}" class="section-link">XEM TOÀN BỘ</a>
    </div>

    <div class="product-grid">
        @foreach($featuredProducts->take(4) as $product)
            <div class="product-spec-card">
                
                <div class="card-badge">
                    {{ $loop->first ? 'HOT' : 'PRO' }}
                </div>

                <div class="image-wrapper">
                    <a href="{{ route('user.products.detail', $product->id) }}">
                        @php
                            $imageUrl = $product->images->first() ? asset($product->images->first()->image_url) : asset('images/default-uav.jpg');
                        @endphp
                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="uav-img">
                    </a>
                </div>

                <div class="card-info">
                    <div class="product-category">
                        {{ $product->category->name ?? 'VANGUARD UAV' }}
                    </div>

                    <h3>
                        <a href="{{ route('user.products.detail', $product->id) }}" style="text-decoration: none; color: inherit;">
                            {{ Str::limit($product->name, 22) }}
                        </a>
                    </h3>

                    {{-- ĐÃ SỬA: Hiển thị mô tả sản phẩm giống hệt trang Sản phẩm thay vì "Mã hiệu" --}}
                    <p class="specs">
                        {{ Str::limit($product->description, 55) }}
                    </p>

                    <div class="card-footer">
                        <span class="price">
                            {{ number_format($product->sale_price, 0, ',', '.') }}đ
                        </span>

                        <div class="card-footer-actions">
                            <form action="{{ route('user.checkout.buyNow') }}" method="POST" style="flex: 1; margin: 0; display: flex;">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-buy-now">MUA NGAY</button>
                            </form>

                            <form action="{{ route('user.cart.add') }}" method="POST" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-buy-hud" title="Thêm vào kho chờ">
                                    <span class="material-symbols-outlined">add_shopping_cart</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- LATEST NEWS --}}
<section class="latest-news" style="max-width: 1280px; margin: 0 auto; padding: 0 24px 60px;">
    <div class="section-header">
        <div>
            <h2 class="tech-title">TÌNH BÁO MỚI NHẤT</h2>
        </div>
    </div>
    
    <div class="news-placeholder" style="border: 1px dashed rgba(28, 169, 201, 0.4); padding: 50px; text-align: center; color: var(--glow-blue); border-radius: 6px; background: rgba(0, 191, 255, 0.02);">
        <p style="font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; letter-spacing: 1px;">KÊNH TÌNH BÁO ĐANG ĐƯỢC THIẾT LẬP KẾT NỐI...</p>
        <p style="font-size: 13px; margin-top: 10px;">Dữ liệu tin tức sẽ sớm được đồng bộ từ trung tâm chỉ huy.</p>
    </div>
</section>

@endsection