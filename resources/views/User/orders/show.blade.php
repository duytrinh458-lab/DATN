@extends('User.layouts.app')

@section('title', 'Chi tiết Chiến dịch - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/order_detail.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endpush

@section('content')
<div class="vg-detail-viewport">
    <div class="vg-detail-container">
        
        {{-- HEADER TACTICAL --}}
        <div class="vg-header-panel">
            <a href="{{ route('user.orders.index') }}" class="btn-back-hud">
                <span class="material-symbols-outlined">chevron_left</span> TRỞ LẠI
            </a>
            
            <div class="header-titles">
                <div class="mission-badge">MÃ: {{ $order->order_code }}</div>
                <h2 class="title-glow">BÁO CÁO CHI TIẾT</h2>
                <div class="mission-time">
                    <span class="material-symbols-outlined icon-sm">radar</span>
                    KHỞI TẠO: {{ $order->ordered_at->format('d/m/Y - H:i:s') }}
                </div>
            </div>
        </div>

        {{-- SUMMARY GRID (3 CỘT GỌN GÀNG) --}}
        <div class="vg-summary-grid">
            
            {{-- CỘT 1: TỌA ĐỘ --}}
            <div class="vg-card hud-card">
                <div class="card-title">
                    <span class="material-symbols-outlined">location_on</span> TỌA ĐỘ GIAO NHẬN
                </div>
                <div class="card-content">
                    <div class="highlight-text">{{ $order->address->full_name }}</div>
                    <div class="sub-text">SĐT: {{ $order->address->phone }}</div>
                    <div class="sub-text mt-2">
                        {{ $order->address->street }}<br>
                        {{ $order->address->district }}, {{ $order->address->city }}, {{ $order->address->province }}
                    </div>
                </div>
            </div>

            {{-- CỘT 2: TÀI CHÍNH --}}
            <div class="vg-card hud-card">
                <div class="card-title">
                    <span class="material-symbols-outlined">account_balance_wallet</span> QUYẾT TOÁN TÀI CHÍNH
                </div>
                <div class="card-content finance-content">
                    <div class="fin-row">
                        <span>Giá trị thiết bị</span>
                        <span>{{ number_format($order->subtotal, 0, ',', '.') }}₫</span>
                    </div>
                    <div class="fin-row">
                        <span>Phí vận chuyển</span>
                        <span class="text-neon-green">FREE TIER</span>
                    </div>
                    <div class="fin-divider"></div>
                    <div class="fin-row total">
                        <span>TỔNG CỘNG</span>
                        <span class="text-neon-cyan">{{ number_format($order->total, 0, ',', '.') }}₫</span>
                    </div>
                </div>
            </div>

            {{-- CỘT 3: TRẠNG THÁI & HÀNH ĐỘNG --}}
            <div class="vg-card hud-card status-action-card">
                <div class="card-title">
                    <span class="material-symbols-outlined">track_changes</span> TRẠNG THÁI
                </div>
                <div class="card-content text-center">
                    
                    @if($order->status == 'pending') 
                        <div class="status-badge-lg warning"><div class="dot"></div> CHỜ DUYỆT</div>
                    @elseif($order->status == 'shipping') 
                        <div class="status-badge-lg info"><div class="dot"></div> ĐANG GIAO</div>
                    @elseif($order->status == 'delivered') 
                        @if(isset($hasRefundRequest) && $hasRefundRequest)
                            <div class="status-badge-lg warning"><div class="dot"></div> CHỜ DUYỆT HOÀN TRẢ</div>
                        @else
                            <div class="status-badge-lg success"><div class="dot"></div> ĐÃ HOÀN THÀNH</div>
                        @endif
                    @elseif($order->status == 'refunded') 
                        <div class="status-badge-lg purple"><div class="dot"></div> ĐÃ HOÀN TIỀN</div>
                    @else 
                        <div class="status-badge-lg danger"><div class="dot"></div> ĐÃ HỦY</div>
                    @endif

                    {{-- ACTIONS --}}
                    <div class="action-zone">
                        @if($order->status == 'delivered' && !$hasRefundRequest)
                            <p class="action-desc">Sự cố thiết bị? Báo cáo trong 24h.</p>
                            <a href="{{ route('user.orders.refund', $order->id) }}" class="btn-vg-danger">
                                <span class="material-symbols-outlined">warning</span> KHỞI TẠO HOÀN TRẢ
                            </a>
                        @elseif($hasRefundRequest || $order->status == 'refunded')
                            <div class="system-msg">
                                <span class="material-symbols-outlined">verified_user</span>
                                <span>{{ $order->status == 'refunded' ? 'YÊU CẦU ĐÃ HOÀN TẤT' : 'ĐANG THẨM ĐỊNH HOÀN TRẢ' }}</span>
                            </div>
                        @else
                            <p class="action-desc" style="opacity: 0.5;">Không có hành động khả dụng.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- FLEET LIST (DANH SÁCH THIẾT BỊ DÀI BÊN DƯỚI) --}}
        <div class="vg-fleet-section">
            <h3 class="fleet-title">
                <span class="material-symbols-outlined">flight_takeoff</span>
                Ảnh sản phẩm ({{ $order->orderItems->count() }})
            </h3>
            
            <div class="fleet-list">
                @foreach($order->orderItems as $item)
                    @php
                        // Logic lấy ảnh chuẩn đã fix
                        $imagePath = \Illuminate\Support\Facades\DB::table('product_images')
                            ->where('product_id', $item->product_id)
                            ->orderBy('position', 'asc')
                            ->value('image_url');
                        
                        $imageUrl = $imagePath ? asset($imagePath) : asset('images/default-uav.jpg');
                    @endphp
                    
                    <div class="fleet-item">
                        <div class="fleet-img-box">
                            <img src="{{ $imageUrl }}" alt="{{ $item->product->name ?? 'UAV' }}">
                        </div>
                        <div class="fleet-info">
                            <div class="fleet-name">{{ $item->product->name ?? 'Unknown UAV' }}</div>
                            <div class="fleet-qty">SỐ LƯỢNG: x{{ $item->quantity }}</div>
                        </div>
                        <div class="fleet-price">
                            {{ number_format($item->unit_price, 0, ',', '.') }}₫
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection