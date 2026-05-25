@extends('User.layouts.app')

@section('title', 'Trang chủ | Vanguard Command')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/home.css') }}">
<link rel="stylesheet" href="{{ asset('Css/User/user-news.css') }}">
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
<section class="latest-news" id="latest-news" style="width: 100%; max-width: 1400px; margin: 0 auto; padding: 0 24px 60px;">
    <div class="section-header">
        <div>
            <h2 class="tech-title">TÌNH BÁO MỚI NHẤT</h2>
        </div>
        <a href="{{ route('user.news.index') }}" class="section-link">XEM TOÀN BỘ</a>
    </div>
    
    <div class="news-grid">
    @forelse($latestNews as $item)
        <div class="news-card">
            <a href="{{ route('user.news.show', $item->id) }}" class="news-link">

                <div class="news-image">
                    @if($item->thumbnail)
                        <img src="{{ asset('storage/' . $item->thumbnail) }}" 
                             alt="{{ $item->title }}"
                             onerror="this.onerror=null; this.src='{{ asset('images/default-news.jpg') }}';">
                    @else
                        <div class="no-img"><span class="material-symbols-outlined">satellite_alt</span></div>
                    @endif
                </div>

                <div class="news-content">
                    <h3 class="news-title">
                        {{ Str::limit($item->title, 55) }}
                    </h3>

                    <p class="news-excerpt">
                        {{ Str::limit(strip_tags($item->content), 80) }}
                    </p>

                    <div class="news-meta-footer">
                        <div class="news-date">
                            <span class="material-symbols-outlined icon-sm">schedule</span>
                            {{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') : 'Đang giải mã' }}
                        </div>
                        
                        <div class="news-readmore">
                            ĐỌC TIẾP <span class="material-symbols-outlined">arrow_forward_ios</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="news-empty" style="grid-column: 1 / -1;">
            <span class="material-symbols-outlined">radar</span>
            <p>RADAR CHƯA QUÉT THẤY TÍN HIỆU TIN TỨC NÀO.</p>
        </div>
    @endforelse
</div>
</section>

@endsection