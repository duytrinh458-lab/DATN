@extends('User.layouts.app')

@section('title', $product->name . ' | Vanguard UAV')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('Css/User/product_detail.css') }}">
@endpush

@section('content')
<main class="vanguard-detail-container">
    <nav class="vanguard-breadcrumb">
        <span class="hud-tag-sm">Tactical Fleet</span>
        <span class="material-symbols-outlined">chevron_right</span>
        <span>{{ $product->category->name ?? 'UAV Systems' }}</span>
        <span class="material-symbols-outlined">chevron_right</span>
        <span class="active">{{ $product->sku }}</span>
    </nav>

    <div class="product-layout-grid">
        <!-- CỘT TRÁI: Gallery Hệ thống (7 Cột) -->
        <div class="gallery-col">
            <div class="main-frame glass-panel">
                <div class="hud-corner top-left"></div>
                <div class="hud-corner top-right"></div>
                <div class="hud-corner bottom-left"></div>
                <div class="hud-corner bottom-right"></div>

                <div class="swiper main-swiper">
                    <div class="swiper-wrapper">
                        @foreach($product->images as $img)
                            <div class="swiper-slide">
                                <img src="{{ asset($img->image_url) }}" alt="Optical Scan">
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="propulsion-badge">Propulsion Ready</div>
            </div>

            <div class="swiper thumb-swiper">
                <div class="swiper-wrapper">
                    @foreach($product->images as $img)
                        <div class="swiper-slide thumb-item">
                            <img src="{{ asset($img->image_url) }}" alt="Telemetry view">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: Bảng điều khiển (5 Cột) -->
        <div class="control-panel-col">
            <div class="info-card glass-panel">
                <div class="cart-status-hud">
                    <span class="material-symbols-outlined">base_station</span>
                    <span class="font-mono text-[10px]">
                        <!-- Xử lý lỗi đếm số lượng giỏ hàng nếu chưa đăng nhập -->
                        CART_STATUS: {{ auth()->check() && \App\Models\Cart::where('user_id', auth()->id())->exists() ? \Illuminate\Support\Facades\DB::table('cart_items')->where('cart_id', \App\Models\Cart::where('user_id', auth()->id())->value('id'))->sum('quantity') : 0 }} UNITS
                    </span>
                </div>

                <header class="uav-header">
                    <span class="series-label">Advanced Aerial Unit</span>
                    <h1 class="uav-title text-glow">{{ $product->name }}</h1>
                </header>

                <div class="price-display">
                    <span class="current-price">{{ number_format($product->sale_price, 0, ',', '.') }}₫</span>
                    @if($product->original_price > $product->sale_price)
                        <span class="old-price">{{ number_format($product->original_price, 0, ',', '.') }}₫</span>
                    @endif
                </div>

                <div class="telemetry-grid">
                    <div class="gauge-box">
                        <div class="ring"><span class="val">{{ $product->flight_time ?? '45' }}</span></div>
                        <span class="lab">Thời gian bay</span>
                    </div>
                    <div class="gauge-box">
                        <div class="ring"><span class="val">{{ $product->max_altitude ?? '12' }}</span></div>
                        <span class="lab">Trần bay (km)</span>
                    </div>
                </div>

                <!-- Đã xóa cái input cờ redirect_to_checkout đi vì không cần thiết nữa -->
                <form id="purchase-form" action="{{ route('user.cart.add') }}" method="POST" class="purchase-interface">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    <div class="quantity-selector">
                        <label>ĐƠN VỊ CẤU HÌNH:</label>
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}">
                    </div>

                    <div class="button-stack">
                        <button type="button" onclick="buyNowAction()" class="btn-buy-now">
                            <span class="material-symbols-outlined">bolt</span>
                            MUA NGAY BÂY GIỜ
                        </button>
                        
                        <button type="submit" class="btn-add-to-cart">
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                            THÊM VÀO GIỎ HÀNG
                        </button>
                    </div>
                </form>

                <footer class="security-footer">
                    <span class="material-symbols-outlined">verified_user</span>
                    <span class="text-[10px] tracking-widest uppercase">Bảo mật AES-256 & Bảo hành chính hãng</span>
                </footer>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var thumbs = new Swiper(".thumb-swiper", { spaceBetween: 12, slidesPerView: 4, watchSlidesProgress: true });
        new Swiper(".main-swiper", { spaceBetween: 10, thumbs: { swiper: thumbs }, grabCursor: true });

        // ĐÃ SỬA HÀM NÀY: Tự động đổi link Form sang Mua ngay trước khi gửi
        function buyNowAction() {
            var form = document.getElementById('purchase-form');
            form.action = "{{ route('user.checkout.buyNow') }}"; 
            form.submit();
        }
    </script>
@endpush