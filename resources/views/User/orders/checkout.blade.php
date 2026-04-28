@extends('User.layouts.app')

@section('title', 'Thanh toán')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user/checkout.css') }}">
@endpush

@section('content')
<header class="checkout-header">
    <h2>VANGUARD UAV</h2>
    <nav>
        <a href="#">Trang chủ</a> | <a href="#">Hỗ trợ</a>
    </nav>
</header>

<div class="checkout-container">
    <h2 class="checkout-title">THANH TOÁN</h2>
    <p class="checkout-subtitle">HỆ THỐNG GIAO DỊCH VANGUARD-07</p>

    <div class="row">
        <!-- Thông tin giao hàng -->
        <div class="col-md-6">
            <div class="checkout-box">
                <h4>THÔNG TIN GIAO HÀNG</h4>
                <form>
                    <input type="text" class="form-control" placeholder="Họ tên">
                    <input type="text" class="form-control" placeholder="Số điện thoại">
                    <input type="email" class="form-control" placeholder="Email">
                    <input type="text" class="form-control" placeholder="Địa chỉ chi tiết">
                    <input type="text" class="form-control" placeholder="Thành phố">
                    <input type="text" class="form-control" placeholder="Quận/Huyện">
                </form>
            </div>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="col-md-6">
            <div class="checkout-box">
                <h4>PHƯƠNG THỨC THANH TOÁN</h4>
                <div class="payment-method"><input type="radio" name="payment"> Ví điện tử V-Pay (QR Code)</div>
                <div class="payment-method"><input type="radio" name="payment"> Thẻ ngân hàng (Visa/Mastercard)</div>
                <div class="payment-method"><input type="radio" name="payment"> Chuyển khoản trực tiếp</div>
            </div>
        </div>
    </div>

    <!-- Tóm tắt đơn hàng -->
    <div class="checkout-box mt-4">
        <h4>TÓM TẮT ĐƠN HÀNG</h4>
        <ul class="order-summary">
            <li><span>Vanguard X1 Alpha</span><span>45,000,000₫</span></li>
            <li><span>V-Core Battery Pack</span><span>3,200,000₫</span></li>
        </ul>
        <div class="promo-code">
            <input type="text" class="form-control" placeholder="Mã khuyến mãi">
            <button class="btn-apply">Áp dụng</button>
        </div>
        <div class="order-total">
            <p>Tạm tính: 51,400,000₫</p>
            <p>Phí vận chuyển (Hỏa tốc): 250,000₫</p>
            <p>Giảm giá voucher: -1,500,000₫</p>
            <h5>TỔNG CỘNG: <span class="grand-total">50,150,000₫</span></h5>
        </div>
        <button class="btn-confirm">XÁC NHẬN THANH TOÁN</button>
    </div>
</div>

<footer class="checkout-footer">
    <h4>VANGUARD UAV</h4>
    <p>Tiên phong công nghệ giúp người lái kiểm soát hành trình của vạn vật quan sát tầm cao.</p>
    <div class="footer-links">
        <div><strong>SẢN PHẨM:</strong> Dòng Enterprise, Cinematic, Phụ kiện</div>
        <div><strong>HỖ TRỢ:</strong> Bảo hành, Hướng dẫn, Firmware</div>
        <div><strong>KẾT NỐI:</strong> © 2026 Aerial Vanguard</div>
    </div>
</footer>
@endsection
