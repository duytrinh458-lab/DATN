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

                    <h3 class="profile-title-blue">
                        Thông tin cá nhân
                    </h3>

                    @if(session('success'))
                        <div class="alert-vg-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert-vg-error">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('user.profile.update') }}"
                          method="POST"
                          enctype="multipart/form-data">

                        @csrf

                        {{-- AVATAR --}}
                        <div class="avatar-box">

                            <label class="form-label-vg" for="avatar">
                                Ảnh đại diện
                            </label>

                            <div class="avatar-wrapper">

                                <img id="avatarPreview"
                                     src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) }}"
                                     class="avatar-img">

                                <input type="file"
                                       id="avatar"
                                       name="avatar"
                                       accept="image/*"
                                       class="avatar-input"
                                       onchange="previewAvatar(event)">
                            </div>
                        </div>

                        {{-- HỌ TÊN --}}
                        <div class="mb-4">

                            <label class="form-label-vg" for="full_name">
                                Họ và tên
                            </label>

                            <input type="text"
                                   id="full_name"
                                   name="full_name"
                                   class="form-control-vg"
                                   value="{{ $user->full_name ?? '' }}"
                                   required>
                        </div>

                        {{-- EMAIL --}}
                        <div class="mb-4">

                            <label class="form-label-vg" for="email">
                                Email
                            </label>

                            <input type="email"
                                   id="email"
                                   class="form-control-vg"
                                   value="{{ $user->email ?? '' }}"
                                   disabled>
                        </div>

                        {{-- PHONE --}}
                        <div class="mb-4">

                            <label class="form-label-vg" for="phone">
                                Số điện thoại
                            </label>

                            <input type="text"
                                   id="phone"
                                   name="phone"
                                   class="form-control-vg"
                                   value="{{ $user->phone ?? '' }}"
                                   required>
                        </div>

                        <button type="submit"
                                class="btn-vg-blue">

                            Cập nhật thông tin

                        </button>

                    </form>
                </div>

                {{-- DANH SÁCH ĐỊA CHỈ --}}
                <div class="profile-card">

                    <h3 class="profile-title-blue">
                        Danh sách địa chỉ
                    </h3>

                    @if($addresses->count() > 0)

                        @foreach($addresses as $index => $addr)

                            <div class="address-item {{ $addr->is_default ? 'active' : '' }}">

                                <span class="address-index">
                                    #{{ $index + 1 }}
                                </span>

                                @if($addr->is_default)
                                    <span class="badge-active">
                                        Mặc định
                                    </span>
                                @endif

                                <h5 class="mb-2">
                                    {{ $addr->full_name }}
                                </h5>

                                <p class="address-text">
                                    {{ $addr->phone }}
                                </p>

                                <p class="address-text">
                                    {{ $addr->street }},
                                    {{ $addr->district }},
                                    {{ $addr->ward }},
                                    {{ $addr->province }}
                                </p>

                                <div class="address-time">
                                    <span>
                                        Địa chỉ giao hàng
                                    </span>
                                </div>

                                <div class="address-actions">

                                    @if(!$addr->is_default)

                                        <form action="{{ route('user.profile.address.setDefault', $addr->id) }}"
                                              method="POST"
                                              style="display:inline;">

                                            @csrf

                                            <button type="submit"
                                                    class="btn-set-default">

                                                Đặt mặc định

                                            </button>

                                        </form>

                                    @endif

                                    {{-- BUTTON EDIT --}}
                                    <button type="button"
                                            class="btn-edit"
                                            onclick="editAddress({{ $addr->id }})">

                                        Sửa

                                    </button>

                                    {{-- DELETE --}}
                                    <form action="{{ route('user.profile.address.destroy', $addr->id) }}"
                                          method="POST"
                                          style="display:inline;">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn-delete"
                                                onclick="return confirm('Bạn có chắc muốn xóa địa chỉ này?')">

                                            Xóa

                                        </button>

                                    </form>

                                </div>
                            </div>

                        @endforeach

                    @else

                        <div class="alert-vg-success">
                            Chưa có địa chỉ nào được lưu.
                        </div>

                    @endif

                </div>
            </div>

            {{-- =========================================
                CỘT PHẢI
            ========================================== --}}
            <div class="right-profile-column">

                <div class="profile-card">

                    <h3 class="profile-title-green">
                        Địa chỉ giao hàng
                    </h3>

                    <form id="addressForm"
                          action="{{ route('user.profile.address.store') }}"
                          method="POST">

                        @csrf

                        {{-- PROVINCE --}}
                        <div class="mb-4">

                            <label class="form-label-vg" for="province">
                                Tỉnh / Thành phố
                            </label>

                            <input type="text"
                                   id="province"
                                   name="province"
                                   class="form-control-vg"
                                   value="{{ old('province') }}"
                                   required>
                        </div>

                        {{-- DISTRICT --}}
                        <div class="mb-4">

                            <label class="form-label-vg" for="district">
                                Quận / Huyện
                            </label>

                            <input type="text"
                                   id="district"
                                   name="district"
                                   class="form-control-vg"
                                   value="{{ old('district') }}"
                                   required>
                        </div>

                        {{-- WARD --}}
                        <div class="mb-4">

                            <label class="form-label-vg" for="ward">
                                Xã / Phường
                            </label>

                            <input type="text"
                                   id="ward"
                                   name="ward"
                                   class="form-control-vg"
                                   value="{{ old('ward') }}"
                                   required>
                        </div>

                        {{-- STREET --}}
                        <div class="mb-4">

                            <label class="form-label-vg" for="street">
                                Số nhà / Tên đường
                            </label>

                            <textarea id="street"
                                      name="street"
                                      class="form-control-vg"
                                      rows="3"
                                      required>{{ old('street') }}</textarea>
                        </div>

                        <button type="submit"
                                class="btn-vg-green"
                                id="submitAddressBtn">

                            Lưu địa chỉ

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

/*
|--------------------------------------------------------------------------
| PREVIEW AVATAR
|--------------------------------------------------------------------------
*/
function previewAvatar(event) {

    const reader = new FileReader();

    reader.onload = function () {

        document.getElementById('avatarPreview').src = reader.result;
    };

    reader.readAsDataURL(event.target.files[0]);
}

/*
|--------------------------------------------------------------------------
| EDIT ADDRESS
|--------------------------------------------------------------------------
*/
function editAddress(id) {

    console.log('Editing address ID:', id);

    fetch(`{{ url('profile/address') }}/${id}/json`)

        .then(response => {

            if (!response.ok) {
                throw new Error('Không tìm thấy route JSON');
            }

            return response.json();
        })

        .then(data => {

            console.log(data);

            const form = document.getElementById('addressForm');

            document.getElementById('province').value = data.province ?? '';
            document.getElementById('district').value = data.district ?? '';
            document.getElementById('ward').value = data.ward ?? '';
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

            document.getElementById('submitAddressBtn').innerText = 'Cập nhật địa chỉ';

            form.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        })

        .catch(error => {

            console.error(error);

            alert('Không thể tải dữ liệu địa chỉ!');
        });
}

</script>

@endpush