@extends('User.layouts.app')

@section('title', $category->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/categories.css') }}">
@endpush

@section('content')
<div class="categories-viewport">

    {{-- HEADER --}}
    <div class="categories-title">
        <i class="fa fa-layer-group"></i> {{ $category->name }}
    </div>
    <div class="categories-subtitle">DANH MỤC SẢN PHẨM</div>

    <div class="category-detail-layout">

        {{-- ===== SIDEBAR LỌC ===== --}}
        <aside class="filter-sidebar">
            <form method="GET" id="filter-form">

                <div class="filter-block">
                    <div class="filter-block-title">Sắp xếp</div>
                    <select name="sort" class="filter-select" onchange="document.getElementById('filter-form').submit()">
                        <option value="default"  {{ request('sort','default')=='default'  ? 'selected':'' }}>Mặc định</option>
                        <option value="price_asc" {{ request('sort')=='price_asc'  ? 'selected':'' }}>Giá tăng dần</option>
                        <option value="price_desc"{{ request('sort')=='price_desc' ? 'selected':'' }}>Giá giảm dần</option>
                        <option value="newest"    {{ request('sort')=='newest'     ? 'selected':'' }}>Mới nhất</option>
                        <option value="popular"   {{ request('sort')=='popular'    ? 'selected':'' }}>Phổ biến nhất</option>
                    </select>
                </div>

                {{-- GIÁ --}}
                <div class="filter-block">
                    <div class="filter-block-title">Khoảng giá (₫)</div>
                    <div class="filter-range-row">
                        <input type="number" name="price_min" class="filter-input"
                            placeholder="Từ" value="{{ request('price_min') }}"
                            min="0" max="{{ $priceRange->max_price }}">
                        <span class="filter-range-sep">—</span>
                        <input type="number" name="price_max" class="filter-input"
                            placeholder="Đến" value="{{ request('price_max') }}"
                            min="0" max="{{ $priceRange->max_price }}">
                    </div>
                </div>

                {{-- BRAND --}}
                @if($brands->count())
                <div class="filter-block">
                    <div class="filter-block-title">Thương hiệu</div>
                    @foreach($brands as $brand)
                    <label class="filter-checkbox">
                        <input type="radio" name="brand_id" value="{{ $brand->id }}"
                            {{ request('brand_id') == $brand->id ? 'checked' : '' }}>
                        <span>{{ $brand->name }}</span>
                    </label>
                    @endforeach
                    @if(request('brand_id'))
                    <a href="{{ request()->fullUrlWithoutQuery(['brand_id']) }}" class="filter-clear-link">Bỏ chọn</a>
                    @endif
                </div>
                @endif

                {{-- THỜI GIAN BAY --}}
                <div class="filter-block">
                    <div class="filter-block-title">Thời gian bay tối thiểu</div>
                    <div class="filter-spec-row">
                        <input type="number" name="flight_min" class="filter-input"
                            placeholder="Phút" value="{{ request('flight_min') }}" min="0">
                        <span class="filter-unit">phút</span>
                    </div>
                </div>

                {{-- CAMERA --}}
                <div class="filter-block">
                    <div class="filter-block-title">Camera tối thiểu</div>
                    <div class="filter-spec-row">
                        <input type="number" name="camera_min" class="filter-input"
                            placeholder="MP" value="{{ request('camera_min') }}" min="0" step="0.1">
                        <span class="filter-unit">MP</span>
                    </div>
                </div>

                {{-- CÂN NẶNG --}}
                <div class="filter-block">
                    <div class="filter-block-title">Trọng lượng tối đa</div>
                    <div class="filter-spec-row">
                        <input type="number" name="weight_max" class="filter-input"
                            placeholder="kg" value="{{ request('weight_max') }}" min="0" step="0.1">
                        <span class="filter-unit">kg</span>
                    </div>
                </div>

                {{-- CÒN HÀNG --}}
                <div class="filter-block">
                    <label class="filter-checkbox">
                        <input type="checkbox" name="in_stock" value="1"
                            {{ request('in_stock') ? 'checked' : '' }}>
                        <span>Chỉ hiện còn hàng</span>
                    </label>
                </div>

                <button type="submit" class="filter-btn-apply">Áp dụng</button>
                <a href="{{ route('user.categories.show', $category->slug) }}" class="filter-btn-reset">Xoá bộ lọc</a>

            </form>
        </aside>

        {{-- ===== PRODUCT GRID ===== --}}
        <div class="product-container-vanguard">

            {{-- KẾT QUẢ --}}
            <div class="result-bar">
                <span class="result-count">{{ $products->total() }} sản phẩm</span>
            </div>

            <section class="product-grid">
                @forelse($products as $product)
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
                            <p>{{ Str::limit($product->description, 55) }}</p>

                            {{-- SPECS MINI --}}
                            <div class="product-specs-mini">
                                @if($product->flight_time)
                                <span><i class="fa fa-clock"></i> {{ $product->flight_time }} phút</span>
                                @endif
                                @if($product->camera_mp)
                                <span><i class="fa fa-camera"></i> {{ $product->camera_mp }}MP</span>
                                @endif
                                @if($product->weight)
                                <span><i class="fa fa-weight-hanging"></i> {{ $product->weight }}kg</span>
                                @endif
                            </div>

                            <div class="price-box">

    @if($product->original_price > $product->sale_price)
        <div class="price-original">
            {{ number_format($product->original_price, 0, ',', '.') }}₫
        </div>
    @endif

    <div class="price-sale">
        {{ number_format($product->sale_price, 0, ',', '.') }}₫
    </div>

</div>
                        </div>
                    </a>

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
                <div class="empty-state">
                    <i class="fa fa-box-open fa-2x"></i>
                    <p>Không có sản phẩm phù hợp với bộ lọc.</p>
                    <a href="{{ route('user.categories.show', $category->slug) }}">Xoá bộ lọc</a>
                </div>
                @endforelse
            </section>

            {{-- PAGINATION --}}
            <div class="pagination-wrap">
                {{ $products->links() }}
            </div>

        </div>
    </div>
</div>
@endsection