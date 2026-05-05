@extends('User.layouts.app') 

@section('title', 'Giỏ Hàng Chiến Thuật - Vanguard UAV')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/User/cart.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endpush

@section('content')
<div class="vanguard-cart-page">
    <div class="cart-header">
        <h2>Giỏ hàng chiến lược</h2>
        <div style="color: var(--outline); font-size: 10px; text-transform: uppercase; letter-spacing: 4px;">Tactical Resource Management</div>
    </div>

    <div class="cart-flex-wrapper">
        <div class="cart-items-list">
            @if(isset($cartItems) && $cartItems->count() > 0)
                
                <div class="select-all-bar">
                    <input type="checkbox" id="check-all" class="vg-checkbox" onchange="toggleAll(this)">
                    <label for="check-all" style="cursor: pointer; margin: 0; letter-spacing: 1px; text-transform: uppercase;">Chọn toàn bộ đội hình</label>
                </div>

                @foreach($cartItems as $item)
                    <div class="cart-item-module">
                        <div class="vg-checkbox-wrapper">
                            <input type="checkbox" class="vg-checkbox item-checkbox" 
                                value="{{ $item->id }}" 
                                data-price="{{ $item->unit_price }}" 
                                data-qty="{{ $item->quantity }}"
                                onchange="calculateTotal()">
                        </div>

                        <div class="item-img-box">
                            <img src="{{ asset($item->product->images->first()->image_url ?? 'default.jpg') }}" alt="{{ $item->product->name }}">
                        </div>
                        
                        <div class="item-info">
                            <div class="item-sku">UAV-{{ $item->product->sku }}</div>
                            <div class="item-name">{{ $item->product->name }}</div>
                        </div>

                        <!-- BẢNG ĐIỀU KHIỂN SỐ LƯỢNG MỚI (CÓ NÚT + / -) -->
                        <div class="quantity-hud">
                            <form action="{{ route('user.cart.update', $item->id) }}" method="POST" style="display:flex; align-items:center; width: 100%; margin: 0;">
                                @csrf
                                @method('PUT')
                                <button type="button" class="qty-btn" onclick="updateQty(this, -1)">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">remove</span>
                                </button>
                                
                                <input type="number" name="quantity" class="qty-input" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}" onchange="this.form.submit()">
                                
                                <button type="button" class="qty-btn" onclick="updateQty(this, 1)">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                                </button>
                            </form>
                        </div>

                        <div style="text-align: right; min-width: 120px;">
                            <div style="font-size: 10px; color: var(--outline); text-transform: uppercase;">Giá đơn vị</div>
                            <div style="font-weight: 900; color: var(--primary);">{{ number_format($item->unit_price, 0, ',', '.') }}₫</div>
                        </div>

                        <form id="delete-form-{{ $item->id }}" action="{{ route('user.cart.destroy', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="openDeleteModal({{ $item->id }})" style="background:transparent; border:none; color:var(--outline); cursor:pointer;">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            @else
                <div style="padding: 100px; text-align: center; background: var(--surface-container-low); border-radius: 40px;">
                    <p style="color: var(--outline); text-transform: uppercase; letter-spacing: 2px;">Kho hàng của đặc vụ đang trống</p>
                    <a href="{{ route('user.products') }}" style="color: var(--primary); text-decoration: underline; margin-top: 20px; display: inline-block;">Quay lại kho vũ khí</a>
                </div>
            @endif
        </div>

        <aside class="cart-summary-vanguard">
            <div class="summary-card-inner">
                <div class="summary-title">Ngân sách huy động</div>
                
                <div class="summary-row">
                    <span>ĐÃ CHỌN (<span id="selected-count">0</span>)</span>
                    <span id="temp-total">0₫</span>
                </div>
                
                <div class="summary-row">
                    <span>VẬN CHUYỂN</span>
                    <span>FREE</span>
                </div>

                <div class="total-row">
                    <div style="font-size: 10px; font-weight: 900; opacity: 0.6;">TỔNG CỘNG</div>
                    <div class="total-price-display" id="final-total">0₫</div>
                </div>

                <button type="button" class="btn-checkout-vanguard" onclick="proceedToCheckout()">
                    Tiến hành thanh toán
                </button>
                
                <a href="{{ route('user.products') }}" style="display: block; text-align: center; margin-top: 20px; font-size: 10px; font-weight: 900; text-decoration: none; color: inherit; opacity: 0.5;">
                    ← TIẾP TỤC ĐIỀU ĐỘNG THIẾT BỊ
                </a>
            </div>
        </aside>
    </div>
