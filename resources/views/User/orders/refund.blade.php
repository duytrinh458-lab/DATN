@extends('User.layouts.app')

@section('title', 'Báo Cáo Sự Cố - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/refund.css') }}">
@endpush

@section('content')
<div class="refund-viewport">
    <div class="refund-container">
        
        <a href="{{ route('user.orders.show', $order->id) }}" style="color: #6b7280; text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> HUỶ BỎ VÀ QUAY LẠI
        </a>

        <div class="refund-box">
            <div class="refund-header">
                <h2>Yêu Cầu Hoàn Trả</h2>
                <p style="font-size: 13px; opacity: 0.6;">VANGUARD RETURN PROTOCOL</p>
            </div>

            <div class="order-mini-info">
                <span>Mã đơn: <strong>#{{ $order->order_code }}</strong></span>
                <span>Giá trị: <strong style="color: #ff4757;">{{ number_format($order->total, 0, ',', '.') }}₫</strong></span>
            </div>

            <form action="{{ route('user.orders.refund.submit', $order->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Lý do hoàn hàng</label>
                    <select name="reason" class="hud-input" required>
                        <option value="" disabled selected>-- Chọn phân loại lỗi --</option>
                        <option value="Lỗi kỹ thuật phần cứng (Động cơ/Pin)">Lỗi kỹ thuật phần cứng (Động cơ/Pin)</option>
                        <option value="Lỗi phần mềm điều khiển / Kết nối">Lỗi phần mềm điều khiển / Kết nối</option>
                        <option value="Sản phẩm bể vỡ do vận chuyển">Sản phẩm bể vỡ do vận chuyển</option>
                        <option value="Giao sai mẫu UAV / Thiếu phụ kiện">Giao sai mẫu UAV / Thiếu phụ kiện</option>
                        <option value="Khác">Lý do khác...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết tình trạng</label>
                    <textarea name="description" class="hud-textarea" placeholder="Vui lòng cung cấp chi tiết về lỗi thiết bị để hệ thống thẩm định nhanh nhất..." required></textarea>
                </div>

                <button type="submit" class="btn-refund-submit">
                    Gửi báo cáo hoàn trả
                </button>

                <div class="warning-note">
                    * Yêu cầu của bạn sẽ được đội ngũ kỹ thuật thẩm định trong 24h.<br>
                    Tiền hoàn trả sẽ được chuyển trực tiếp vào ví V-Pay của bạn sau khi chấp thuận.
                </div>
            </form>
        </div>
    </div>
</div>
@endsection