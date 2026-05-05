@extends('User.layouts.app')

@section('title', 'Chi tiết Chiến dịch - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/order_detail.css') }}">
@endpush

@section('content')
<div class="order-detail-viewport">
    <div class="order-detail-container">
        
        <a href="{{ route('user.orders.index') }}" style="color: #00eaff; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <span class="material-symbols-outlined">arrow_back</span> TRỞ LẠI DANH SÁCH
        </a>

        <div class="detail-box" style="text-align: center; border-color: #00eaff;">
            <div class="status-indicator" style="background: rgba(0, 234, 255, 0.1); color: #00eaff; border: 1px solid #00eaff;">
                MÃ CHIẾN DỊCH: {{ $order->order_code }}
            </div>
            <h2 style="margin: 10px 0; letter-spacing: 2px;">BÁO CÁO CHI TIẾT</h2>
            <p style="font-size: 13px; opacity: 0.6;">KHỞI TẠO LÚC: {{ $order->ordered_at->format('d/m/Y H:i:s') }}</p>
        </div>

        <div class="detail-grid">
            <!-- CỘT TRÁI: DANH SÁCH SẢN PHẨM -->
            <div class="left-col">
                <div class="detail-box">
                    <h4>HẠM ĐỘI ĐIỀU ĐỘNG ({{ $order->orderItems->count() }})</h4>
                    @foreach($order->orderItems as $item)
                        <div class="product-item">
                            <img src="{{ asset($item->product->images->first()->image_url ?? 'default.jpg') }}" class="product-img">
                            <div class="product-info">
                                <h5>{{ $item->product->name }}</h5>
                                <p style="font-size: 13px; opacity: 0.7;">Số lượng: x{{ $item->quantity }}</p>
                                <span class="product-price">{{ number_format($item->unit_price, 0, ',', '.') }}₫</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- CỘT PHẢI: TỌA ĐỘ VÀ THANH TOÁN -->
            <div class="right-col">
                <div class="detail-box">
                    <h4>TỌA ĐỘ GIAO NHẬN</h4>
                    <p style="font-size: 15px; line-height: 1.6;">
                        <strong>{{ $order->address->full_name }}</strong><br>
                        SĐT: {{ $order->address->phone }}<br>
                        Địa chỉ: {{ $order->address->street }}, {{ $order->address->district }}, {{ $order->address->city }}, {{ $order->address->province }}
                    </p>
                </div>

                <div class="detail-box">
                    <h4>QUYẾT TOÁN TÀI CHÍNH</h4>
                    <div class="finance-row">
                        <span>Giá trị thiết bị</span>
                        <span>{{ number_format($order->subtotal, 0, ',', '.') }}₫</span>
                    </div>
                    <div class="finance-row">
                        <span>Phí vận chuyển</span>
                        <span style="color: #00ff88;">FREE</span>
                    </div>
                    <div class="finance-row total-row">
                        <span style="font-weight: bold;">TỔNG CỘNG</span>
                        <span class="grand-total-text">{{ number_format($order->total, 0, ',', '.') }}₫</span>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: rgba(0,255,136,0.05); border-radius: 8px; border: 1px dashed #00ff88; text-align: center; font-size: 13px;">
                        TRẠNG THÁI: 
                        @if($order->status == 'pending') <span style="color: #ffab00;">CHỜ DUYỆT</span>
                        @elseif($order->status == 'shipping') <span style="color: #00eaff;">ĐANG GIAO</span>
                        @elseif($order->status == 'delivered') <span style="color: #00ff88;">ĐÃ HOÀN THÀNH</span>
                        @else <span style="color: #ff4757;">ĐÃ HỦY</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection