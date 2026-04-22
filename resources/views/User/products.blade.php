@extends('layouts.app')

@section('title', 'Sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')
<div class="product-container">
    <!-- Sidebar bộ lọc -->
    <aside class="filter-sidebar">
        <h3>Bộ lọc</h3>
        <ul>
            <li><a href="#">Drone Camera</a></li>
            <li><a href="#">Mini Series</a></li>
            <li><a href="#">Pro Experience</a></li>
        </ul>
        <div class="price-filter">
            <label>KHOẢNG GIÁ (VNĐ)</label>
            <input type="range" min="5000000" max="200000000" step="1000000">
        </div>
        <div class="support-box">
            <h4>Hỗ trợ trực tuyến</h4>
            <button class="btn-support">Liên hệ ngay</button>
        </div>
    </aside>

    <!-- Grid sản phẩm -->
    <section class="product-grid">
        <div class="product-card">
            <img src="{{ asset('images/uav1.jpg') }}" alt="Vanguard X9 Phantom">
            <h3>Vanguard X9 Phantom</h3>
            <p>Surveillance Unit</p>
            <span class="price">24,500,000₫</span>
            <button class="btn-buy">Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="{{ asset('images/uav2.jpg') }}" alt="Keiula Mini S8">
            <h3>Keiula Mini S8</h3>
            <p>Lightweight Scout</p>
            <span class="price">42,900,000₫</span>
            <button class="btn-buy">Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="{{ asset('images/uav3.jpg') }}" alt="Titan Cargo MK-II">
            <h3>Titan Cargo MK-II</h3>
            <p>Industrial Logistics</p>
            <span class="price">89,000,000₫</span>
            <button class="btn-buy">Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="{{ asset('images/uav4.jpg') }}" alt="Apex Racer FPV">
            <h3>Apex Racer FPV</h3>
            <p>Extreme Performance</p>
            <span class="price">18,200,000₫</span>
            <button class="btn-buy">Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="{{ asset('images/uav5.jpg') }}" alt="Oracle Cinema Pro">
            <h3>Oracle Cinema Pro</h3>
            <p>Mobile Production</p>
            <span class="price">56,000,000₫</span>
            <button class="btn-buy">Mua ngay</button>
        </div>
        <div class="product-card">
            <img src="{{ asset('images/uav6.jpg') }}" alt="Cadet Trainer V2">
            <h3>Cadet Trainer V2</h3>
            <p>Education Mastery</p>
            <span class="price">7,500,000₫</span>
            <button class="btn-buy">Mua ngay</button>
        </div>
    </section>
</div>
@endsection
