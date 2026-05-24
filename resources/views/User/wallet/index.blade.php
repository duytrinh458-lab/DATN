@extends('User.layouts.app')

@section('title', 'Ví V-Pay Của Tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/wallet.css') }}">
@endpush

@section('content')
<div class="vpay-viewport">
    <div class="vpay-header">
        <h1 class="vpay-title">V-PAY HUD</h1>
        <div class="vpay-status">
            <span class="status-dot"></span> KẾT NỐI BẢO MẬT
        </div>
    </div>

    @if(session('success'))
        <div class="alert-custom alert-success-tech mb-4">
            ✔️ [HỆ THỐNG]: {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert-custom alert-error-tech mb-4">
            ⚠️ [LỖI]: {{ session('error') }}
        </div>
    @endif

    <div class="vpay-grid">
        {{-- CỘT TRÁI: BẢNG ĐIỀU KHIỂN --}}
        <div class="vpay-controls">
            
            {{-- THẺ SỐ DƯ --}}
            <div class="vpay-card balance-card">
                <div class="corner tl"></div><div class="corner br"></div>
                <span class="module-label">SỐ DƯ KHẢ DỤNG</span>
                <div class="amount-display">
                    <span class="amount">{{ number_format($balance ?? 0, 0, ',', '.') }}</span>
                    <span class="currency">VNĐ</span>
                </div>
                <div class="wallet-id">ID: VP-{{ Auth::id() + 1000 }} | TRẠNG THÁI: HOẠT ĐỘNG</div>
            </div>

            {{-- TRUNG TÂM GIAO DỊCH (NẠP / RÚT) --}}
            <div class="vpay-card action-card">
                <div class="corner tr"></div><div class="corner bl"></div>
                
                {{-- Nút chuyển Tab --}}
                <div class="action-tabs">
                    <button class="tab-btn active" onclick="openTab('deposit')">NẠP TIỀN</button>
                    <button class="tab-btn" onclick="openTab('withdraw')">RÚT TIỀN</button>
                </div>

                {{-- Tab 1: Form Nạp Tiền --}}
                <div id="deposit-tab" class="tab-content active">
                    <form action="{{ route('user.wallet.deposit') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <label>SỐ TIỀN MUỐN NẠP (VNĐ)</label>
                            <input type="number" name="amount" id="dep-amount" class="tech-input" placeholder="Tối thiểu 10.000đ" required>
                        </div>
                        
                        <div id="js-alert-box" class="alert-box-container"></div>
                        
                        <div class="qr-mockup hide" id="qr-box">
                            <p class="qr-title">QUÉT MÃ ĐỂ CHUYỂN KHOẢN</p>
                            <img src="{{ asset('images/qr-demo.png') }}?v={{ time() }}" alt="QR Code" class="qr-img">
                            <p class="qr-desc">
                                Nội dung CK: <strong class="qr-highlight">NAP VPAY {{ Auth::id() }}</strong>
                            </p>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn-outline" id="btn-gen-qr" onclick="showQR()">KHỞI TẠO MÃ QR</button>
                            <button type="submit" class="btn-solid hide" id="btn-confirm-dep">XÁC NHẬN ĐÃ CHUYỂN KHOẢN</button>
                        </div>
                    </form>
                </div>

                {{-- Tab 2: Form Rút Tiền --}}
                <div id="withdraw-tab" class="tab-content">
                    <form action="{{ route('user.wallet.withdraw') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <label>SỐ TIỀN CẦN RÚT (VNĐ)</label>
                            <input type="number" name="amount" class="tech-input" placeholder="0" required>
                        </div>
                        <div class="input-group mt-3">
                            <label>THÔNG TIN NHẬN TIỀN</label>
                            <input type="text" name="bank_info" class="tech-input" placeholder="VD: MBBank - 012345678 - Tên Bạn" required>
                        </div>
                        <p class="warning-text">* Yêu cầu sẽ được Admin duyệt và chuyển khoản thủ công trong 24h.</p>
                        <button type="submit" class="btn-solid btn-danger mt-3">GỬI YÊU CẦU RÚT</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: LỊCH SỬ GIAO DỊCH --}}
        <div class="vpay-sidebar">
            <div class="vpay-card history-card">
                <span class="module-label">LỊCH SỬ GIAO DỊCH</span>
                <div class="tx-list">
                    @forelse($transactions ?? [] as $item)
                    <div class="tx-item">
                        <div class="tx-left">
                            <div class="tx-title">
                                @if($item->type == 'deposit') [NẠP TIỀN]
                                @elseif($item->type == 'withdraw') [RÚT TIỀN]
                                @elseif($item->type == 'payment') [THANH TOÁN ĐƠN]
                                @else [HOÀN TIỀN] @endif
                            </div>
                            <div class="tx-date">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i - d/m/Y') }}</div>
                        </div>
                        <div class="tx-right">
                            <div class="tx-amount {{ in_array($item->type, ['payment', 'withdraw']) ? 'text-red' : 'text-cyan' }}">
                                {{ in_array($item->type, ['payment', 'withdraw']) ? '-' : '+' }}{{ number_format($item->amount, 0, ',', '.') }}₫
                            </div>
                            
                            <div class="tx-status-badge {{ $item->status }}">
                                @if($item->status == 'pending')
                                    <span class="text-warning">⏳ Chờ duyệt</span>
                                @elseif($item->status == 'success')
                                    <span class="text-success">✔️ Thành công</span>
                                @else
                                    <span class="text-danger">❌ Thất bại</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center mt-4 text-muted">Chưa có dữ liệu giao dịch</div>
                    @endforelse
                </div>
                
                {{-- PHÂN TRANG CYBERPUNK CHUẨN BOOTSTRAP-4 --}}
                @if(isset($transactions) && $transactions->hasPages())
                <div class="vpay-pagination mt-4">
                    {{ $transactions->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function openTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');
    }

    function showQR() {
        let amt = document.getElementById('dep-amount').value;
        let alertBox = document.getElementById('js-alert-box');
        
        if(amt && amt >= 10000) {
            document.getElementById('qr-box').classList.remove('hide');
            document.getElementById('btn-confirm-dep').classList.remove('hide');
            document.getElementById('btn-gen-qr').classList.add('hide');
            alertBox.innerHTML = '';
        } else {
            alertBox.innerHTML = `
                <div class="alert-custom alert-error-tech">
                    ⚠️ Vui lòng nhập số tiền hợp lệ (Tối thiểu 10.000đ).
                </div>
            `;
        }
    }
</script>
@endsection