</div>

<div id="vg-delete-modal" class="vg-modal-overlay">
    <div class="vg-modal-box">
        <span class="material-symbols-outlined vg-modal-icon">warning</span>
        <div class="vg-modal-title">Cảnh báo hệ thống</div>
        <div class="vg-modal-text">Xác nhận loại bỏ thiết bị UAV này khỏi đội hình chiến thuật của bạn?</div>
        <div class="vg-modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">HỦY BỎ</button>
            <button type="button" class="btn-modal-confirm" onclick="executeDelete()">XÁC NHẬN</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentDeleteFormId = null;
    function openDeleteModal(itemId) {
        currentDeleteFormId = 'delete-form-' + itemId;
        document.getElementById('vg-delete-modal').classList.add('active');
    }
    function closeDeleteModal() {
        document.getElementById('vg-delete-modal').classList.remove('active');
        currentDeleteFormId = null;
    }
    function executeDelete() {
        if (currentDeleteFormId) document.getElementById(currentDeleteFormId).submit();
    }

    // XỬ LÝ NÚT TĂNG/GIẢM SỐ LƯỢNG MỚI
    function updateQty(btn, change) {
        const form = btn.closest('form');
        const input = form.querySelector('.qty-input');
        let newVal = parseInt(input.value) + change;
        let max = parseInt(input.getAttribute('max'));
        let min = parseInt(input.getAttribute('min'));
        
        if (newVal >= min && newVal <= max) {
            input.value = newVal;
            form.submit(); // Tự động submit lên database
        } else if (newVal > max) {
            showFrontendToast('Không đủ lượng UAV trong kho để cung cấp!');
        }
    }

    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => { cb.checked = source.checked; });
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0; let count = 0;
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        
        checkboxes.forEach(cb => {
            total += parseFloat(cb.getAttribute('data-price')) * parseInt(cb.getAttribute('data-qty'));
            count++;
        });

        const allCheckboxes = document.querySelectorAll('.item-checkbox');
        const checkAllBtn = document.getElementById('check-all');
        if(checkAllBtn) {
            checkAllBtn.checked = (checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0);
        }

        const formattedTotal = new Intl.NumberFormat('vi-VN').format(total) + '₫';
        document.getElementById('selected-count').innerText = count;
        document.getElementById('temp-total').innerText = formattedTotal;
        document.getElementById('final-total').innerText = formattedTotal;
    }

    function proceedToCheckout() {
        const selected = document.querySelectorAll('.item-checkbox:checked');
        if(selected.length === 0) {
            showFrontendToast('Vui lòng chọn ít nhất 1 thiết bị UAV để tiến hành thanh toán!');
            return;
        }

        let url = "{{ route('user.checkout.index') }}?";
        let params = new URLSearchParams();
        selected.forEach(cb => { params.append('items[]', cb.value); });
        
        window.location.href = url + params.toString();
    }

    function showFrontendToast(message) {
        let toast = document.getElementById('vanguard-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'vanguard-toast';
            document.body.appendChild(toast);
        }
        toast.className = 'toast-error show';
        toast.innerHTML = `<span class="material-symbols-outlined">warning</span><span class="toast-msg">${message}</span>`;
        setTimeout(() => { toast.classList.remove('show'); }, 5000);
    }
</script>
@endpush