@extends('User.layouts.app')

@section('title', 'Chi tiết Chiến dịch - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/order_detail.css') }}">
@endpush

@section('content')
<div class="order-detail-viewport">
    <div class="order-detail-container">
        
        <a href="{{ route('user.orders.index') }}" class="back-link">
            <span class="material-symbols-outlined">arrow_back</span> TRỞ LẠI DANH SÁCH
        </a>

        <div class="detail-box text-center header-box">
            <div class="status-indicator">
                MÃ CHIẾN DỊCH: {{ $order->order_code }}
            </div>
            <h2>BÁO CÁO CHI TIẾT</h2>
            <p class="order-date">KHỞI TẠO LÚC: {{ $order->ordered_at->format('d/m/Y H:i:s') }}</p>
        </div>

        <div class="detail-grid">
            <div class="left-col">
                <div class="detail-box">
                    <h4>HẠM ĐỘI ĐIỀU ĐỘNG ({{ $order->orderItems->count() }})</h4>
                    @foreach($order->orderItems as $item)
                        <div class="product-item">
                            <img src="{{ asset($item->product->images->first()->image_url ?? 'default.jpg') }}" class="product-img">
                            <div class="product-info">
                                <h5>{{ $item->product->name }}</h5>
                                <p class="quantity-text">Số lượng: x{{ $item->quantity }}</p>
                                <span class="product-price">{{ number_format($item->unit_price, 0, ',', '.') }}₫</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="right-col">
                <div class="detail-box">
                    <h4>TỌA ĐỘ GIAO NHẬN</h4>
                    <p class="address-text">
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
                        <span class="free-ship">FREE</span>
                    </div>
                    <div class="finance-row total-row">
                        <span class="bold-text">TỔNG CỘNG</span>
                        <span class="grand-total-text">{{ number_format($order->total, 0, ',', '.') }}₫</span>
                    </div>
                    
                    <div class="status-box">
                        TRẠNG THÁI: 
                        @if($order->status == 'pending') 
                            <span class="status-text warning">CHỜ DUYỆT</span>
                        @elseif($order->status == 'shipping') 
                            <span class="status-text info">ĐANG GIAO</span>
                        @elseif($order->status == 'delivered') 
                            @if(isset($hasRefundRequest) && $hasRefundRequest)
                                <span class="status-text warning-bold">CHỜ DUYỆT HOÀN TRẢ</span>
                            @else
                                <span class="status-text success">ĐÃ HOÀN THÀNH</span>
                            @endif
                        @elseif($order->status == 'refunded') 
                            <span class="status-text neon-purple">ĐÃ TRẢ HÀNG & HOÀN TIỀN</span>
                        @else 
                            <span class="status-text danger">ĐÃ HỦY</span>
                        @endif
                    </div>

                    @if($order->status == 'delivered' && !$hasRefundRequest)
                        <div class="refund-action-box danger-box">
                            <h4 class="text-center danger-title">⚠️ GẶP SỰ CỐ VỚI THIẾT BỊ?</h4>
                            <p class="text-center danger-desc">Báo cáo lỗi kỹ thuật hoặc sự cố vận chuyển trong vòng 24h để được hệ thống hoàn tiền vào ví V-Pay.</p>
                            <a href="{{ route('user.orders.refund', $order->id) }}" class="btn-neon-danger">KHỞI TẠO HOÀN TRẢ</a>
                        </div>
                    @elseif($hasRefundRequest || $order->status == 'refunded')
                        <div class="refund-action-box info-box text-center">
                            <span class="material-symbols-outlined info-icon">verified_user</span>
                            <div class="info-title">
                                @if($order->status == 'refunded')
                                    YÊU CẦU HOÀN TIỀN ĐÃ HOÀN TẤT
                                @else
                                    YÊU CẦU HOÀN TRẢ ĐANG ĐƯỢC THẨM ĐỊNH
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>
@endsection