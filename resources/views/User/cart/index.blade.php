@extends('User.layouts.app') 

@section('title', 'Giỏ Hàng Chiến Thuật - Vanguard UAV')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/cart.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endpush

@section('content')
<div class="vanguard-cart-page">
    <div class="cart-header">
        <h2>Giỏ hàng chiến lược</h2>
        <div style="color: var(--outline); font-size: 10px; text-transform: uppercase; letter-spacing: 4px;">Tactical Resource Management</div>
    </div>

    <div class="cart-flex-wrapper">
        <div class="cart-items-list">
            @if(isset($cartItems) && $cartItems->count() > 0)
                @foreach($cartItems as $item)
                    <div class="cart-item-module">
                        <div class="item-img-box">
                            <img src="{{ asset($item->product->images->first()->image_url ?? 'default.jpg') }}" alt="{{ $item->product->name }}">
                        </div>
                        
                        <div class="item-info">
                            <div class="item-sku">UAV-{{ $item->product->sku }}</div>
                            <div class="item-name">{{ $item->product->name }}</div>
                        </div>

                        <div class="quantity-hud">
                            <form action="{{ route('user.cart.update', $item->id) }}" method="POST" style="display:flex; align-items:center; gap: 5px;">
                                @csrf
                                @method('PUT')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}" onchange="this.form.submit()" style="width: 60px; text-align: center; background: transparent; border: 1px solid var(--outline); color: #fff; padding: 5px;">
                            </form>
                        </div>

                        <div style="text-align: right; min-width: 120px;">
                            <div style="font-size: 10px; color: var(--outline); text-transform: uppercase;">Giá đơn vị</div>
                            <div style="font-weight: 900; color: var(--primary);">{{ number_format($item->unit_price, 0, ',', '.') }}₫</div>
                        </div>

                        <form id="delete-form-{{ $item->id }}" action="{{ route('user.cart.destroy', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="openDeleteModal({{ $item->id }})" style="background:transparent; border:none; color:var(--outline); cursor:pointer;">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            @else
                <div style="padding: 100px; text-align: center; background: var(--surface-container-low); border-radius: 40px;">
                    <p style="color: var(--outline); text-transform: uppercase; letter-spacing: 2px;">Kho hàng của đặc vụ đang trống</p>
                    <a href="{{ route('user.products') }}" style="color: var(--primary); text-decoration: underline; margin-top: 20px; display: inline-block;">Quay lại kho vũ khí</a>
                </div>
            @endif
        </div>

        <aside class="cart-summary-vanguard">
            <div class="summary-card-inner">
                <div class="summary-title">Ngân sách dự kiến</div>
                
                <div class="summary-row">
                    <span>TẠM TÍNH</span>
                    <span>{{ isset($total) ? number_format($total, 0, ',', '.') : '0' }}đ</span>
                </div>
                
                <div class="summary-row">
                    <span>VẬN CHUYỂN</span>
                    <span>FREE</span>
                </div>

                <div class="total-row">
                    <div style="font-size: 10px; font-weight: 900; opacity: 0.6;">TỔNG CỘNG</div>
                    <div class="total-price-display">{{ isset($total) ? number_format($total, 0, ',', '.') : '0' }}₫</div>
                </div>

                <button type="button" class="btn-checkout-vanguard">Tiến hành thanh toán</button>
                
                <a href="{{ route('user.products') }}" style="display: block; text-align: center; margin-top: 20px; font-size: 10px; font-weight: 900; text-decoration: none; color: inherit; opacity: 0.5;">
                    ← TIẾP TỤC ĐIỀU ĐỘNG THIẾT BỊ
                </a>
            </div>
        </aside>
    </div>
</div>

<!-- GIAO DIỆN HỘP THOẠI XÁC NHẬN XÓA (MODAL) -->
<div id="vg-delete-modal" class="vg-modal-overlay">
    <div class="vg-modal-box">
        <span class="material-symbols-outlined vg-modal-icon">warning</span>
        <div class="vg-modal-title">Cảnh báo hệ thống</div>
        <div class="vg-modal-text">Xác nhận loại bỏ thiết bị UAV này khỏi đội hình chiến thuật của bạn?</div>
        <div class="vg-modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">HỦY BỎ</button>
            <button type="button" class="btn-modal-confirm" onclick="executeDelete()">XÁC NHẬN</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentDeleteFormId = null;

    function openDeleteModal(itemId) {
        currentDeleteFormId = 'delete-form-' + itemId;
        document.getElementById('vg-delete-modal').classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('vg-delete-modal').classList.remove('active');
        currentDeleteFormId = null;
    }

    function executeDelete() {
        if (currentDeleteFormId) {
            document.getElementById(currentDeleteFormId).submit();
        }
    }
</script>
@endpush