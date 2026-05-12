@extends('Admin.layouts.admin')

@section('title', 'Thêm người dùng mới - Vanguard UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/adduser.css') }}">
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
                ← Quay lại
            </a>
        </div>
    </header>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert error-alert">
            <div class="alert-title">
                ⚠ Đã có lỗi xảy ra:
            </div>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">

        <div class="card-header">
            <h2 class="card-title">Thông tin tài khoản</h2>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="uav-form">
            @csrf

            <div class="form-grid">

                {{-- ❗ KHÔNG CẦN username (controller tự tạo) --}}
                
                <div class="form-group">
                    <label>Họ và Tên</label>
                    <input type="text" name="full_name">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" required>
                </div>

                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Vai trò</label>
                    <select name="role" required>
                        <option value="customer">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

            </div>

            <div class="form-footer">
                <button type="submit" class="btn-submit">
                    💾 Thêm người dùng
                </button>
            </div>

        </form>

    </div>

</div>

@endsection