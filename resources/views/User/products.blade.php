@extends('User.layouts.app')

@section('title', 'Sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')

<style>
.product-container {
    display: flex !important;
    gap: 20px !important;
    width: 100% !important;
    max-width: 1400px !important;
    margin: 20px auto !important;
}

.filter-sidebar {
    width: 250px !important;
    background: #1e293b;
    padding: 20px;
    border-radius: 10px;
    height: fit-content;
}

.filter-sidebar h3 { color: #00eaff; margin-bottom: 15px; }
.filter-sidebar ul { list-style: none; padding: 0; }
.filter-sidebar ul li a { color: #ccc; text-decoration: none; line-height: 2; }
.filter-sidebar ul li a:hover { color: #00eaff; }

/* GRID */
.product-grid {
    flex: 1 !important;
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 20px !important;
}

/* CARD - DARK THEME */
.product-card {
    background: #0f172a !important; 
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 0 15px rgba(0,255,255,0.1);
    color: #fff;
    transition: transform 0.3s;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0 20px rgba(0,255,255,0.3);
}

.product-card img {
    width: 100% !important;
    height: 200px !important;
    object-fit: cover !important;
    border-radius: 8px;
    margin-bottom: 10px;
}

.product-card h3 {
    color: #00eaff;
    font-size: 1.2rem;
    margin: 10px 0;
}

.product-card p {
    font-size: 0.9rem;
    color: #cbd5e1;
    height: 40px;
    overflow: hidden;
}

/* BUTTON */
.btn-buy {
    background: linear-gradient(45deg, #00eaff, #007bff);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    width: 100%;
    cursor: pointer;
    font-weight: bold;
    margin-top: 10px;
    transition: 0.3s;
}

.btn-buy:hover {
    opacity: 0.9;
    box-shadow: 0 0 10px #00eaff;
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
        <div class="price-filter" style="margin-top: 20px;">
            <label style="color: #00ff88; font-size: 0.8rem;">KHOẢNG GIÁ (VNĐ)</label>
            <input type="range" min="0" max="99999999" step="10000000" style="width: 100%;">
        </div>
        <div class="support-box" style="margin-top: 30px; border-top: 1px solid #334155; pt: 20px;">
            <h4 style="color: #fff;">Hỗ trợ trực tuyến</h4>
            <button class="btn-buy" style="background: #334155;">Liên hệ ngay</button>
        </div>
    </aside>

    <section class="product-grid">
        @isset($products)
            @foreach($products as $product)
                <div class="product-card">
                    {{-- Hiển thị ảnh --}}
                    <img src="{{ $product->images && $product->images->first() 
                        ? asset($product->images->first()->image_url) 
                        : asset('images/uav1.jpg') }}" 
                    alt="{{ $product->name }}">

                    <h3>{{ $product->name }}</h3>

                    <p>{{ Str::limit($product->description, 50) }}</p>

                    <div style="margin: 10px 0;">
                        <span style="color:#00ff88; font-weight:bold; font-size: 1.1rem;">
                            {{ number_format($product->sale_price, 0, ',', '.') }}₫
                        </span>

                        @if($product->original_price && $product->original_price > $product->sale_price)
                            <br>
                            <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.9rem;">
                                {{ number_format($product->original_price, 0, ',', '.') }}₫
                            </span>
                        @endif
                    </div>

                    {{-- FORM MUA HÀNG --}}
                    <form action="{{ route('user.orders.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-buy">Mua ngay</button>
                    </form>
                </div>
            @endforeach
        @else
            <p style="color: #fff;">Hiện chưa có sản phẩm nào.</p>
        @endisset
    </section>
</div>
@endsection