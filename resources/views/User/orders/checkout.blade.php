@extends('User.layouts.app')

@section('title', 'Thanh toán - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user/checkout.css') }}">
@endpush

@section('content')
@php
    // Lấy dữ liệu sản phẩm khách vừa bấm "Mua ngay"
    $item = session('checkout_item');
    
    // Lấy thông tin người dùng và địa chỉ mặc định
    $user = Auth::user();
    $defaultAddress = \App\Models\Address::where('user_id', $user->id)->where('is_default', 1)->first();
    
    // Tính toán tiền tệ
    $subtotal = $item ? ($item['price'] * $item['quantity']) : 0;
    $shipping_fee = 30000; // Phí vận chuyển mặc định
    $total = $subtotal + $shipping_fee;
@endphp

<div class="checkout-viewport">
    <div class="checkout-container">
        <h2 class="checkout-title">THANH TOÁN</h2>
        <p class="checkout-subtitle">HỆ THỐNG GIAO DỊCH VANGUARD-07 // SECURE LINE</p>

        <form action="{{ route('user.checkout.process') }}" method="POST">
            @csrf
            <!-- Giữ nguyên các thẻ inline-flex của bản thiết kế gốc do bạn làm -->
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
                        
                        @if($item)
                            <div style="margin-bottom: 20px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                    <span>{{ $item['name'] }} <strong style="color: #00eaff;">x{{ $item['quantity'] }}</strong></span>
                                    <span>{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                                </div>
                                <div style="display:flex; justify-content:space-between; opacity:0.7; font-size:13px;">
                                    <span>Phí vận chuyển</span>
                                    <span>{{ number_format($shipping_fee, 0, ',', '.') }}₫</span>
                                </div>
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