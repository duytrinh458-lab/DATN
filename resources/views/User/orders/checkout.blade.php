@extends('User.layouts.app')

@section('title', 'Thanh toán - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/checkout.css') }}">
@endpush

@section('content')
<div class="checkout-viewport">
    <div class="checkout-container">
        <h2 class="checkout-title">TIẾN TRÌNH THANH TOÁN</h2>
        <p class="checkout-subtitle vg-pulse-text">HỆ THỐNG GIAO DỊCH VANGUARD-07 // SECURE LINE ACTIVE</p>

        <form action="{{ route('user.checkout.process') }}" method="POST" id="vanguard-checkout-form">
            @csrf
            
            @if($defaultAddress)
                <input type="hidden" name="address_id" value="{{ $defaultAddress->id }}">
            @endif

            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div style="flex: 1.2; min-width: 350px;">
                    <div class="checkout-box">
                        <h4><span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 5px; font-size: 18px;">location_on</span> TỌA ĐỘ GIAO HÀNG</h4>
                        
                        @if($defaultAddress)
                            <div class="address-card">
                                <strong>Đặc vụ: {{ $defaultAddress->full_name }}</strong>
                                <span style="display:block; margin: 8px 0; color: #cbd5e1;"><span class="material-symbols-outlined" style="font-size: 14px; vertical-align: bottom;">call</span> {{ $defaultAddress->phone }}</span>
                                <div style="color: #94a3b8; font-size: 13px;">
                                    Tuyến đường: {{ $defaultAddress->street }}<br>
                                    Khu vực: {{ $defaultAddress->district ? $defaultAddress->district . ', ' : '' }}{{ $defaultAddress->city ? $defaultAddress->city . ', ' : '' }}{{ $defaultAddress->province }}
                                </div>
                            </div>
                            <div class="address-card-note">
                                *Hệ thống tự động khóa mục tiêu tại Tọa độ mặc định. Cập nhật tại Cài đặt Hồ sơ.
                            </div>
                        @else
                            <div class="address-warning">
                                <span class="material-symbols-outlined" style="font-size: 30px; margin-bottom: 10px; display:block;">warning</span>
                                <strong>CHƯA XÁC ĐỊNH TỌA ĐỘ!</strong><br>
                                Vui lòng truy cập <a href="{{ route('user.profile.index') }}">Hồ sơ</a> để thiết lập điểm rơi.
                            </div>
                        @endif
                    </div>

                    <div class="checkout-box">
                        <h4><span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 5px; font-size: 18px;">speaker_notes</span> CHỈ THỊ CHIẾN THUẬT (TÙY CHỌN)</h4>
                        <textarea name="note" class="form-control vg-textarea" placeholder="Nhập yêu cầu đặc biệt khi giao UAV (VD: Cần bảo mật đóng gói, Giao vào giờ hành chính...)" rows="3"></textarea>
                    </div>

                    <div class="checkout-box">
                        <h4><span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 5px; font-size: 18px;">account_balance_wallet</span> KÊNH THANH TOÁN</h4>
                        
                        <div class="vg-payment-options">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="wallet" checked> 
                                <div class="payment-content">
                                    <span class="material-symbols-outlined icon">account_balance</span>
                                    <div class="text-info">
                                        <span class="title">Ví điện tử V-Pay</span>
                                        <span class="desc">Thanh toán tức thì, bảo mật lượng tử</span>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash"> 
                                <div class="payment-content">
                                    <span class="material-symbols-outlined icon">local_shipping</span>
                                    <div class="text-info">
                                        <span class="title">Thanh toán khi nhận hàng (COD)</span>
                                        <span class="desc">Thanh toán tiền mặt cho đơn vị vận chuyển</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div style="flex: 0.8; min-width: 320px;">
                    <div class="checkout-box sticky-hud">
                        <h4><span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 5px; font-size: 18px;">receipt_long</span> TÓM TẮT ĐIỀU ĐỘNG</h4>
                        
                        @if(isset($checkoutItems) && count($checkoutItems) > 0)
                            <div class="checkout-items-list" style="margin-bottom: 20px; max-height: 350px; overflow-y: auto; padding-right: 10px;">
                                @foreach($checkoutItems as $item)
                                <div class="hud-item-row">
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}">
                                        <div class="hud-item-info">
                                            <span class="name">{{ $item['name'] }}</span>
                                            <span class="qty">Số lượng: <strong>{{ $item['quantity'] }}</strong></span>
                                        </div>
                                    </div>
                                    <span class="price">{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}₫</span>
                                </div>
                                @endforeach
                            </div>

                            <div class="summary-line">
                                <span>Ngân sách tạm tính</span>
                                <span>{{ number_format($total, 0, ',', '.') }}₫</span>
                            </div>
                            <div class="summary-line">
                                <span>Chi phí vận chuyển</span>
                                <span class="highlight-free">MIỄN PHÍ</span>
                            </div>

                            <div class="grand-total-box">
                                <span class="label">TỔNG NGÂN SÁCH CẤP PHÉP</span>
                                <div class="grand-total">{{ number_format($total, 0, ',', '.') }}₫</div>
                            </div>
                            
                            <button type="submit" class="btn-confirm" id="btn-submit-order" {{ !$defaultAddress ? 'disabled' : '' }}>
                                <span class="material-symbols-outlined">fingerprint</span>
                                <span id="btn-text">XÁC NHẬN GIAO DỊCH</span>
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

@push('scripts')
<script>
    // Xử lý hiệu ứng Loading khi bấm Đặt Hàng
    document.getElementById('vanguard-checkout-form').addEventListener('submit', function(e) {
        let btn = document.getElementById('btn-submit-order');
        let text = document.getElementById('btn-text');
        
        // Tránh click đúp
        if(btn.classList.contains('processing')) {
            e.preventDefault();
            return;
        }

        btn.classList.add('processing');
        text.innerText = "ĐANG MÃ HÓA DỮ LIỆU...";
        btn.style.opacity = "0.7";
        btn.style.cursor = "wait";
    });
</script>
@endpush