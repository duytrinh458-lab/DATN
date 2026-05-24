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

    // ✔ FIX: load reviews + replies + user (KHÔNG query logic nặng trong blade)
    $reviews = \App\Models\Review::with(['user', 'replies.user'])
        ->where('product_id', $product->id)
        ->whereNull('parent_id')
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
                                <img src="{{ asset($img->image_url) }}" alt="{{ $product->name }}">
                            </div>
                        @endforeach

                    </div>
                </div>

            </div>

            <div class="swiper thumb-swiper">
                <div class="swiper-wrapper">

                    @foreach($product->images as $img)
                        <div class="swiper-slide thumb-item">
                            <img src="{{ asset($img->image_url) }}" alt="{{ $product->name }}">
                        </div>
                    @endforeach

                </div>
            </div>

            <!-- COMMENTS -->
            <div class="comments-section glass-panel">

                <h3 class="comment-title">Bình luận</h3>

                @auth
                    <form action="{{ route('user.comment.store', $product->id) }}"
                          method="POST"
                          class="comment-form">

                        @csrf

                        <textarea name="comment"
                                  class="comment-textarea"
                                  placeholder="Viết bình luận..."
                                  required></textarea>

                        <button type="submit" class="submit-comment-btn">
                            Gửi
                        </button>

                    </form>
                @else
                    <p class="comment-login-text">Đăng nhập để bình luận</p>
                @endauth

                <div class="comment-list">

                    @forelse($reviews as $cmt)

                        <div class="comment-item">

                            <!-- USER -->
                            <div class="comment-header">

                                <div class="comment-user">

                                    <img
                                        src="{{ optional($cmt->user)->avatar
                                            ? asset($cmt->user->avatar)
                                            : 'https://ui-avatars.com/api/?name=' . urlencode(optional($cmt->user)->full_name ?? 'User') }}"
                                        class="comment-avatar"
                                        alt="avatar">

                                    <div>

                                        <div class="comment-username">
                                            {{ optional($cmt->user)->full_name ?? 'User' }}
                                        </div>

                                        <div class="comment-rating">
                                            ⭐ {{ $cmt->rating ?? 5 }}/5
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- CONTENT -->
                            <div class="comment-content" id="content-{{ $cmt->id }}">
                                {{ $cmt->comment }}
                            </div>

                            <!-- EDIT -->
                            <textarea class="edit-comment-textarea"
                                      id="textarea-{{ $cmt->id }}"
                                      style="display:none;">{{ $cmt->comment }}</textarea>

                            @auth
                                @if(auth()->id() == $cmt->user_id)

                                    <div class="comment-actions">

                                        <button type="button"
                                                class="btn-comment-edit"
                                                onclick="editComment({{ $cmt->id }})">
                                            Sửa
                                        </button>

                                        <button type="button"
                                                class="btn-comment-save"
                                                id="save-btn-{{ $cmt->id }}"
                                                style="display:none;"
                                                onclick="saveComment({{ $cmt->id }})">
                                            Lưu
                                        </button>

                                        <button type="button"
        id="cancel-btn-{{ $cmt->id }}"
        class="btn-comment-cancel"
        style="display:none;"
        onclick="cancelEdit({{ $cmt->id }})">
    Hủy
</button>

                                        <form action="{{ route('user.comment.delete', $cmt->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Xóa bình luận?')"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn-comment-delete">
                                                Xóa
                                            </button>
                                        </form>

                                    </div>

                                @endif
                            @endauth

                            <!-- REPLIES -->
                            @if($cmt->replies && $cmt->replies->count())

                                <div class="reply-list">

                                    @foreach($cmt->replies as $reply)

                                        <div class="reply-item">

                                            <strong>
                                                {{ optional($reply->user)->full_name ?? 'User' }}
                                            </strong>

                                            <p>{{ $reply->comment }}</p>

                                        </div>

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    @empty
                        <p class="empty-comment">Chưa có bình luận</p>
                    @endforelse

                </div>

            </div>

        </div>

        <!-- RIGHT -->
        <div class="control-panel-col">

            <div class="info-card glass-panel">

                <div class="cart-count">
                    🛒 Giỏ hàng: {{ $cartCount }} sản phẩm
                </div>

                <h1 class="product-title">{{ $product->name }}</h1>

                <div class="product-price">
                    {{ number_format($product->sale_price, 0, ',', '.') }}₫
                </div>

                <div class="stock-box">
                    @if($product->stock > 0)
                        <span class="stock-in">✔ Còn hàng ({{ $product->stock }})</span>
                    @else
                        <span class="stock-out">✖ Hết hàng</span>
                    @endif
                </div>

                <div class="product-spec">
                    <p>Thời gian bay: {{ $product->flight_time ?? 'N/A' }} phút</p>
                    <p>Độ cao tối đa: {{ $product->max_altitude ?? 'N/A' }} m</p>
                    <p>Camera: {{ $product->camera_mp ?? 'N/A' }} MP</p>
                    <p>Tần số: {{ $product->frequency ?? 'N/A' }}</p>
                    <p>Trọng lượng: {{ $product->weight ?? 'N/A' }} kg</p>
                </div>

                <form id="purchase-form"
                      action="{{ route('user.cart.add') }}"
                      method="POST">

                    @csrf

                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="quantity-box">

                        <label class="quantity-label">Số lượng mua</label>

                        <input type="number"
                               name="quantity"
                               value="1"
                               min="1"
                               max="{{ $product->stock > 0 ? $product->stock : 1 }}">
                    </div>

                    <div class="button-stack">

                        <button type="button"
                                onclick="buyNowAction()"
                                class="btn-buy-now">
                            MUA NGAY
                        </button>

                        <button type="submit" class="btn-add-to-cart">
                            THÊM GIỎ
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</main>

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>

function editComment(id)
{
    document.getElementById('content-' + id).style.display = 'none';
    document.getElementById('textarea-' + id).style.display = 'block';

    document.getElementById('save-btn-' + id).style.display = 'inline-block';
    document.getElementById('cancel-btn-' + id).style.display = 'inline-block';
}

function cancelEdit(id)
{
    document.getElementById('content-' + id).style.display = 'block';
    document.getElementById('textarea-' + id).style.display = 'none';

    document.getElementById('save-btn-' + id).style.display = 'none';
    document.getElementById('cancel-btn-' + id).style.display = 'none';
}

function saveComment(id)
{
    const textarea = document.getElementById('textarea-' + id);
    const content  = document.getElementById('content-' + id);

    let newValue = textarea.value.trim();

    if (!newValue) {
        alert("Bình luận không được để trống");
        return;
    }

    fetch("{{ route('user.comment.update', ':id') }}".replace(':id', id), {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            comment: newValue
        })
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            content.innerText = newValue;
            cancelEdit(id);
        } else {
            alert(data.message ?? "Không cập nhật được bình luận");
        }

    })
    .catch(err => {
        console.log(err);
        alert("Lỗi server khi cập nhật");
    });
}

</script>

@endpush