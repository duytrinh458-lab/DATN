<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu — Vanguard</title>
    <link rel="stylesheet" href="{{ asset('Css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper">

    {{-- CỘT TRÁI --}}
    <div class="auth-brand">
        <div>
            <div class="brand-logo">Vanguard</div>
            <div class="brand-tagline">Unmanned Aerial Vehicle Platform</div>
        </div>

        <div class="brand-center">
            <h1 class="brand-title">Thiết lập<br><em>mật khẩu</em></h1>
            <p class="brand-desc">
                Đây là lần đăng nhập đầu tiên của bạn.
                Vui lòng đặt mật khẩu riêng để bảo mật tài khoản.
            </p>
        </div>

        <div class="brand-footer">© 2026 Vanguard. All rights reserved.</div>
    </div>

    {{-- CỘT PHẢI --}}
    <div class="auth-form-panel">
        <div class="auth-box">

            <h2>Đổi mật khẩu lần đầu</h2>
            <p class="subtitle">Bắt buộc để kích hoạt tài khoản</p>

            {{-- ALERTS --}}
            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('password.change.update') }}">
                @csrf

                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="password"
                           placeholder="Tối thiểu 6 ký tự" required>
                </div>

                <div class="form-group">
                    <label>Nhập lại mật khẩu</label>
                    <input type="password" name="password_confirmation"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-primary">Xác nhận đổi mật khẩu</button>
            </form>

            <div class="auth-links">
                <a href="{{ url('/forgot') }}">Quên mật khẩu?</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>