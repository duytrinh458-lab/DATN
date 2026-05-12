@extends('User.layouts.app')

@section('title', 'Hồ sơ cá nhân - Vanguard')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/profile2.css') }}">
@endpush

@section('content')
<div class="profile-viewport">
    <div class="container">
        <div class="row">

            <!-- CỘT TRÁI -->
            <div class="col-md-5 mb-4">
                <div class="profile-card">
                    <h3 class="profile-title-blue">Thông tin cá nhân</h3>

                    @if(session('success'))
                        <div class="alert-vg-success">
                            [SYS_MSG] {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('user.profile.update') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <!-- AVATAR -->
                        <div class="avatar-box">
                            <label class="form-label-vg">Ảnh đại diện</label>

                            <div class="avatar-wrapper">

                                <img id="avatarPreview"
                                     src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) }}"
                                     class="avatar-img">

                                <input type="file"
                                       name="avatar"
                                       accept="image/*"
                                       class="avatar-input"
                                       onchange="previewAvatar(event)">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-vg">Họ và tên</label>
                            <input type="text" name="full_name"
                                   class="form-control-vg"
                                   value="{{ $user->full_name ?? '' }}"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-vg">Email</label>
                            <input type="email"
                                   class="form-control-vg"
                                   value="{{ $user->email ?? '' }}"
                                   disabled>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-vg">Số điện thoại</label>
                            <input type="text" name="phone"
                                   class="form-control-vg"
                                   value="{{ $user->phone ?? '' }}"
                                   required>
                        </div>

                        <button type="submit" class="btn-vg-blue">
                            CẬP NHẬT THÔNG TIN
                        </button>
                    </form>
                </div>
            </div>

            <!-- CỘT PHẢI -->
            <div class="col-md-7 mb-4">
                <div class="profile-card">

                    <h3 class="profile-title-green">Địa chỉ giao hàng</h3>

                    <form action="{{ route('user.profile.address.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label-vg">Địa chỉ đầy đủ</label>
                            <textarea name="full_address"
                                      class="form-control-vg"
                                      rows="3"
                                      placeholder="Nhập địa chỉ..."
                                      required></textarea>
                        </div>

                        <button type="submit" class="btn-vg-green">
                            LƯU ĐỊA CHỈ
                        </button>
                    </form>

                    <hr style="border-color:#334155; margin:30px 0;">

                    <h4 class="profile-title-blue" style="font-size:1.2rem;">
                        Danh sách địa chỉ
                    </h4>

                    @if($addresses->count() > 0)

                        @foreach($addresses as $addr)
                            <div class="address-item {{ $addr->is_default ? 'active' : '' }}">

                                @if($addr->is_default)
                                    <span class="badge-active">Mặc định</span>
                                @endif

                                <p class="address-text">
                                    {{ $addr->full_address
                                        ?? $addr->street
                                        ?? $addr->address
                                        ?? 'Không có dữ liệu' }}
                                </p>

                                <div class="address-time">

                                    <span>
                                        {{ $addr->updated_at
                                            ? $addr->updated_at->diffForHumans()
                                            : 'Đã tạo trong hệ thống' }}
                                    </span>

                                    @if(!$addr->is_default)
                                        <form action="{{ route('user.profile.address.setDefault', $addr->id) }}"
                                              method="POST">
                                            @csrf

                                            <button type="submit" class="btn-set-default">
                                                Đặt mặc định
                                            </button>
                                        </form>
                                    @endif

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
</script>
@endpush