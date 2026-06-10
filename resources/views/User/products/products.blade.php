@extends('User.layouts.app')

@section('title', 'Danh sách sản phẩm | Vanguard Command')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/products.css') }}">
@endpush

@section('content')

<div class="product-container-vanguard">

{{-- SEARCH ROW --}}
    <div class="search-row">
        <!-- <label class="search-title">
            TÌM KIẾM
        </label> -->

        <form action="{{ route('user.products') }}" method="GET" class="search-form" id="filterForm">
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
            <button type="submit" class="btn-scan">Tìm Kiếm
            <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">radar</span>

            </button>
        </form>

        <button class="btn-filter-toggle" id="btnFilterToggle" type="button">
            <span class="material-symbols-outlined filter-icon">tune</span>
            <span class="filter-label">Bộ lọc</span>
        </button>
    </div>

    {{-- FILTER PANEL --}}
    <div class="filter-panel open" id="filterPanel">

        <div class="filter-grid">

            <div class="filter-group">
                <div class="filter-group-title">
                    <i class="fas fa-sort-amount-down"></i> SẮP XẾP
                </div>
                <select class="filter-select" name="sort" form="filterForm">
                    <option value="default">Mặc định</option>
                    <option value="price-asc"  {{ request('sort') == 'price-asc'  ? 'selected' : '' }}>Giá tăng dần</option>
                    <option value="price-desc" {{ request('sort') == 'price-desc' ? 'selected' : '' }}>Giá giảm dần</option>
                </select>
            </div>

            <div class="filter-group">
                <div class="filter-group-title">
                    <i class="fas fa-award"></i> THƯƠNG HIỆU
                </div>
                <select class="filter-select" name="brand_id" form="filterForm">
                    <option value="">-- Tất cả thương hiệu --</option>
                    <option value="dji"   {{ request('brand_id') == 'dji'   ? 'selected' : '' }}>DJI</option>
                    <option value="autel" {{ request('brand_id') == 'autel' ? 'selected' : '' }}>Autel Robotics</option>
                </select>
            </div>

            <div class="filter-group">
                <div class="filter-group-title">
                    <i class="fas fa-money-bill-wave"></i> KHOẢNG GIÁ ($)
                </div>
                <div class="filter-range-row">
                    <input type="number" class="filter-input" name="price_min" form="filterForm"
                           placeholder="Từ" value="{{ request('price_min') }}">
                    <span style="color: rgba(0,255,255,0.5);">—</span>
                    <input type="number" class="filter-input" name="price_max" form="filterForm"
                           placeholder="Đến" value="{{ request('price_max') }}">
                </div>
            </div>

            <div class="filter-group">
                <div class="filter-group-title">
                    <i class="fas fa-plane"></i> THỜI GIAN BAY TỐI THIỂU
                </div>
                <div class="filter-spec-row">
                    <input type="number" class="filter-input" name="flight_min" form="filterForm"
                           placeholder="Phút" value="{{ request('flight_min') }}">
                    <!-- <span class="unit-label">phút</span> -->
                </div>
            </div>

            <div class="filter-group">
                <div class="filter-group-title">
                    <i class="fas fa-camera"></i> CAMERA TỐI THIỂU
                </div>
                <div class="filter-spec-row">
                    <input type="number" class="filter-input" name="camera_min" form="filterForm"
                           placeholder="MP" value="{{ request('camera_min') }}">
                    <!-- <span class="unit-label">MP</span> -->
                </div>
            </div>

        <div class="filter-group">
            <div class="filter-group-title">
                    <i class="fas fa-hourglass-half"></i> TRỌNG LƯỢNG TỐI ĐA
            </div>
            <div class="filter-spec-row">
                    <input type="number" class="filter-input" name="weight_max" form="filterForm"
                           placeholder="kg" value="{{ request('weight_max') }}">
                    <!-- <span class="unit-label">kg</span> -->
            </div>
        </div>

        </div> {{-- end filter-grid --}}

                <div class="filter-actions">
                    <button type="submit"
                            form="filterForm"
                            class="btn-apply-filter">
                        ÁP DỤNG
                    </button>

                    <a href="{{ route('user.products') }}"
                    class="btn-clear-filter">
                        XÓA LỌC
                    </a>
                </div>
    </div> {{-- end .product-container-vanguard --}}



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
<!-- <script src="{{ asset('js/products-mobile.js') }}"></script> -->
<script>
/* ---- Filter toggle ---- */
const btnToggle   = document.getElementById('btnFilterToggle');
const filterPanel = document.getElementById('filterPanel');

function setPanel(open) {
    filterPanel.classList.toggle('open', open);
    btnToggle.classList.toggle('active', open);
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