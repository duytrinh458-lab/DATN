@extends('User.layouts.app')

@section('title', 'Hồ sơ cá nhân - Vanguard')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/profile2.css') }}">
@endpush

@section('content')

<div class="profile-viewport">
    <div class="container">
        <div class="profile-grid">

            {{-- =========================================
                CỘT TRÁI
            ========================================== --}}
            <div class="left-profile-column">

                {{-- THÔNG TIN CÁ NHÂN --}}
                <div class="profile-card mb-4">
                    <h3 class="profile-title-blue">Thông tin cá nhân</h3>

                    @if(session('success'))
                        <div class="alert-vg-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- AVATAR --}}
                        <div class="avatar-box">
                            <label class="form-label-vg">Ảnh đại diện</label>
                            <div class="avatar-wrapper">
                                <img id="avatarPreview"
                                     src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) }}"
                                     class="avatar-img">
                                <input type="file" name="avatar" accept="image/*" class="avatar-input" onchange="previewAvatar(event)">
                            </div>
                        </div>

                        {{-- HỌ TÊN --}}
                        <div class="mb-4">
                            <label class="form-label-vg">Họ và tên</label>
                            <input type="text" name="full_name" class="form-control-vg" value="{{ $user->full_name ?? '' }}" required>
                        </div>

                        {{-- EMAIL --}}
                        <div class="mb-4">
                            <label class="form-label-vg">Email</label>
                            <input type="email" class="form-control-vg" value="{{ $user->email ?? '' }}" disabled>
                        </div>

                        {{-- PHONE --}}
                        <div class="mb-4">
                            <label class="form-label-vg">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control-vg" value="{{ $user->phone ?? '' }}" required>
                        </div>

                        <button type="submit" class="btn-vg-blue">Cập nhật thông tin</button>
                    </form>
                </div>

                {{-- DANH SÁCH ĐỊA CHỈ --}}
                <div class="profile-card">
                    <h3 class="profile-title-blue">Danh sách địa chỉ</h3>

                    @if($addresses->count() > 0)
                        @foreach($addresses as $index => $addr)
                            <div class="address-item {{ $addr->is_default ? 'active' : '' }}">
                                <span class="address-index">#{{ $index + 1 }}</span>
                                @if($addr->is_default)
                                    <span class="badge-active">Mặc định</span>
                                @endif

                                <h5 class="mb-2">{{ $addr->full_name }}</h5>
                                <p class="address-text">{{ $addr->phone }}</p>
                                <p class="address-text">
                                    {{ $addr->street }},
                                    {{ $addr->district }},
                                    {{ $addr->city }},
                                    {{ $addr->province }}
                                </p>

                                <div class="address-time">
                                    <span>
                                        {{ $addr->updated_at ? $addr->updated_at->diffForHumans() : 'Đã tạo trong hệ thống' }}
                                    </span>
                                </div>

                                <div class="address-actions">
                                    @if(!$addr->is_default)
                                        <form action="{{ route('user.profile.address.setDefault', $addr->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-set-default">Đặt mặc định</button>
                                        </form>
                                    @endif

                                    <button type="button" class="btn-edit" onclick="editAddress({{ $addr->id }})">Sửa</button>

                                    <form action="{{ route('user.profile.address.destroy', $addr->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa địa chỉ này?')">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert-vg-success">Chưa có địa chỉ nào được lưu.</div>
                    @endif
                </div>
            </div>

            {{-- =========================================
                CỘT PHẢI
            ========================================== --}}
            <div class="right-profile-column">
                <div class="profile-card">
                    <h3 class="profile-title-green">Địa chỉ giao hàng</h3>

                    <form action="{{ route('user.profile.address.store') }}" method="POST">
                        @csrf

                        {{-- HỌ TÊN --}}
                        <!-- <div class="mb-4">
                            <label class="form-label-vg">Họ và tên người nhận</label>
                            <input type="text" name="full_name" class="form-control-vg" value="{{ old('full_name', $user->full_name ?? '') }}" required>
                        </div> -->

                        {{-- PHONE --}}
                        <!-- <div class="mb-4">
                            <label class="form-label-vg">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control-vg" value="{{ old('phone', $user->phone ?? '') }}" required>
                        </div> -->

                        {{-- PROVINCE --}}
                        <div class="mb-4">
                            <label class="form-label-vg">Tỉnh / Thành phố</label>
                            <input type="text" name="province" class="form-control-vg" value="{{ old('province') }}" required>
                        </div>


                        {{-- DISTRICT --}}
                        <div class="mb-4">
                            <label class="form-label-vg">Quận / Huyện</label>
                            <input type="text" name="district" class="form-control-vg" value="{{ old('district') }}" required>
                        </div>

                        {{--  --}}
                        <div class="mb-4">
                            <label class="form-label-vg">Xã / Phường</label>
                            <input type="text" name="city" class="form-control-vg" value="{{ old('ward') }}" required>
                        </div>

                        {{-- STREET --}}
                        <div class="mb-4">
                            <label class="form-label-vg">Số nhà / Tên đường</label>
                            <textarea name="street" class="form-control-vg" rows="3" required>{{ old('street') }}</textarea>
                        </div>

                        <button type="submit" class="btn-vg-green">Lưu địa chỉ</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function previewAvatar(event) {
    const reader = new FileReader();
    reader.onload = function () {
        document.getElementById('avatarPreview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}

// Nếu muốn bật lại chức năng sửa địa chỉ bằng AJAX thì bỏ comment đoạn dưới
// function editAddress(id) {
//     fetch(`/profile/address/${id}/json`)
//         .then(response => response.json())
//         .then(data => {
//             document.querySelector('input[name="full_name"]').value = data.full_name;
//             document.querySelector('input[name="phone"]').value = data.phone;
//             document.querySelector('input[name="province"]').value = data.province;
//             document.querySelector('input[name="city"]').value = data.city;
//             document.querySelector('input[name="district"]').value = data.district;
//             document.querySelector('textarea[name="street"]').value = data.street;

//             const form = document.querySelector('.right-profile-column form');
//             form.action = `/profile/address/${id}`;

//             let methodInput = form.querySelector('input[name="_method"]');
//             if (!methodInput) {
//                 methodInput = document.createElement('input');
//                 methodInput.type = 'hidden';
//                 methodInput.name = '_method';
//                 form.appendChild(methodInput);
//             }
//             methodInput.value = 'PUT';

//             form.querySelector('button[type="submit"]').innerText = 'Cập nhật địa chỉ';
//         });
// }
</script>
@endpush
