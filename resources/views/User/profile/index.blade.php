@extends('User.layouts.app')

@section('title', 'Hồ sơ | Vanguard')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('Css/User/profile.css') }}">
@endpush

@section('content')
<div class="profile-viewport">
    <div class="container">
        
        <div class="vg-header-panel">
            <h1 class="vg-page-title">HỒ SƠ</h1>
            <div class="vg-status">
                <span class="status-dot"></span> BẢO MẬT CẤP ĐỘ 5
            </div>
        </div>

        <div class="profile-grid">
            {{-- =========================================
                CỘT TRÁI: THÔNG TIN & DANH SÁCH TỌA ĐỘ
            ========================================== --}}
            <div class="left-profile-column">

                {{-- THÔNG TIN CÁ NHÂN --}}
                <div class="vg-card mb-4">
                    <div class="corner tl"></div><div class="corner br"></div>
                    <h3 class="vg-card-title cyan">
                        <span class="material-symbols-outlined">fingerprint</span> Thông tin cá nhân
                    </h3>

                    @if(session('success'))
                        <div class="alert-vg alert-success">
                            ✔️ [HỆ THỐNG]: {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert-vg alert-error">
                            ⚠️ [LỖI]: {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- AVATAR SCANNER --}}
                        <div class="avatar-scanner-box">
                            <label class="form-label-vg">(Avatar)</label>
                            <div class="avatar-wrapper">
                                <div class="avatar-radar">
                                    <img id="avatarPreview"
                                         src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) }}"
                                         class="avatar-img" alt="Avatar">
                                </div>
                                <div class="avatar-upload-zone">
                                    <label for="avatar" class="btn-vg-outline cyan btn-sm">TẢI LÊN ẢNH MỚI</label>
                                    <input type="file" id="avatar" name="avatar" accept="image/*" class="hide" onchange="previewAvatar(event)">
                                    <div class="upload-hint">Định dạng hỗ trợ: JPG, PNG. Tối đa 2MB.</div>
                                </div>
                            </div>
                        </div>

                        {{-- HỌ TÊN --}}
                        <div class="mb-4">
                            <label class="form-label-vg" for="full_name">Họ và Tên</label>
                            <input type="text" id="full_name" name="full_name" class="tech-input" value="{{ $user->full_name ?? '' }}" required>
                        </div>

                        <div class="input-row">
                            {{-- EMAIL --}}
                            <div class="mb-4 flex-1">
                                <label class="form-label-vg" for="email">Email</label>
                                <input type="email" id="email" class="tech-input locked" value="{{ $user->email ?? '' }}" disabled>
                            </div>

                            {{-- PHONE --}}
                            <div class="mb-4 flex-1">
                                <label class="form-label-vg" for="phone"> SỐ Điện thoại</label>
                                <input type="text" id="phone" name="phone" class="tech-input" value="{{ $user->phone ?? '' }}" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-vg-solid cyan mt-2">
                            <span class="material-symbols-outlined">sync</span> ĐỒNG BỘ DỮ LIỆU
                        </button>
                    </form>
                </div>

                {{-- DANH SÁCH ĐỊA CHỈ --}}
                <div class="vg-card">
                    <div class="corner tr"></div><div class="corner bl"></div>
                    <h3 class="vg-card-title cyan">
                        <span class="material-symbols-outlined">share_location</span> Địa Chỉ lưu trữ
                    </h3>

                    @if($addresses->count() > 0)
                        <div class="address-list">
                            @foreach($addresses as $index => $addr)
                                <div class="coord-item {{ $addr->is_default ? 'active' : '' }}">
                                    <div class="coord-header">
                                        <div class="coord-name">
                                            <span class="coord-index">[{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}]</span> 
                                            {{ $addr->full_name }}
                                        </div>
                                        @if($addr->is_default)
                                            <span class="badge-neon-green">ĐỊA CHỈ MẶC ĐỊNH</span>
                                        @endif
                                    </div>

                                    <div class="coord-body">
                                        <div class="coord-detail"><span class="material-symbols-outlined icon-xs">call</span> {{ $addr->phone }}</div>
                                        <div class="coord-detail"><span class="material-symbols-outlined icon-xs">map</span> {{ $addr->street }}, {{ $addr->district }}, {{ $addr->ward }}, {{ $addr->province }}</div>
                                    </div>

                                    <div class="coord-actions">
                                        @if(!$addr->is_default)
                                            <form action="{{ route('user.profile.address.setDefault', $addr->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn-action set-default" title="Đặt làm mặc định">
                                                    <span class="material-symbols-outlined">gps_fixed</span>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn-action edit" onclick="editAddress({{ $addr->id }})" title="Chỉnh sửa tọa độ">
                                            <span class="material-symbols-outlined">edit_location</span>
                                        </button>

                                        <form action="{{ route('user.profile.address.destroy', $addr->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action delete" onclick="return confirm('Hủy bỏ tọa độ này khỏi hệ thống?')" title="Xóa tọa độ">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert-vg alert-neutral">
                            Hệ thống chưa ghi nhận tọa độ nào.
                        </div>
                    @endif
                </div>
            </div>

            {{-- =========================================
                CỘT PHẢI: FORM THÊM/SỬA ĐỊA CHỈ
            ========================================== --}}
            <div class="right-profile-column align-top">
                <div class="vg-card sticky-card">
                    <div class="corner tl"></div><div class="corner br"></div>
                    <h3 class="vg-card-title green">
                        <span class="material-symbols-outlined">add_location_alt</span> Thiết lập Địa Chỉ mới
                    </h3>

                    <form id="addressForm" action="{{ route('user.profile.address.store') }}" method="POST">
                        @csrf
                        
                        <div class="input-row" style="display: flex; gap: 15px;">
                            <div class="mb-4 flex-1">
                                <label class="form-label-vg" for="full_name">Tên người nhận</label>
                                <input type="text" id="full_name" name="full_name" class="tech-input" value="{{ old('full_name', $user->full_name) }}" required>
                            </div>

                            <div class="mb-4 flex-1">
                                <label class="form-label-vg" for="addr_phone">SĐT liên lạc</label>
                                <input type="text" id="addr_phone" name="phone" class="tech-input" value="{{ old('phone', $user->phone) }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-vg" for="province">Tỉnh / Thành phố</label>
                            <input type="text" id="province" name="province" class="tech-input" value="{{ old('province') }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-vg" for="district">Quận / Huyện</label>
                            <input type="text" id="district" name="district" class="tech-input" value="{{ old('district') }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-vg" for="city">Xã / Phường</label>
                            <input type="text" id="city" name="city" class="tech-input" value="{{ old('city') }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-vg" for="street">Số nhà / Tên đường / Tòa nhà</label>
                            <textarea id="street" name="street" class="tech-input" rows="2" required>{{ old('street') }}</textarea>
                        </div>

                        <button type="submit" class="btn-vg-solid green w-100 mt-2" id="submitAddressBtn">
                            <span class="material-symbols-outlined">save</span> LƯU ĐỊA CHỈ
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* PREVIEW AVATAR */
function previewAvatar(event) {
    const reader = new FileReader();
    reader.onload = function () {
        document.getElementById('avatarPreview').src = reader.result;
    };
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

/* EDIT ADDRESS */
function editAddress(id) {
    fetch(`{{ url('profile/address') }}/${id}/json`)
        .then(response => {
            if (!response.ok) throw new Error('Không tìm thấy route JSON');
            return response.json();
        })
        .then(data => {
            const form = document.getElementById('addressForm');
            
            // Đổ dữ liệu người nhận
            document.getElementById('full_name').value = data.full_name ?? '';
            document.getElementById('addr_phone').value = data.phone ?? '';
            
            // Đổ dữ liệu tọa độ
            document.getElementById('province').value = data.province ?? '';
            document.getElementById('district').value = data.district ?? '';
            
            // Đổ dữ liệu Xã/Phường vào ô mang id 'city'
            document.getElementById('city').value = data.city ?? '';
            
            document.getElementById('street').value = data.street ?? '';

            form.action = `{{ url('profile/address') }}/${id}`;

            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'PUT';

            const btn = document.getElementById('submitAddressBtn');
            btn.innerHTML = '<span class="material-symbols-outlined">update</span> CẬP NHẬT TỌA ĐỘ';
            
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            form.closest('.vg-card').style.boxShadow = '0 0 20px rgba(0, 255, 136, 0.4)';
            setTimeout(() => { form.closest('.vg-card').style.boxShadow = '0 10px 30px rgba(0,0,0,0.5)'; }, 1000);
        })
        .catch(error => {
            console.error(error);
            alert('Không thể tải dữ liệu địa chỉ!');
        });
}

</script>
@endpush


