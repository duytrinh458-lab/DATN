@extends('User.layouts.app')

@section('title', 'Sản phẩm UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')

<div class="product-container-vanguard">

    {{-- SIDEBAR --}}
<aside class="filter-sidebar">

    <div class="search-row">
        <label class="search-title">Tìm kiếm</label>

        <form action="{{ route('user.products') }}" method="GET" class="search-form">
            <input type="text"
                   name="search"
                   placeholder="SCANNING_SERIAL..."
                   value="{{ request('search') }}">
        </form>

        </div>

    </aside>

    {{-- GRID SẢN PHẨM --}}
    <section class="product-grid">

        @isset($products)

            @foreach($products as $product)

                <div class="product-card-v4">

                    <a href="{{ route('user.products.detail', $product->id) }}" class="product-link">

                        <div class="img-wrapper">
                            <img src="{{ $product->images && $product->images->first()
                                ? asset($product->images->first()->image_url)
                                : asset('images/uav1.jpg') }}"
                            alt="{{ $product->name }}">
                        </div>

                        <div class="card-body">

                            <h3>{{ $product->name }}</h3>

                            <p>{{ Str::limit($product->description, 50) }}</p>

                            <div class="price">
                                {{ number_format($product->sale_price, 0, ',', '.') }}₫
                            </div>

                        </div>

                    </a>

                    <div class="card-body" style="padding-top: 0;">

                        <div class="product-actions">

                            <form action="{{ route('user.checkout.buyNow') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-buy">Mua ngay</button>
                            </form>

                            <form action="{{ route('user.cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-cart">Thêm giỏ</button>
                            </form>

                        </div>

                    </div>

                </div>

            @endforeach

        @else

            <p class="no-product">>> NO_DATA_FOUND: 0 PRODUCTS AVAILABLE.</p>

        @endisset

    </section>

</div>

@endsection