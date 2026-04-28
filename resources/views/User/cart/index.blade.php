@extends('User.layouts.app') 
@section('title', 'Giỏ Hàng - Cửa Hàng UAV')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/cart.css') }}">
@endpush

@section('content')
<div class="cart-container">
    <h2 class="cart-title">Giỏ Hàng Của Bạn</h2>

    <div class="cart-wrapper">
        <div class="cart-items">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="product-info">
                            <img src="https://via.placeholder.com/80" alt="UAV Drone">
                            <div>
                                <p class="product-name">Drone Phantom X-100</p>
                                <span class="product-sku">Mã: UAV-001</span>
                            </div>
                        </td>
                        <td>25,000,000đ</td>
                        <td>
                            <div class="quantity-control">
                                <button>-</button>
                                <input type="number" value="1" min="1">
                                <button>+</button>
                            </div>
                        </td>
                        <td class="total-price">25,000,000đ</td>
                        <td><button class="remove-btn">&times;</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="cart-summary">
            <h3>Tóm tắt đơn hàng</h3>
            <div class="summary-item">
                <span>Tạm tính:</span>
                <span>25,000,000đ</span>
            </div>
            <div class="summary-item">
                <span>Phí vận chuyển:</span>
                <span>Miễn phí</span>
            </div>
            <hr>
            <div class="summary-item total">
                <span>Tổng cộng:</span>
                <span class="final-price">25,000,000đ</span>
            </div>
            <button class="checkout-btn">TIẾN HÀNH THANH TOÁN</button>
            <a href="{{ url('/products') }}" class="back-link">← Tiếp tục mua sắm</a>
        </div>
    </div>
</div>
@endsection