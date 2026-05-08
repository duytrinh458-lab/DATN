@extends('User.layouts.app')

@section('title', $product->name . ' | Vanguard UAV')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link rel="stylesheet" href="{{ asset('Css/User/product_detail.css') }}">
@endpush

@section('content')

@php
    $cart = auth()->check()
        ? \App\Models\Cart::where('user_id', auth()->id())->first()
        : null;

    $cartCount = $cart
        ? \App\Models\CartItem::where('cart_id', $cart->id)->sum('quantity')
        : 0;

    $reviews = \App\Models\Review::with('user')
        ->where('product_id', $product->id)
        ->orderBy('id', 'desc')
        ->get();
@endphp

<main class="vanguard-detail-container">

    <!-- BREADCRUMB -->
    <nav class="vanguard-breadcrumb">
        <span class="hud-tag-sm">Tactical Fleet</span>
        <span class="material-symbols-outlined">chevron_right</span>
        <span>{{ $product->category->name ?? 'UAV Systems' }}</span>
        <span class="material-symbols-outlined">chevron_right</span>
        <span class="active">{{ $product->sku ?? 'NO-SKU' }}</span>
    </nav>

    <div class="product-layout-grid">

        <!-- LEFT -->
        <div class="gallery-col">

            <div class="main-frame glass-panel">
                <div class="swiper main-swiper">
                    <div class="swiper-wrapper">
                        @foreach($product->images as $img)
                            <div class="swiper-slide">
                                <img src="{{ asset($img->image_url) }}" alt="">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- THUMBNAIL (FIX CLASS) -->
            <div class="swiper thumb-swiper">
                <div class="swiper-wrapper">
                    @foreach($product->images as $img)
                        <div class="swiper-slide thumb-item">
                            <img src="{{ asset($img->image_url) }}" alt="">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- COMMENTS -->
            <div class="comments-panel glass-panel">

                <h3>Bình luận</h3>

                @auth
                <form action="{{ url('/api/set_comments_product/' . $product->id) }}" method="POST">
                    @csrf
                    <textarea name="comment" style="width:100%;min-height:70px"></textarea>
                    <button type="submit">Gửi</button>
                </form>
                @else
                    <p>Đăng nhập để bình luận</p>
                @endauth

                <div class="comment-list">
                    @forelse($reviews as $cmt)
                        <div>
                            <b>{{ $cmt->user->full_name ?? 'User' }}</b>
                            <div>{{ $cmt->comment }}</div>
                            <small>⭐ {{ $cmt->rating ?? 0 }}/5</small>
                        </div>
                    @empty
                        <p>Chưa có bình luận</p>
                    @endforelse
                </div>

            </div>

        </div>

        <!-- RIGHT -->
<div class="control-panel-col">

    <div class="info-card glass-panel">

        <div>
            🛒 Giỏ hàng: {{ $cartCount }} sản phẩm
        </div>

        <h1>{{ $product->name }}</h1>

        <div>
            {{ number_format($product->sale_price, 0, ',', '.') }}₫
        </div>

        <!-- TỒN KHO -->
        <div>
            @if($product->stock > 0)
                <span style="color:#22c55e;font-weight:600">
                    ✔ Còn hàng ({{ $product->stock }})
                </span>
            @else
                <span style="color:#ef4444;font-weight:600">
                    ✖ Hết hàng
                </span>
            @endif
        </div>

        <!-- THÔNG TIN SẢN PHẨM (VIỆT HOÁ) -->
        <div class="product-spec">
            <p>Thời gian bay: {{ $product->flight_time ?? 'N/A' }} phút</p>
            <p>Độ cao tối đa: {{ $product->max_altitude ?? 'N/A' }} m</p>
            <p>Camera: {{ $product->camera_mp ?? 'N/A' }} MP</p>
            <p>Tần số: {{ $product->frequency ?? 'N/A' }}</p>
            <p>Trọng lượng: {{ $product->weight ?? 'N/A' }} kg</p>
        </div>

        <!-- FORM MUA HÀNG -->
        <form id="purchase-form" action="{{ route('user.cart.add') }}" method="POST">
            @csrf

            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <input type="number"
                   name="quantity"
                   value="1"
                   min="1"
                   max="{{ $product->stock > 0 ? $product->stock : 1 }}">

            <!-- BUTTON STACK -->
            <!-- <div class="action-row"> -->

            <div class="button-stack">

                <button type="button"
                        onclick="buyNowAction()"
                        class="btn-buy-now">
                    MUA NGAY
                </button>


                    <button type="submit" class="btn-add-to-cart">
                        THÊM GIỎ
                    </button>

                    <!-- ❤️ YÊU THÍCH -->
                    <button type="button" class="btn-favorite">
                        YÊU THÍCH
                    </button>

                <!-- </div> -->

            </div>

        </form>

    </div>
</div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
var thumbs = new Swiper(".thumb-swiper", {
    spaceBetween: 10,
    slidesPerView: 4,
    watchSlidesProgress: true
});

new Swiper(".main-swiper", {
    spaceBetween: 10,
    thumbs: {
        swiper: thumbs
    }
});

function buyNowAction() {
    let form = document.getElementById('purchase-form');
    form.action = "{{ route('user.checkout.buyNow') }}";
    form.submit();
}
</script>
@endpush