@extends('User.layouts.app')

@section('title', 'Thanh toán - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user/checkout.css') }}">
@endpush

@section('content')
<div class="checkout-viewport">
    <div class="checkout-container">
        <h2 class="checkout-title">THANH TOÁN</h2>
        <p class="checkout-subtitle">HỆ THỐNG GIAO DỊCH VANGUARD-07 // SECURE LINE</p>

        <form action="{{ route('user.orders.confirm') }}" method="POST">
            @csrf
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div style="flex: 1.2; min-width: 350px;">
                    <div class="checkout-box">
                        <h4>THÔNG TIN GIAO HÀNG</h4>
                        <div class="shipping-grid">
                            <div class="full-width">
                                <input type="text" name="name" class="form-control" placeholder="Họ tên đặc vụ" required>
                            </div>
                            
                            <input type="text" name="phone" class="form-control" placeholder="Số điện thoại" required>
                            <input type="email" name="email" class="form-control" placeholder="Email nhận hóa đơn">
                            
                            <div class="full-width">
                                <input type="text" name="address" class="form-control" placeholder="Địa chỉ chi tiết" required>
                            </div>

                            <input type="text" name="city" class="form-control" placeholder="Tỉnh / Thành phố">
                            <input type="text" name="district" class="form-control" placeholder="Quận / Huyện">
                        </div>
                    </div>

                    <div class="checkout-box">
                        <h4>PHƯƠNG THỨC THANH TOÁN</h4>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="vpay" checked> 
                            <span>Ví điện tử Vanguard (V-Pay)</span>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="cod"> 
                            <span>Thanh toán khi nhận hàng (COD)</span>
                        </label>
                    </div>
                </div>

                <div style="flex: 0.8; min-width: 320px;">
                    <div class="checkout-box">
                        <h4>TÓM TẮT ĐƠN HÀNG</h4>
                        <div style="margin-bottom: 20px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                <span>Vanguard X1 Alpha</span>
                                <span>45,000,000₫</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; opacity:0.7; font-size:13px;">
                                <span>Phí vận chuyển</span>
                                <span>250,000₫</span>
                            </div>
                        </div>

                        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; text-align: right;">
                            <span style="font-size: 12px; opacity: 0.6; display: block; margin-bottom: 5px;">TỔNG QUYẾT TOÁN</span>
                            <div class="grand-total">45,250,000₫</div>
                        </div>
                        
                        <button type="submit" class="btn-confirm">XÁC NHẬN GIAO DỊCH</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection