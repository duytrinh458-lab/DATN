@extends('User.layouts.app')

@section('title', 'Lịch sử Chiến dịch - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/orders.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endpush

@section('content')
<div class="orders-viewport">
    <div class="orders-container">
        
        <div class="orders-header">
            <h2 class="orders-title">LỊCH SỬ CHIẾN DỊCH</h2>
            <p class="orders-subtitle">BÁO CÁO CÁC LỆNH ĐIỀU ĐỘNG UAV</p>
        </div>

        @if(session('success'))
            <div style="background: rgba(0, 255, 136, 0.1); color: #00ff88; border: 1px solid rgba(0, 255, 136, 0.3); padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center; font-weight: bold;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background: rgba(255, 71, 87, 0.1); color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.3); padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center; font-weight: bold;">
                {{ session('error') }}
            </div>
        @endif

        @if(isset($orders) && $orders->count() > 0)
            <div class="orders-grid">
                @foreach($orders as $order)
                    <div class="order-card">
                        <div class="order-header-row">
                            <div>
                                <div class="order-code">{{ $order->order_code }}</div>
                                <div class="order-date">{{ $order->ordered_at->format('d/m/Y - H:i') }}</div>
                            </div>
                            
                            @if($order->status == 'pending')
                                <span class="status-badge status-pending">CHỜ DUYỆT</span>
                            @elseif($order->status == 'shipping')
                                <span class="status-badge status-shipping">ĐANG GIAO</span>
                            @elseif($order->status == 'delivered')
                                <span class="status-badge status-delivered">HOÀN THÀNH</span>
                            @elseif($order->status == 'cancelled')
                                <span class="status-badge status-cancelled">ĐÃ HỦY</span>
                            @endif
                        </div>

                        <div class="order-info-row">
                            <span class="order-info-label">Ngân sách huy động:</span>
                            <span class="order-total-value">{{ number_format($order->total, 0, ',', '.') }}₫</span>
                        </div>
                        
                        <div class="order-info-row">
                            <span class="order-info-label">Phương thức:</span>
                            <span class="order-info-value">Ví Vanguard (V-Pay)</span>
                        </div>

                        <div class="order-actions">
                            <a href="{{ route('user.orders.show', $order->id) }}" class="btn-view">
                                XEM CHI TIẾT
                            </a>
                            
                            <!-- Chỉ hiện nút Hủy lệnh khi đơn hàng đang chờ duyệt -->
                            @if($order->status == 'pending')
                                <button type="button" class="btn-cancel-order" onclick="openCancelModal({{ $order->id }}, '{{ $order->order_code }}')">
                                    HỦY LỆNH
                                </button>
                                
                                <!-- Form ngầm để gửi request Hủy -->
                                <form id="cancel-form-{{ $order->id }}" action="{{ route('user.orders.cancel', $order->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    <input type="hidden" name="reason" id="reason-input-{{ $order->id }}">
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-orders">
                <span class="material-symbols-outlined empty-orders-icon">flight_takeoff</span>
                <h3 style="color: #fff; margin-bottom: 10px;">KHÔNG CÓ DỮ LIỆU</h3>
                <p style="color: var(--outline); font-size: 14px; margin-bottom: 25px;">Chưa có lệnh điều động UAV nào được thực hiện trong hồ sơ của bạn.</p>
                <a href="{{ route('user.products') }}" style="display: inline-block; padding: 12px 25px; background: var(--primary); color: #00363d; font-weight: bold; border-radius: 8px; text-decoration: none;">ĐẾN KHO VŨ KHÍ</a>
            </div>
        @endif

    </div>
</div>

<!-- GIAO DIỆN MODAL XÁC NHẬN HỦY ĐƠN -->
<div id="vg-cancel-modal" class="vg-modal-overlay">
    <div class="vg-modal-box">
        <span class="material-symbols-outlined vg-modal-icon">warning</span>
        <div class="vg-modal-title">CẢNH BÁO HỦY LỆNH</div>
        <div class="vg-modal-text">Bạn sắp hủy lệnh điều động <strong id="modal-order-code" style="color:#fff;"></strong>. Tọa độ và số lượng UAV sẽ được hoàn trả về kho tổng.</div>
        
        <textarea id="cancel-reason-field" class="cancel-reason-input" rows="3" placeholder="Lý do hủy (Không bắt buộc)..."></textarea>

        <div class="vg-modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeCancelModal()">QUAY LẠI</button>
            <button type="button" class="btn-modal-confirm" onclick="executeCancel()">XÁC NHẬN HỦY</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentOrderId = null;

    function openCancelModal(orderId, orderCode) {
        currentOrderId = orderId;
        document.getElementById('modal-order-code').innerText = orderCode;
        document.getElementById('cancel-reason-field').value = ''; // Reset textarea
        document.getElementById('vg-cancel-modal').classList.add('active');
    }

    function closeCancelModal() {
        document.getElementById('vg-cancel-modal').classList.remove('active');
        currentOrderId = null;
    }

    function executeCancel() {
        if (currentOrderId) {
            // Lấy lý do nhập từ modal
            let reason = document.getElementById('cancel-reason-field').value;
            // Gán vào form ngầm tương ứng
            document.getElementById('reason-input-' + currentOrderId).value = reason;
            // Gửi form đi
            document.getElementById('cancel-form-' + currentOrderId).submit();
        }
    }
</script>
@endpush