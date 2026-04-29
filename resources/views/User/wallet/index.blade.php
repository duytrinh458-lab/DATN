@extends('User.layouts.app')

@section('title', 'Vanguard Wallet')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/wallet.css') }}">
@endpush

@section('content')
<div class="wallet-viewport">
    <div class="wallet-main">
        <h1 style="font-family: 'Space Grotesk'; font-size: 3.5rem; font-weight: 900; font-style: italic; color: var(--primary); margin: 0;">WALLET</h1>
        <p style="font-size: 10px; letter-spacing: 5px; color: var(--primary); opacity: 0.6; margin-bottom: 50px;">FINANCIAL CONTROL TERMINAL</p>

        <div class="wallet-module" style="background: linear-gradient(135deg, rgba(153, 247, 255, 0.1), transparent);">
            <span class="module-title">Số dư khả dụng</span>
            <div style="font-size: 3rem; font-weight: 900; font-family: 'Space Grotesk';">50.000.000₫</div>
        </div>

        <div class="wallet-module">
            <span class="module-title">Cấu hình triển khai</span>
            <div style="display: grid; gap: 20px;">
                <input type="text" class="wallet-input" placeholder="Tên đặc vụ nhận thiết bị">
                <input type="text" class="wallet-input" placeholder="Tọa độ giao hàng (Địa chỉ)">
            </div>
        </div>

        <div class="wallet-module">
            <span class="module-title">Phương thức xác thực</span>
            <div class="payment-card active">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                    <span style="font-weight: 700;">Ví Vanguard</span>
                </div>
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>
    </div>

    <aside class="wallet-sidebar">
        <span class="module-title" style="color: #00363d; opacity: 0.5;">Chi tiết lệnh thanh toán</span>
        
        <div style="margin: 40px 0; border-bottom: 1px solid rgba(0, 54, 61, 0.1); padding-bottom: 25px;">
            <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.2rem;">
                <span>Phantom X-Drone</span>
                <span>25.000.000₫</span>
            </div>
            <span style="font-size: 10px; opacity: 0.6;">MODEL: SERIES-V // QTY: 01</span>
        </div>

        <div style="margin-bottom: 50px;">
            <span class="module-title" style="color: #00363d; margin-bottom: 5px;">Tổng quyết toán</span>
            <div style="font-size: 3.5rem; font-weight: 900; letter-spacing: -3px;">25.000.000₫</div>
        </div>

        <button class="btn-confirm">Kích hoạt thanh toán</button>
    </aside>
</div>
@endsection