@extends('Admin.layouts.admin')

@section('title', 'Thêm người dùng')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/user.css') }}">
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')

<div class="user-detail-page">

    {{-- HEADER --}}
    <div class="admin-header">

        <div class="header-info">

            <h1>Thêm người dùng</h1>

            <p>Tạo tài khoản mới cho hệ thống</p>

        </div>

        <div class="header-actions">

            <a href="{{ route('admin.users.index') }}"
               class="btn-back-text">

                <i class="fas fa-arrow-left"></i>

                Quay lại

            </a>

        </div>

    </div>

    {{-- FORM CARD --}}
    <div class="card shadow-premium action-card">

        <div class="card-header">

            <h3 class="card-title">
                Thông tin tài khoản
            </h3>

        </div>

        <form action="{{ route('admin.users.store') }}"
              method="POST"
              class="uav-form">

            @csrf

            {{-- FULL NAME --}}
            <div class="form-group">

                <label>Họ và tên</label>

                <input type="text"
                       name="full_name"
                       value="{{ old('full_name') }}"
                       required>

            </div>

            {{-- EMAIL --}}
            <div class="form-group">

                <label>Email</label>

                <input type="email"
                       name="email"
                       value="{{ old('email') }}">

            </div>

            {{-- PHONE --}}
            <div class="form-group">

                <label>Số điện thoại</label>

                <input type="text"
                       name="phone"
                       value="{{ old('phone') }}"
                       required>

            </div>

            {{-- PASSWORD --}}
            <div class="form-group">

                <label>Mật khẩu</label>

                <input type="password"
                       name="password"
                       required>

            </div>

            {{-- ROLE --}}
            <div class="form-group">

                <label>Vai trò</label>

                <select name="role">

                    <option value="customer">
                        Người dùng
                    </option>

                    <option value="admin">
                        Quản trị viên
                    </option>

                </select>

            </div>

            {{-- BUTTON --}}
            <div class="form-footer">

                <button type="submit"
                        class="btn-update">

                    <i class="fas fa-save"></i>

                    Tạo tài khoản

                </button>

            </div>

        </form>

    </div>

</div>

@endsection