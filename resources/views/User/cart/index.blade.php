@extends('User.layouts.app') 

@section('title', 'Giỏ Hàng Chiến Thuật - Vanguard UAV')

@push('styles')
    {{-- Gọi file CSS riêng --}}
    <link rel="stylesheet" href="{{ asset('Css/User/cart.css') }}">
    {{-- Thêm icon Google để dùng cho nút xóa --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endpush

@section('content')
<div class="vanguard-cart-page">
    <div class="cart-header">
        <h2>Giỏ hàng chiến lược</h2>
        <div style="color: var(--outline); font-size: 10px; text-transform: uppercase; letter-spacing: 4px;">Tactical Resource Management</div>
    </div>

    <div class="cart-flex-wrapper">
        {{-- DANH SÁCH SẢN PHẨM --}}
        <div class="cart-items-list">
            @if(session('cart') && count(session('cart')) > 0)
                @foreach(session('cart') as $id => $details)
                    <div class="cart-item-module">
                        <div class="item-img-box">
                            <img src="{{ asset($details['image']) }}" alt="{{ $details['name'] }}">
                        </div>
                        
                        <div class="item-info">
                            <div class="item-sku">UAV-{{ $id }}</div>
                            <div class="item-name">{{ $details['name'] }}</div>
                        </div>

                        <div class="quantity-hud">
                            <button>-</button>
                            <input type="number" value="{{ $details['quantity'] }}" readonly>
                            <button>+</button>
                        </div>

                        <div style="text-align: right; min-width: 120px;">
                            <div style="font-size: 10px; color: var(--outline); text-transform: uppercase;">Giá đơn vị</div>
                            <div style="font-weight: 900; color: var(--primary);">{{ number_format($details['price'], 0, ',', '.') }}₫</div>
                        </div>

                        <button style="background:transparent; border:none; color:var(--outline); cursor:pointer;">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                @endforeach
            @else
                <div style="padding: 100px; text-align: center; background: var(--surface-container-low); border-radius: 40px;">
                    <p style="color: var(--outline); text-transform: uppercase; letter-spacing: 2px;">Kho hàng của đặc vụ đang trống</p>
                    <a href="{{ url('/products') }}" style="color: var(--primary); text-decoration: underline; margin-top: 20px; display: inline-block;">Quay lại kho vũ khí</a>
                </div>
            @endif
        </div>

        {{-- TÓM TẮT ĐƠN HÀNG (VÍ) --}}
        <aside class="cart-summary-vanguard">
            <div class="summary-card-inner">
                <div class="summary-title">Ngân sách dự kiến</div>
                
                <div class="summary-row">
                    <span>TẠM TÍNH</span>
                    <span>25.000.000đ</span>
                </div>
                
                <div class="summary-row">
                    <span>VẬN CHUYỂN</span>
                    <span>FREE</span>
                </div>

                <div class="total-row">
                    <div style="font-size: 10px; font-weight: 900; opacity: 0.6;">TỔNG CỘNG</div>
                    <div class="total-price-display">25.000.000₫</div>
                </div>

                <button class="btn-checkout-vanguard">Tiến hành thanh toán</button>
                
                <a href="{{ url('/products') }}" style="display: block; text-align: center; margin-top: 20px; font-size: 10px; font-weight: 900; text-decoration: none; color: inherit; opacity: 0.5;">
                    ← TIẾP TỤC ĐIỀU ĐỘNG THIẾT BỊ
                </a>
            </div>
        </aside>
    </div>
</div>
@endsection