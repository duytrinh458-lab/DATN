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
<div class="filter-sidebar">

    {{-- SEARCH ROW --}}
    <div class="search-row">
        <label class="search-title">
            <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">radar</span>
            TÌM KIẾM
        </label>

        <form action="{{ route('user.categories.show', $category->slug) }}" method="GET" class="search-form" id="filterForm">
            {{-- Giữ lại filter khi search --}}
            <input type="hidden" name="sort"       value="{{ request('sort') }}">
            <input type="hidden" name="price_min"  value="{{ request('price_min') }}">
            <input type="hidden" name="price_max"  value="{{ request('price_max') }}">
            <input type="hidden" name="brand_id"   value="{{ request('brand_id') }}">
            <input type="hidden" name="flight_min" value="{{ request('flight_min') }}">
            <input type="hidden" name="camera_min" value="{{ request('camera_min') }}">
            <input type="hidden" name="weight_max" value="{{ request('weight_max') }}">

            <input type="text"
                   name="search"
                   placeholder="Nhập tên UAV cần tìm..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn-scan">Tìm Kiếm</button>
        </form>

        {{-- NÚT ẨN/HIỆN BỘ LỌC --}}
        <button class="btn-filter-toggle" id="btnFilterToggle" type="button">
            <span class="material-symbols-outlined filter-icon">tune</span>
            <span class="filter-label">Bộ lọc</span>
        </button>
    </div>

    {{-- FILTER PANEL --}}
    <div class="filter-panel open" id="filterPanel">
        <div class="filter-group">
            <div class="filter-group-title">
                <i class="fas fa-sort-amount-down"></i> SẮP XẾP
            </div>
            <select class="filter-select">
                <option value="default">Mặc định</option>
                <option value="price-asc">Giá tăng dần</option>
                <option value="price-desc">Giá giảm dần</option>
            </select>
        </div>

        <div class="filter-group">
            <div class="filter-group-title">
                <i class="fas fa-money-bill-wave"></i> KHOẢNG GIÁ ($)
            </div>
            <div class="filter-range-row">
                <input type="number" class="filter-input" placeholder="Từ">
                <span style="color: rgba(0,255,255,0.5);">—</span>
                <input type="number" class="filter-input" placeholder="Đến">
            </div>
        </div>

        <div class="filter-group">
            <div class="filter-group-title">
                <i class="fas fa-award"></i> THƯƠNG HIỆU
            </div>
            <select class="filter-select">
                <option value="">-- Tất cả thương hiệu --</option>
                <option value="dji">DJI</option>
                <option value="autel">Autel Robotics</option>
            </select>
        </div>

        <div class="filter-group">
            <div class="filter-group-title">
                <i class="fas fa-plane"></i> THỜI GIAN BAY TỐI THIỂU
            </div>
            <div class="filter-spec-row">
                <input type="number" class="filter-input" placeholder="Phút">
                <span class="unit-label" style="color: #7fa2b0; font-size: 13px;">phút</span>
            </div>
        </div>

        <div class="filter-group">
            <div class="filter-group-title">
                <i class="fas fa-camera"></i> CAMERA TỐI THIỂU
            </div>
            <div class="filter-spec-row">
                <input type="number" class="filter-input" placeholder="MP">
                <span class="unit-label" style="color: #7fa2b0; font-size: 13px;">MP</span>
            </div>
        </div>

        <div class="filter-group">
            <div class="filter-group-title">
                <i class="fas fa-hourglass-half"></i> TRỌNG LƯỢNG TỐI ĐA
            </div>
            <div class="filter-spec-row">
                <input type="number" class="filter-input" placeholder="kg">
                <span class="unit-label" style="color: #7fa2b0; font-size: 13px;">kg</span>
            </div>
        </div>

        <button type="submit" class="btn-apply-filter">ÁP DỤNG</button>
        <a href="#" class="btn-clear-filter">Xóa bộ lọc</a>

    </div>
</div>

</div>

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
    @if(method_exists($products, 'links'))
        {{ $products->onEachSide(0)->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
    @endif
</div>

        </div>
    </div>
</div>

@push('scripts')
<script>
const btnToggle   = document.getElementById('btnFilterToggle');
const filterPanel = document.getElementById('filterPanel');
 
function setPanel(open) {
    filterPanel.classList.toggle('open', open);
    btnToggle.classList.toggle('active', open);
    btnToggle.querySelector('.filter-label').textContent = open ? 'Ẩn lọc' : 'Bộ lọc';
    btnToggle.querySelector('.filter-icon').textContent  = open ? 'close'  : 'tune';
}
 
btnToggle.addEventListener('click', function () {
    setPanel(!filterPanel.classList.contains('open'));
});
 
// Tự mở lại nếu đang có filter active
const hasFilter = {{ (request('sort') || request('price_min') || request('price_max') || request('brand_id') || request('flight_min') || request('camera_min') || request('weight_max')) ? 'true' : 'false' }};
if (hasFilter) setPanel(true);
 
function applyFilters() {
    document.getElementById('filter-form').submit();
}
</script>
@endpush
@endsection