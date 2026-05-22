@extends('User.layouts.app')

@section('title', 'Kho Hạm Đội UAV | Vanguard Command')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')

<div class="product-container-vanguard">

    {{-- SIDEBAR TÌM KIẾM TRẠM RADAR --}}
    <aside class="filter-sidebar">
        <div class="search-row">
            <label class="search-title">
                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">radar</span>
                TÌM KIẾM
            </label>

            <form action="{{ route('user.products') }}" method="GET" class="search-form">
                <input type="text"
                       name="search"
                       placeholder="Nhập tên UAV cần tìm..."
                       value="{{ request('search') }}">
                <button type="submit" class="btn-scan">Tìm Kiếm</button>
            </form>
        </div>
    </aside>

    {{-- GRID SẢN PHẨM CHIẾN THUẬT --}}
    <section class="product-grid">
        @isset($products)
            @forelse($products as $product)
                <div class="product-spec-card">
                    
                    <div class="card-badge">
                        VANGUARD
                    </div>

                    <div class="image-wrapper">
                        <a href="{{ route('user.products.detail', $product->id) }}">
                            <img src="{{ $product->images && $product->images->first()
                                ? asset($product->images->first()->image_url)
                                : asset('images/default-uav.jpg') }}"
                            alt="{{ $product->name }}" class="uav-img">
                        </a>
                    </div>

                    <div class="card-info">
                        <div class="product-category">
                            {{ $product->category->name ?? 'HẠM ĐỘI KHÔNG NGƯỜI LÁI' }}
                        </div>

                        <h3>
                            <a href="{{ route('user.products.detail', $product->id) }}">
                                {{ Str::limit($product->name, 25) }}
                            </a>
                        </h3>

                        <p class="specs">{{ Str::limit($product->description, 55) }}</p>

                        <div class="card-footer">
                            <span class="price">
                                {{ number_format($product->sale_price, 0, ',', '.') }}đ
                            </span>

                            <div class="card-footer-actions">
                                {{-- MUA NGAY --}}
                                <form action="{{ route('user.checkout.buyNow') }}" method="POST" style="flex: 1; margin: 0; display: flex;">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-buy-now">MUA NGAY</button>
                                </form>

                                {{-- THÊM GIỎ HÀNG --}}
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
            @empty
                <div class="no-product">
                    <span class="material-symbols-outlined" style="font-size: 40px; margin-bottom: 10px; display: block;">warning</span>
                    >> SYSTEM_ALERT: KHÔNG TÌM THẤY UAV NÀO TRONG KHO DỮ LIỆU.
                </div>
            @endforelse
        @endisset
    </section>

    {{-- Phân trang --}}
    <div class="pagination-matrix" style="margin-top: 50px; display: flex; justify-content: center;">
        @if(method_exists($products, 'links'))
            {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
        @endif
    </div>
    

</div>

@endsection