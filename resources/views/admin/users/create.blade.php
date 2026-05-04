@extends('Admin.layouts.admin')

@section('title', 'Thêm người dùng mới - Vanguard UAV')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/Admin/create.css') }}">
@endpush

@section('content')
<div class="user-form-page">
    <header class="admin-header">
        <div class="header-info">
            <h1>Thêm thành viên mới</h1>
            <p>Khởi tạo tài khoản cho nhân viên hoặc khách hàng</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </header>

    @if ($errors->any())
        <div class="alert error-alert">
            <div class="alert-title"><i class="fas fa-exclamation-triangle"></i> Đã có lỗi xảy ra:</div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-premium">
        <div class="card-header">
            <h2 class="card-title">Thông tin tài khoản</h2>
        </div>
        
        <form method="POST" action="{{ route('admin.users.store') }}" class="uav-form">
            @csrf
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" placeholder="Ví dụ: uav_admin" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Họ và Tên</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Nhập tên đầy đủ">
                </div>

                <div class="form-group">
                    <label for="email">Email liên hệ</label>
                    <input type="email" id="email" name="email" placeholder="email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" placeholder="Nhập số điện thoại" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Tối thiểu 8 ký tự" required>
                </div>

                <div class="form-group">
                    <label for="role">Vai trò hệ thống</label>
                    <select name="role" id="role" required>
                        <option value="customer">Người dùng (User)</option>
                        <option value="admin">Quản trị viên (Admin)</option>
                    </select>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-save"></i> Xác nhận thêm người dùng
                </button>
            </div>
        </form>
    </div>
</div>
@endsection