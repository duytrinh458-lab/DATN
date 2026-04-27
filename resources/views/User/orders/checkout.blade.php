@extends('User.layouts.app')

@section('title', 'Xác nhận thanh toán')

@section('content')
<div class="container py-5" style="color: white; background: #0f172a; border-radius: 15px; padding: 30px; margin-top: 50px;">
    <h2 class="mb-4" style="color: #00eaff;">Xác nhận đơn hàng của bạn</h2>

    <div class="row">
        <div class="col-md-8">
            <div style="background: #1e293b; padding: 20px; border-radius: 10px;">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <img src="{{ $product->images->first() ? asset($product->images->first()->image_url) : asset('images/uav1.jpg') }}" width="70" class="rounded mr-2">
                                <span>{{ $product->name }}</span>
                            </td>
                            <td>{{ number_format($product->sale_price, 0, ',', '.') }}₫</td>
                            <td>1</td>
                            <td style="color: #00ff88;">{{ number_format($product->sale_price, 0, ',', '.') }}₫</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div style="background: #1e293b; padding: 20px; border-radius: 10px; border: 1px solid #00eaff;">
                <h4>Tổng thanh toán</h4>
                <hr style="background: #334155;">
                <div class="d-flex justify-content-between mb-3">
                    <span>Tiền hàng:</span>
                    <span>{{ number_format($product->sale_price, 0, ',', '.') }}₫</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <strong>Tổng cộng:</strong>
                    <strong style="color: #00ff88; font-size: 1.4rem;">{{ number_format($product->sale_price, 0, ',', '.') }}₫</strong>
                </div>

                {{-- FORM GỬI ĐẾN HÀM CONFIRM --}}
                <form action="{{ route('user.orders.confirm') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="btn-buy" style="width: 100%; padding: 15px; background: linear-gradient(45deg, #00eaff, #007bff); border: none; color: white; font-weight: bold; border-radius: 8px; cursor: pointer;">
                        XÁC NHẬN VÀ ĐẶT HÀNG
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection