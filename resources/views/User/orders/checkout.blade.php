@extends('User.layouts.app')

@section('title', 'Thanh toán - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/checkout.css') }}">
@endpush

@section('content')
<div class="checkout-viewport">
    <div class="checkout-container">
        <h2 class="checkout-title">THANH TOÁN</h2>
        <p class="checkout-subtitle">HỆ THỐNG GIAO DỊCH VANGUARD-07 // SECURE LINE</p>

        <!-- Route trỏ về hàm placeOrder trong Controller -->
        <form action="{{ route('user.checkout.process') }}" method="POST">
            @csrf
            
            <!-- Truyền ID địa chỉ lên Controller một cách âm thầm -->
            @if($defaultAddress)
                <input type="hidden" name="address_id" value="{{ $defaultAddress->id }}">
            @endif

            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div style="flex: 1.2; min-width: 350px;">
                    <div class="checkout-box">
                        <h4>THÔNG TIN GIAO HÀNG</h4>
                        
                        @if($defaultAddress)
                            <div class="address-card">
                                <strong>Đặc vụ: {{ $defaultAddress->full_name }}</strong>
                                SĐT: {{ $defaultAddress->phone }}<br>
                                Tọa độ: {{ $defaultAddress->street }}<br>
                                Căn cứ: {{ $defaultAddress->district ? $defaultAddress->district . ', ' : '' }}{{ $defaultAddress->city ? $defaultAddress->city . ', ' : '' }}{{ $defaultAddress->province }}
                            </div>
                            <div class="address-card-note">
                                *Hệ thống sẽ giao hàng đến Tọa độ mặc định này. Để thay đổi, vui lòng cập nhật tại Cài đặt Hồ sơ.
                            </div>
                        @else
                            <div class="address-warning">
                                ⚠️ CẢNH BÁO: CHƯA CÓ TỌA ĐỘ GIAO HÀNG!<br>
                                Vui lòng quay lại <a href="{{ route('user.profile.index') }}">Hồ sơ</a> để thiết lập địa chỉ mặc định.
                            </div>
                        @endif
                    </div>

                    <div class="checkout-box">
                        <h4>PHƯƠNG THỨC THANH TOÁN</h4>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="wallet" checked> 
                            <span>Ví điện tử Vanguard (V-Pay)</span>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="cash"> 
                            <span>Thanh toán khi nhận hàng (COD)</span>
                        </label>
                    </div>
                </div>

                <div style="flex: 0.8; min-width: 320px;">
                    <div class="checkout-box">
                        <h4>TÓM TẮT ĐƠN HÀNG</h4>
                        
                        @if(isset($checkoutItems) && count($checkoutItems) > 0)
                            <!-- Danh sách sản phẩm (Có thể scroll nếu mua nhiều món) -->
                            <div class="checkout-items-list" style="margin-bottom: 20px; max-height: 300px; overflow-y: auto; padding-right: 10px;">
                                @foreach($checkoutItems as $item)
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" style="width: 45px; height: 45px; border-radius: 8px; object-fit: cover; border: 1px solid var(--outline);">
                                        <span style="font-size: 14px;">{{ $item['name'] }} <br><strong style="color: #00eaff;">x{{ $item['quantity'] }}</strong></span>
                                    </div>
                                    <span style="font-size: 14px; font-weight: bold; margin-top: auto;">{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}₫</span>
                                </div>
                                @endforeach
                            </div>

                            <div style="display:flex; justify-content:space-between; opacity:0.7; font-size:13px; margin-bottom: 10px;">
                                <span>Tạm tính</span>
                                <span>{{ number_format($total, 0, ',', '.') }}₫</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; opacity:0.7; font-size:13px; margin-bottom: 20px;">
                                <span>Phí vận chuyển</span>
                                <span style="color: #00ff88; font-weight: bold;">FREE</span>
                            </div>

                            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; text-align: right;">
                                <span style="font-size: 12px; opacity: 0.6; display: block; margin-bottom: 5px;">TỔNG QUYẾT TOÁN</span>
                                <div class="grand-total">{{ number_format($total, 0, ',', '.') }}₫</div>
                            </div>
                            
                            <button type="submit" class="btn-confirm" {{ !$defaultAddress ? 'disabled' : '' }}>
                                XÁC NHẬN GIAO DỊCH
                            </button>
                        @else
                            <div class="error-text">Lỗi: Không tìm thấy sản phẩm trong phiên giao dịch.</div>
                        @endif
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection