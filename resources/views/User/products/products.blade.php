@extends('User.layouts.app')

@section('title', 'Danh sách sản phẩm | Vanguard Command')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')

<div class="product-container-vanguard">

    <aside class="filter-sidebar">
        {{-- SEARCH ROW --}}
        <div class="search-row">
            <label class="search-title">
                <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">radar</span>
                TÌM KIẾM
            </label>

            <form action="{{ route('user.products') }}" method="GET" class="search-form" id="filterForm">
                {{-- Giữ lại filter khi search --}}
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="sort"     value="{{ request('sort') }}">
                <input type="hidden" name="price_max" id="priceMaxHidden" value="{{ request('price_max', 100000000) }}">

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
        <div class="filter-panel" id="filterPanel">
            <div class="filter-panel-inner">

                {{-- DANH MỤC --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        <span class="material-symbols-outlined">category</span>
                        DANH MỤC
                    </div>
                    <select class="filter-select" id="filterCategory" onchange="applyFilters()">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- GIÁ (range slider) --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        <span class="material-symbols-outlined">payments</span>
                        GIÁ TỐI ĐA
                    </div>
                    <div class="price-range-wrap">
                        <div class="price-range-label">
                            <span>0đ</span>
                            <strong id="priceDisplay">{{ number_format(request('price_max', 100000000), 0, ',', '.') }}đ</strong>
                            <span>100tr</span>
                        </div>
                        <input type="range"
                               class="price-slider"
                               id="priceSlider"
                               min="0"
                               max="100000000"
                               step="1000000"
                               value="{{ request('price_max', 100000000) }}">
                    </div>
                </div>

                {{-- SẮP XẾP --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        <span class="material-symbols-outlined">sort</span>
                        SẮP XẾP
                    </div>
                    <select class="filter-select" id="filterSort" onchange="applyFilters()">
                        <option value=""         {{ request('sort') == ''           ? 'selected' : '' }}>Mặc định</option>
                        <option value="price_asc"  {{ request('sort') == 'price_asc'  ? 'selected' : '' }}>Giá tăng dần</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                        <option value="newest"     {{ request('sort') == 'newest'     ? 'selected' : '' }}>Mới nhất</option>
                    </select>
                </div>

                {{-- NÚT ÁP DỤNG --}}
                <button class="btn-apply-filter" onclick="applyFilters()">
                    ÁP DỤNG
                </button>

                {{-- NÚT XÓA LỌC (chỉ hiện khi đang có filter) --}}
                @if(request('category') || request('sort') || request('price_max') || request('search'))
                <a href="{{ route('user.products') }}" class="btn-clear-filter">
                    <span class="material-symbols-outlined">close</span>
                    XÓA LỌC
                </a>
                @endif

            </div>
        </div>
    </aside>

    {{-- GRID SẢN PHẨM --}}
    <section class="product-grid">
        @isset($products)
            @forelse($products as $product)
                <div class="product-spec-card">
                    <div class="card-badge">VANGUARD</div>
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
                            <span class="price">{{ number_format($product->sale_price, 0, ',', '.') }}đ</span>
                            <div class="card-footer-actions">
                                <form action="{{ route('user.checkout.buyNow') }}" method="POST" style="flex:1;margin:0;display:flex;">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity"   value="1">
                                    <button type="submit" class="btn-buy-now">MUA NGAY</button>
                                </form>
                                <form action="{{ route('user.cart.add') }}" method="POST" style="margin:0;">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity"   value="1">
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
                    <span class="material-symbols-outlined" style="font-size:40px;margin-bottom:10px;display:block;">warning</span>
                    >> SYSTEM_ALERT: KHÔNG TÌM THẤY UAV NÀO TRONG KHO DỮ LIỆU.
                </div>
            @endforelse
        @endisset
    </section>

    {{-- PHÂN TRANG --}}
    {{-- CODE MỚI: Đã ép thu gọn và gọi đúng file bạn vừa dán đè --}}
<div class="pagination-matrix">
    @if(method_exists($products, 'links'))
        {{ $products->onEachSide(0)->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
    @endif
</div>

</div>

@push('scripts')
{{-- <script src="{{ asset('js/products-mobile.js') }}"></script> --}}
<script>
/* ---- Filter toggle ---- */
const btnToggle   = document.getElementById('btnFilterToggle');
const filterPanel = document.getElementById('filterPanel');

function setPanel(open) {
    filterPanel.classList.toggle('open', open);
    btnToggle.classList.toggle('active', open);
    btnToggle.querySelector('.filter-label').textContent = open ? 'Ẩn lọc' : 'Bộ lọc';
    btnToggle.querySelector('.filter-icon').textContent  = open ? 'close'   : 'tune';
}

btnToggle.addEventListener('click', function () {
    setPanel(!filterPanel.classList.contains('open'));
});

/* Tự mở lại nếu đang có filter active */
const hasFilter = {{ (request('category') || request('sort') || request('price_max')) ? 'true' : 'false' }};
if (hasFilter) setPanel(true);

/* ---- Range slider ---- */
const slider      = document.getElementById('priceSlider');
const display     = document.getElementById('priceDisplay');
const hiddenInput = document.getElementById('priceMaxHidden');

function formatVND(val) {
    if (val >= 1000000) return (val / 1000000).toFixed(0) + ' triệu';
    return new Intl.NumberFormat('vi-VN').format(val) + 'đ';
}

function updateSlider(val) {
    const pct = (val / slider.max * 100).toFixed(1);
    slider.style.setProperty('--pct', pct + '%');
    display.textContent = formatVND(Number(val));
    hiddenInput.value   = val;
}

slider.addEventListener('input', () => updateSlider(slider.value));
updateSlider(slider.value); // khởi tạo

/* ---- Áp dụng filter (submit form) ---- */
function applyFilters() {
    const form = document.getElementById('filterForm');
    form.querySelector('[name="category"]').value  = document.getElementById('filterCategory').value;
    form.querySelector('[name="sort"]').value      = document.getElementById('filterSort').value;
    hiddenInput.value = slider.value;
    form.submit();
}
</script>
@endpush

@endsection