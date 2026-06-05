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

    <nav class="vanguard-breadcrumb">

        <span class="hud-tag-sm">Tactical Fleet</span>

        <span class="material-symbols-outlined">chevron_right</span>

        <span>{{ $product->category->name ?? 'UAV Systems' }}</span>

        <span class="material-symbols-outlined">chevron_right</span>

        <span class="active">{{ $product->sku ?? 'NO-SKU' }}</span>

    </nav>

    <div class="product-layout-grid">

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
                    
                    <div class="swiper-button-next vg-swiper-btn"></div>
                    <div class="swiper-button-prev vg-swiper-btn"></div>
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

            <div class="comments-section glass-panel">

            <div class="product-description-box">

    <div class="description-header">
        <span class="material-symbols-outlined">description</span>
        <span>Mô tả sản phẩm</span>
    </div>

    <div class="description-content collapsed" id="productDescription">
        {{ $product->description }}
    </div>

    <button type="button"
            class="description-toggle"
            onclick="toggleDescription()">
        <span id="toggleText">Xem thêm</span>
        <span id="toggleIcon">▼</span>
    </button>

</div>


                <h3 class="comment-title">Bình luận</h3>

                @auth
                    <form action="{{ route('user.comment.store', $product->id) }}"
      method="POST"
      class="comment-form">

    @csrf

    {{-- ===== RATING ===== --}}
    <div class="rating-box">

        <label class="rating-label">
            Đánh giá sản phẩm
        </label>

        <div class="star-rating">

            <input type="radio"
                   id="star5"
                   name="rating"
                   value="5"
                   checked>

            <label for="star5">★</label>

            <input type="radio"
                   id="star4"
                   name="rating"
                   value="4">

            <label for="star4">★</label>

            <input type="radio"
                   id="star3"
                   name="rating"
                   value="3">

            <label for="star3">★</label>

            <input type="radio"
                   id="star2"
                   name="rating"
                   value="2">

            <label for="star2">★</label>

            <input type="radio"
                   id="star1"
                   name="rating"
                   value="1">

            <label for="star1">★</label>

        </div>

    </div>

    {{-- ===== COMMENT ===== --}}
    <textarea name="comment"
              class="comment-textarea"
              placeholder="Viết bình luận..."
              required></textarea>

    <button type="submit"
            class="submit-comment-btn">

        Gửi

    </button>

</form>
                @else
                    <p class="comment-login-text">Đăng nhập để bình luận</p>
                @endauth

                <div class="comment-list">

                    @forelse($reviews as $cmt)

<div class="comment-item">

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

                {{-- HIỂN THỊ RATING --}}
                <div class="comment-rating"
                     id="rating-display-{{ $cmt->id }}">

                    @for($i = 1; $i <= 5; $i++)

                        @if($i <= $cmt->rating)
                            <span style="color:gold;">★</span>
                        @else
                            <span style="color:#777;">★</span>
                        @endif

                    @endfor

                    ({{ $cmt->rating }}/5)

                </div>

            </div>

        </div>

    </div>

    <div class="comment-content"
         id="content-{{ $cmt->id }}">

        {{ $cmt->comment }}

    </div>

    <textarea class="edit-comment-textarea"
              id="textarea-{{ $cmt->id }}"
              style="display:none;">{{ $cmt->comment }}</textarea>

    <div class="edit-rating-box"
         id="edit-rating-{{ $cmt->id }}"
         style="display:none; margin-top:10px;">

        <div class="star-rating">

            @for($i = 5; $i >= 1; $i--)

                <input type="radio"
                       id="edit-star-{{ $cmt->id }}-{{ $i }}"
                       name="edit-rating-{{ $cmt->id }}"
                       value="{{ $i }}"
                       {{ $cmt->rating == $i ? 'checked' : '' }}>

                <label for="edit-star-{{ $cmt->id }}-{{ $i }}">
                    ★
                </label>

            @endfor

        </div>

    </div>

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

                    <button type="submit"
                            class="btn-comment-delete">
                        Xóa
                    </button>

                </form>

            </div>

        @endif
    @endauth

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

<p class="empty-comment">
    Chưa có bình luận
</p>

