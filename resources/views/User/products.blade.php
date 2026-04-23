@extends('User.layouts.app')

@section('title', 'Sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')

<!-- 🔥 FIX CỨNG LAYOUT NGAY TẠI ĐÂY -->
<style>
.product-container {
    display: flex !important;
    gap: 20px !important;
    width: 100% !important;
    max-width: 1400px !important;
    margin: 0 auto !important;
}

.filter-sidebar {
    width: 250px !important;
}

/* GRID */
.product-grid {
    flex: 1 !important;
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 20px !important;
}

/* CARD - FIX DARK THEME */
.product-card {
    background: #0f172a !important; /* 🔥 màu tối */
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 0 15px rgba(0,255,255,0.2);
    color: #fff;
}

/* ẢNH */
.product-card img {
    width: 100% !important;
    height: 200px !important;
    object-fit: cover !important;
    border-radius: 8px;
}

/* TÊN */
.product-card h3 {
    color: #00eaff;
}

/* GIÁ */
.product-card span {
    color: #00ff88;
}

/* BUTTON */
.btn-buy {
    background: linear-gradient(45deg, #00eaff, #007bff);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
}
</style>

<div class="product-container">
    <aside class="filter-sidebar">
        <h3>Bộ lọc</h3>
        <ul>
            <li><a href="#">Drone Camera</a></li>
            <li><a href="#">Mini Series</a></li>
            <li><a href="#">Pro Experience</a></li>
        </ul>
        <div class="price-filter">
            <label>KHOẢNG GIÁ (VNĐ)</label>
            <input type="range" min="0" max="99999999" step="10000000">
        </div>
        <div class="support-box">
            <h4>Hỗ trợ trực tuyến</h4>
            <button class="btn-support">Liên hệ ngay</button>
        </div>
    </aside>

    <section class="product-grid">
        @isset($products)
            @foreach($products as $product)
                <div class="product-card">

                    <img src="{{ $product->images && $product->images->first() 
                        ? asset($product->images->first()->image_url) 
                        : asset('images/uav1.jpg') }}" 
                    alt="{{ $product->name }}">

                    <h3>{{ $product->name }}</h3>

                    <p>{{ $product->description }}</p>

                    <span style="color:green; font-weight:bold;">
                        {{ number_format($product->sale_price, 0, ',', '.') }}₫
                    </span>

                    @if($product->original_price && $product->original_price > $product->sale_price)
                        <span style="text-decoration: line-through; color: #999;">
                            {{ number_format($product->original_price, 0, ',', '.') }}₫
                        </span>
                    @endif

                    <button class="btn-buy">Mua ngay</button>
                </div>
            @endforeach
        @else
            <p>Hiện chưa có sản phẩm nào.</p>
        @endisset
    </section>
</div>
@endsection