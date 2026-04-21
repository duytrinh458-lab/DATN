@extends('layouts.app')

@section('title', 'Sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/products.css') }}">
@endpush

@section('content')
<div class="product-container">
    <aside class="filter-sidebar">
        <h3>Bộ lọc</h3>
        <ul>
            <li><a href="#">Drone Camera</a></li>
            <li><a href="#">Mini Series</a></li>
            <li><a href="#">Pro Developer</a></li>
        </ul>
        <div class="price-filter">
            <label>Khoảng giá (VNĐ)</label>
            <input type="range" min="5000000" max="200000000" step="1000000">
        </div>
    </aside>

    <section class="product-grid">
        <div class="product-card">
            <img src="{{ asset('images/uav1.jpg') }}" alt="Vanguard X-1 Phantom">
            <h3>Vanguard X-1 Phantom</h3>
            <p>Surveillance Unit</p>
            <span class="price">24,500,000₫</span>
            <button class="btn-buy">Mua ngay</button>
        </div>
        <!-- thêm các sản phẩm khác -->
    </section>
</div>
@endsection
