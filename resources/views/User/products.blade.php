@extends('User.layouts.app')

@section('title', 'Sản phẩm')

@push('styles')
{{-- Đã sửa đường dẫn Css viết hoa chữ C cho đúng thực tế folder của Duy --}}
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')
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
            <input type="range" min="5000000" max="200000000" step="1000000">
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
                    {{-- Lấy ảnh từ folder public/images, nếu không có ảnh thì lấy ảnh mặc định --}}
                    <img src="{{ $product->image ? asset('images/' . $product->image) : asset('images/uav1.jpg') }}" alt="{{ $product->name }}">
                    
                    <h3>{{ $product->name }}</h3>
                    <p>{{ $product->description }}</p>
                    <span class="price">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                    
                    <button class="btn-buy">Mua ngay</button>
                </div>
            @endforeach
        @else
            <p>Hiện chưa có sản phẩm nào.</p>
        @endisset
    </section>
</div>
@endsection