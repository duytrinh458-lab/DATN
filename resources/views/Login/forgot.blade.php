<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu — UAV Store</title>

    <link rel="stylesheet" href="{{ asset('Css/auth.css') }}">
</head>
<body>

@include('partials.alert')

<div class="auth-wrapper">

    {{-- CỘT TRÁI --}}
    <div class="auth-brand">

        <div>
            <div class="brand-logo">
                UAV<span>Store</span>
            </div>

            <div class="brand-tagline">
                Unmanned Aerial Vehicle Platform
            </div>
        </div>

        <div class="brand-center">

            <h1 class="brand-title">
                Khôi phục<br>
                <em>tài khoản</em>
            </h1>

            <p class="brand-desc">
                Nhập số điện thoại để nhận mã OTP
                và đặt lại mật khẩu mới.
            </p>

        </div>

        <div class="brand-footer">
            © 2026 UAV Store. All rights reserved.
        </div>

    </div>

    {{-- CỘT PHẢI --}}
    <div class="auth-form-panel">

        <div class="auth-box">

            <h2>Quên mật khẩu?</h2>

            @if(!session('forgot_phone'))

                <p class="subtitle">
                    Khôi phục tài khoản qua số điện thoại
                </p>

                {{-- STEP --}}
                <div class="step-indicator">

                    <div class="step active">
                        <span class="step-num">1</span>
                        <span>Xác thực SĐT</span>
                    </div>

                    <div class="step-divider"></div>

                    <div class="step">
                        <span class="step-num">2</span>
                        <span>OTP + Mật khẩu mới</span>
                    </div>

                </div>

                {{-- BƯỚC 1 --}}
                <form method="POST" action="{{ url('/forgot-password/send-otp') }}">

                    @csrf

                    <div class="form-group">

                        <label>Số điện thoại</label>

                        <input type="text"
                               name="phone"
                               value="{{ old('phone') }}"
                               placeholder="09xxxxxxxx"
                               required>

                    </div>

                    <button type="submit" class="btn-secondary">
                        Gửi mã OTP →
                    </button>

                </form>

            @else

                <p class="subtitle">
                    Nhập OTP và mật khẩu mới
                </p>

                {{-- STEP --}}
                <div class="step-indicator">

                    <div class="step active">
                        <span class="step-num">✓</span>
                        <span>{{ session('forgot_phone') }}</span>
                    </div>

                    <div class="step-divider"></div>

                    <div class="step active">
                        <span class="step-num">2</span>
                        <span>Đặt lại mật khẩu</span>
                    </div>

                </div>

                <div class="divider">
                    Xác thực OTP
                </div>

                {{-- BƯỚC 2 --}}
                <form method="POST" action="{{ url('/forgot-password/verify-otp') }}">

                    @csrf

                    <input type="hidden"
                           name="phone"
                           value="{{ session('forgot_phone') }}">

                    <div class="form-group">

                        <label>Mã OTP (6 số)</label>

                        <input type="text"
                               name="otp_code"
                               placeholder="• • • • • •"
                               maxlength="6"
                               required>

                    </div>

                    <div class="form-group">

                        <label>Mật khẩu mới</label>

                        <input type="password"
                               name="new_password"
                               placeholder="Tối thiểu 6 ký tự"
                               required>

                    </div>

                    <button type="submit" class="btn-primary">
                        Đặt lại mật khẩu
                    </button>

                </form>

                {{-- QUAY LẠI --}}
                <form method="GET"
                      action="{{ url('/forgot-password/reset-session') }}"
                      style="margin-top:14px;">

                    <button type="submit" class="btn-secondary">
                        ← Đổi số điện thoại khác
                    </button>

                </form>

            @endif

            <div class="auth-links">

                <a href="{{ url('/login') }}">
                    ← Quay lại đăng nhập
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>