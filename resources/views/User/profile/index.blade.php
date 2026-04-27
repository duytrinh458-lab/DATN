@extends('User.layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="container py-5" style="color: white; margin-top: 50px;">
    <div class="row">
        <div class="col-md-5 mb-4">
            <div style="background: #1e293b; padding: 30px; border-radius: 15px; border: 1px solid #334155; height: 100%;">
                <h3 style="color: #00eaff;" class="mb-4">Thông tin cá nhân</h3>
                
                @if(session('success'))
                    <div class="alert alert-success" style="background: #00ff88; color: #000; border: none; font-weight: bold;">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('user.profile.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted">Họ và tên</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" style="background: #0f172a; color: white; border: 1px solid #334155; padding: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled style="background: #334155; color: #94a3b8; border: none; padding: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" style="background: #0f172a; color: white; border: 1px solid #334155; padding: 12px;">
                    </div>
                    <button type="submit" class="btn mt-3" style="background: linear-gradient(45deg, #00eaff, #007bff); color: white; width: 100%; font-weight: bold; padding: 12px; border: none; border-radius: 8px;">
                        LƯU THAY ĐỔI
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-7 mb-4">
            <div style="background: #1e293b; padding: 30px; border-radius: 15px; border: 1px solid #334155; height: 100%;">
                <h3 style="color: #00ff88;" class="mb-4">Địa chỉ nhận hàng</h3>

                <form action="{{ route('user.profile.address.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted">Nhập địa chỉ giao hàng chi tiết</label>
                        <textarea name="full_address" class="form-control" rows="4" 
                            placeholder="Ví dụ: Số nhà 12, ngõ 34, đường Nguyễn Văn Cừ, Quận Long Biên, Hà Nội" 
                            style="background: #0f172a; color: white; border: 1px solid #334155; padding: 12px;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn" style="background: #00ff88; color: #000; font-weight: bold; width: 100%; border-radius: 8px; padding: 12px;">
                        LƯU ĐỊA CHỈ NÀY
                    </button>
                </form>

                <hr style="background: #334155; margin: 30px 0;">

                <h4 style="color: #00eaff;" class="mb-3">Địa chỉ đang sử dụng:</h4>
                @if($addresses->count() > 0)
                    @foreach($addresses as $addr)
                        <div class="mb-3 p-3" style="background: #0f172a; border-radius: 10px; border-left: 4px solid {{ $user->address_id == $addr->id ? '#00ff88' : '#334155' }};">
                            @if($user->address_id == $addr->id)
                                <span class="badge bg-success mb-2">Đang sử dụng</span>
                            @endif
                            <p class="mb-0 text-white">{{ $addr->street }}</p>
                            
                            {{-- Đã sửa lỗi diffForHumans() ở đây bằng cách kiểm tra null --}}
                            @if($addr->updated_at)
                                <small class="text-muted">Cập nhật lúc: {{ $addr->updated_at->diffForHumans() }}</small>
                            @else
                                <small class="text-muted">Đã lưu thành công</small>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-dark text-muted" style="background: #0f172a; border: 1px dashed #334155;">
                        Bạn chưa lưu địa chỉ nào. Hãy nhập địa chỉ ở trên để có thể đặt hàng.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection