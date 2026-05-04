@extends('User.layouts.app')

@section('title', 'Hồ sơ cá nhân - Vanguard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user/profile.css') }}">
@endpush

@section('content')
<div class="profile-viewport">
    <div class="container">
        <div class="row">
            
            <!-- CỘT TRÁI: THÔNG TIN CÁ NHÂN -->
            <div class="col-md-5 mb-4">
                <div class="profile-card">
                    <h3 class="profile-title-blue">Thông tin cá nhân</h3>
                    
                    @if(session('success'))
                        <div class="alert-vg-success">
                            [SYS_MSG] {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('user.profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label-vg">Họ và tên Đặc vụ</label>
                            <input type="text" name="full_name" class="form-control-vg" value="{{ $user->full_name }}" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label-vg">Định danh Email</label>
                            <input type="email" class="form-control-vg" value="{{ $user->email }}" disabled>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label-vg">Kênh liên lạc (SĐT)</label>
                            <input type="text" name="phone" class="form-control-vg" value="{{ $user->phone }}" required>
                        </div>
                        
                        <button type="submit" class="btn-vg-blue">
                            ĐỒNG BỘ DỮ LIỆU
                        </button>
                    </form>
                </div>
            </div>

            <!-- CỘT PHẢI: QUẢN LÝ TỌA ĐỘ GIAO HÀNG -->
            <div class="col-md-7 mb-4">
                <div class="profile-card">
                    <h3 class="profile-title-green">Tọa độ nhận hàng</h3>

                    <form action="{{ route('user.profile.address.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label-vg">Nhập tọa độ giao hàng chi tiết</label>
                            <textarea name="full_address" class="form-control-vg" rows="3" 
                                placeholder="Ví dụ: Số nhà 12, ngõ 34, đường Nguyễn Văn Cừ, Quận Long Biên, Hà Nội" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-vg-green">
                            LƯU TỌA ĐỘ MỚI
                        </button>
                    </form>

                    <hr style="border-color: #334155; margin: 40px 0;">

                    <h4 class="profile-title-blue" style="font-size: 1.2rem;">Lịch sử Tọa độ:</h4>
                    
                    @if($addresses->count() > 0)
                        @foreach($addresses as $addr)
                            <div class="address-item {{ $addr->is_default == 1 ? 'active' : '' }}">
                                @if($addr->is_default == 1)
                                    <span class="badge-active">Đang sử dụng</span>
                                @endif
                                
                                <p class="address-text">{{ $addr->street }}</p>
                                
                                <div class="address-time" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>
                                        @if($addr->updated_at)
                                            Cập nhật lúc: {{ $addr->updated_at->diffForHumans() }}
                                        @else
                                            Đã ghi nhận vào hệ thống
                                        @endif
                                    </span>

                                    <!-- NÚT ĐẶT LÀM MẶC ĐỊNH ĐÃ ĐƯỢC CHÈN SẴN Ở ĐÂY -->
                                    @if($addr->is_default == 0)
                                        <form action="{{ route('user.profile.address.setDefault', $addr->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" style="background: transparent; border: 1px solid #00eaff; color: #00eaff; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; text-transform: uppercase; transition: 0.3s;">
                                                Đặt mặc định
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert-vg-success" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border-color: #334155;">
                            Chưa có dữ liệu Tọa độ. Vui lòng thiết lập để thực hiện giao dịch.
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection