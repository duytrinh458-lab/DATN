@extends('User.layouts.app')

@section('title', $category->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')

<div class="product-container-vanguard">

    <h2 style="margin-bottom:20px;">Danh mục: {{ $category->name }}</h2>

    <section class="product-grid">
        @forelse($products as $product)

            <div class="product-card-v4">

                <!-- CLICK -->
                <a href="{{ route('user.products.detail', $product->id) }}" class="product-link">

                    <div class="img-wrapper">
                        <img src="{{ $product->images && $product->images->first() 
                            ? asset($product->images->first()->image_url) 
                            : asset('images/uav1.jpg') }}">
                    </div>

                    <div class="card-body">
                        <h3>{{ $product->name }}</h3>

                        <p>{{ Str::limit($product->description, 50) }}</p>

                        <div class="price">
                            {{ number_format($product->sale_price ?? $product->price, 0, ',', '.') }}₫
                        </div>
                    </div>

                </a>

                <!-- ACTION -->
                <div class="product-actions">

                    <form action="{{ route('user.checkout.buyNow') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button class="btn-buy">Mua ngay</button>
                    </form>

                    <form action="{{ route('user.cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button class="btn-cart">Thêm giỏ</button>
                    </form>

                </div>

            </div>

        @empty
            <p>Không có sản phẩm</p>
        @endforelse
    </section>

</div>

@endsection