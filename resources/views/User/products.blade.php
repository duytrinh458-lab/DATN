@extends('User.layouts.app')

@section('title', 'Sản phẩm UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')
<div class="product-container">

    {{-- SIDEBAR --}}
    <aside class="filter-sidebar">
        <h3>Tìm kiếm</h3>
        <div class="search-box">
            <form action="{{ route('user.products') }}" method="GET">
                <input type="text" name="search" placeholder="Nhập tên drone..." value="{{ request('search') }}">
            </form>
        </div>

        <h3>Danh mục</h3>
        <ul>
            <li><a href="#">Drone Camera</a></li>
            <li><a href="#">Mini Series</a></li>
            <li><a href="#">Pro Experience</a></li>
        </ul>

        <div class="support-box" style="margin-top: 30px; border-top: 1px solid #334155; padding-top: 20px;">
            <h4 style="color: #fff; font-size: 0.9rem;">Hỗ trợ trực tuyến</h4>
            <button class="btn-buy" style="background: #334155; margin-top: 10px;">Liên hệ ngay</button>
        </div>
    </aside>

    {{-- GRID SẢN PHẨM --}}
    <section class="product-grid">
        @isset($products)
            @foreach($products as $product)
                <div class="product-card">
                    <img src="{{ $product->images && $product->images->first() 
                        ? asset($product->images->first()->image_url) 
                        : asset('images/uav1.jpg') }}" 
                    alt="{{ $product->name }}">

                    <h3>{{ $product->name }}</h3>

                    <p>{{ Str::limit($product->description, 50) }}</p>

                    <div class="price">
                        {{ number_format($product->sale_price, 0, ',', '.') }}₫
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="product-actions">

                        {{-- MUA NGAY --}}
                        <form action="{{ route('user.orders.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn-buy">Mua ngay</button>
                        </form>

                        {{-- THÊM VÀO GIỎ HÀNG --}}
                        <form action="{{ route('user.cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn-cart">Thêm giỏ</button>
                        </form>

                    </div>

                </div>
            @endforeach
        @else
            <p style="color: #fff;">Hiện chưa có sản phẩm nào.</p>
        @endisset
    </section>

</div>
@endsection