@endforelse

                </div>

            </div>

        </div>

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

                <div class="product-spec-grid">
                    <div class="spec-item">
                        <span class="material-symbols-outlined spec-icon">timer</span>
                        <div class="spec-data">
                            <span class="spec-label">Thời gian bay</span>
                            <span class="spec-value">{{ $product->flight_time ?? 'N/A' }} <small>phút</small></span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <span class="material-symbols-outlined spec-icon">altitude</span>
                        <div class="spec-data">
                            <span class="spec-label">Độ cao Max</span>
                            <span class="spec-value">{{ $product->max_altitude ?? 'N/A' }} <small>m</small></span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <span class="material-symbols-outlined spec-icon">photo_camera</span>
                        <div class="spec-data">
                            <span class="spec-label">Camera</span>
                            <span class="spec-value">{{ $product->camera_mp ?? 'N/A' }} <small>MP</small></span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <span class="material-symbols-outlined spec-icon">wifi_tethering</span>
                        <div class="spec-data">
                            <span class="spec-label">Tần số</span>
                            <span class="spec-value">{{ $product->frequency ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <span class="material-symbols-outlined spec-icon">weight</span>
                        <div class="spec-data">
                            <span class="spec-label">Trọng lượng</span>
                            <span class="spec-value">{{ $product->weight ?? 'N/A' }} <small>kg</small></span>
                        </div>
                    </div>

                    <div class="spec-item">
    <span class="material-symbols-outlined spec-icon">branding_watermark</span>
    <div class="spec-data">
        <span class="spec-label">Thương hiệu</span>
        <span class="spec-value">
            {{ $product->brand->name ?? 'N/A' }}
        </span>
    </div>

</div>


    

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

document.addEventListener('DOMContentLoaded', function () {
    var swiperThumb = new Swiper(".thumb-swiper", {
        spaceBetween: 12,
        slidesPerView: 5,
        freeMode: true,
        watchSlidesProgress: true,
        breakpoints: {
            320: { slidesPerView: 4 },
            768: { slidesPerView: 5 }
        }
    });

    var swiperMain = new Swiper(".main-swiper", {
        spaceBetween: 10,
        effect: "fade",
        fadeEffect: { crossFade: true },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        thumbs: {
            swiper: swiperThumb,
        },
    });
});

function editComment(id)
{
    document.getElementById('content-' + id).style.display = 'none';

    document.getElementById('textarea-' + id).style.display = 'block';

    document.getElementById('edit-rating-' + id).style.display = 'block';

    document.getElementById('save-btn-' + id).style.display = 'inline-block';

    document.getElementById('cancel-btn-' + id).style.display = 'inline-block';
}

function cancelEdit(id)
{
    document.getElementById('content-' + id).style.display = 'block';

    document.getElementById('textarea-' + id).style.display = 'none';

    document.getElementById('edit-rating-' + id).style.display = 'none';

    document.getElementById('save-btn-' + id).style.display = 'none';

    document.getElementById('cancel-btn-' + id).style.display = 'none';
}

function saveComment(id)
{
    const textarea = document.getElementById('textarea-' + id);

    const content = document.getElementById('content-' + id);

    const selectedRating = document.querySelector(
        'input[name="edit-rating-' + id + '"]:checked'
    );

    let newValue = textarea.value.trim();

    if (!newValue) {

        alert("Bình luận không được để trống");

        return;
    }

    let rating = selectedRating
        ? selectedRating.value
        : 5;

    fetch(
        "{{ route('user.comment.update', ':id') }}"
            .replace(':id', id),
        {
            method: "POST",

            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },

            body: JSON.stringify({
                comment: newValue,
                rating: rating
            })
        }
    )
    .then(res => res.json())
    .then(data => {

        if (data.success)
        {
            content.innerText = newValue;

            let html = '';

            for(let i = 1; i <= 5; i++)
            {
                if(i <= rating){
                    html += '<span style="color:gold;">★</span>';
                }else{
                    html += '<span style="color:#777;">★</span>';
                }
            }

            html += ' (' + rating + '/5)';

            document.getElementById(
                'rating-display-' + id
            ).innerHTML = html;

            cancelEdit(id);
        }
        else
        {
            alert(
                data.message ??
                "Không cập nhật được bình luận"
            );
        }

    })
    .catch(err => {

        console.log(err);

        alert("Lỗi server khi cập nhật");

    });
}

function buyNowAction()
{
    const form = document.getElementById('purchase-form');

    form.action = "{{ route('user.checkout.buyNow') }}";

    form.submit();
}

function toggleDescription()
{
    const desc = document.getElementById('productDescription');
    const text = document.getElementById('toggleText');
    const icon = document.getElementById('toggleIcon');

    desc.classList.toggle('collapsed');

    if(desc.classList.contains('collapsed'))
    {
        text.innerText = 'Xem thêm';
        icon.innerText = '▼';
    }
    else
    {
        text.innerText = 'Thu gọn';
        icon.innerText = '▲';
    }
}

</script>

@endpush