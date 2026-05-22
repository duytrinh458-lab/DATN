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
            <div class="header-glow"></div>
            <h2 class="orders-title">LỊCH SỬ Đơn Hàng</h2>
        </div>

        @if(session('success'))
            <div class="alert success">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert error">
                <span class="material-symbols-outlined">warning</span>
                {{ session('error') }}
            </div>
        @endif

        @if(isset($orders) && $orders->count() > 0)
            <div class="orders-grid">
                @foreach($orders as $order)
                    @php
                        $isRefunding = \Illuminate\Support\Facades\DB::table('refunds')
                            ->where('order_id', $order->id)
                            ->where('status', 'pending')
                            ->exists();
                        
                        $items = $order->orderItems ?? collect(); 
                        $firstItem = $items->first();
                        
                        // LẤY ẢNH TỪ BẢNG product_images
                        $imagePath = null;
                        if ($firstItem) {
                            $imagePath = \Illuminate\Support\Facades\DB::table('product_images')
                                ->where('product_id', $firstItem->product_id)
                                ->orderBy('position', 'asc')
                                ->value('image_url');
                        }
                        
                        // 🛠️ ĐÃ SỬA: Bỏ chữ 'storage/' đi cho giống y hệt trang Products của bác
                        $imageUrl = $imagePath 
                            ? asset($imagePath) 
                            : asset('images/default-uav.jpg');
                        
                        $totalItemsCount = $items->count() > 0 ? $items->count() : 1;
                    @endphp

                    <div class="order-card">
                        {{-- IMAGE --}}
                        <div class="order-image">
                            <img src="{{ $imageUrl }}" alt="Product Image">
                            @if($totalItemsCount > 1)
                                <div class="item-count-badge">+{{ $totalItemsCount - 1 }} item</div>
                            @endif
                            <div class="image-overlay"></div>
                        </div>

                        {{-- MAIN --}}
                        <div class="order-main">
                            <div class="order-header-row">
                                <div>
                                    <div class="order-code">
                                        <span class="material-symbols-outlined icon-small">tag</span>
                                        {{ $order->order_code }}
                                    </div>
                                    <div class="order-date">
                                        <span class="material-symbols-outlined icon-small">schedule</span>
                                        {{ $order->ordered_at->format('d/m/Y - H:i') }}
                                    </div>
                                </div>

                                <div class="status-wrapper">
                                    @if($order->status == 'pending')
                                        <span class="status-badge status-pending">
                                            <span class="status-dot"></span> CHỜ DUYỆT
                                        </span>
                                    @elseif($order->status == 'shipping')
                                        <span class="status-badge status-shipping">
                                            <span class="status-dot"></span> ĐANG GIAO
                                        </span>
                                    @elseif($order->status == 'delivered')
                                        @if($isRefunding)
                                            <span class="status-badge status-refunding">
                                                <span class="status-dot"></span> CHỜ HOÀN TRẢ
                                            </span>
                                        @else
                                            <span class="status-badge status-delivered">
                                                <span class="status-dot"></span> HOÀN THÀNH
                                            </span>
                                        @endif
                                    @elseif($order->status == 'refunded')
                                        <span class="status-badge status-refunded">
                                            <span class="status-dot"></span> ĐÃ HOÀN TIỀN
                                        </span>
                                    @elseif($order->status == 'cancelled')
                                        <span class="status-badge status-cancelled">
                                            <span class="status-dot"></span> ĐÃ HỦY
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="order-info-grid">
                                <div class="info-box">
                                    <div class="order-info-label">Ngân sách (Total)</div>
                                    <div class="order-total-value">
                                        {{ number_format($order->total, 0, ',', '.') }}₫
                                    </div>
                                </div>

                                <div class="info-box">
                                    <div class="order-info-label">Phương thức (Method)</div>
                                    <div class="order-info-value">
                                        <span class="material-symbols-outlined icon-small">account_balance_wallet</span> 
                                        VANGUARD PAY
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ACTION --}}
                        <div class="order-actions">
                            <a href="{{ route('user.orders.show', $order->id) }}" class="btn-view">
                                <span class="material-symbols-outlined">visibility</span>
                                XEM CHI TIẾT
                            </a>

                            @if($order->status == 'pending')
                                <button class="btn-cancel-order"
                                    onclick="openCancelModal({{ $order->id }}, '{{ $order->order_code }}')">
                                    <span class="material-symbols-outlined">cancel</span>
                                    HỦY LỆNH
                                </button>

                                <form id="cancel-form-{{ $order->id }}"
                                      action="{{ route('user.orders.cancel', $order->id) }}"
                                      method="POST" style="display:none;">
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
                <div class="empty-icon-wrapper">
                    <span class="material-symbols-outlined">flight_takeoff</span>
                </div>
                <h3>NO MISSION DATA</h3>
                <p>Chưa có chiến dịch nào được triển khai.</p>
                <a href="{{ route('user.products') }}" class="btn-primary-glow">ĐẾN KHO UAV</a>
            </div>
        @endif

    </div>
</div>

{{-- MODAL --}}
<div id="vg-cancel-modal" class="vg-modal">
    <div class="vg-modal-backdrop" onclick="closeCancelModal()"></div>
    <div class="vg-modal-box">
        <div class="vg-modal-header">
            <span class="material-symbols-outlined warning-icon">warning</span>
            <div class="vg-modal-title">CẢNH BÁO HỦY LỆNH</div>
        </div>
        
        <div class="vg-modal-text">
            Xác nhận hủy bỏ chiến dịch <strong id="modal-order-code" class="highlight-code"></strong>? Hệ thống sẽ ghi nhận thao tác này vào nhật ký.
        </div>

        <textarea id="cancel-reason-field" class="vg-textarea" placeholder="Nhập lý do hủy lệnh (bắt buộc)..."></textarea>

        <div class="vg-modal-actions">
            <button class="btn-modal-cancel" onclick="closeCancelModal()">QUAY LẠI</button>
            <button class="btn-modal-confirm" onclick="executeCancel()">XÁC NHẬN HỦY</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentOrderId = null;

function openCancelModal(id, code){
    currentOrderId = id;
    document.getElementById('modal-order-code').innerText = code;
    document.getElementById('vg-cancel-modal').classList.add('active');
}

function closeCancelModal(){
    document.getElementById('vg-cancel-modal').classList.remove('active');
    document.getElementById('cancel-reason-field').value = '';
    currentOrderId = null;
}

function executeCancel(){
    if(currentOrderId){
        let reason = document.getElementById('cancel-reason-field').value.trim();
        if(reason === '') {
            alert('Vui lòng nhập lý do hủy lệnh!');
            return;
        }
        document.getElementById('reason-input-' + currentOrderId).value = reason;
        document.getElementById('cancel-form-' + currentOrderId).submit();
    }
}
</script>
@endpush