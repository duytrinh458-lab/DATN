<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu — UAV Store</title>
    <link rel="stylesheet" href="{{ asset('Css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper">

    {{-- CỘT TRÁI --}}
    <div class="auth-brand">
        <div>
            <div class="brand-logo">UAV<span>Store</span></div>
            <div class="brand-tagline">Unmanned Aerial Vehicle Platform</div>
        </div>

        <div class="brand-center">
            <h1 class="brand-title">Khôi phục<br><em>tài khoản</em></h1>
            <p class="brand-desc">
                Nhập số điện thoại để nhận mã OTP.
                Mã có hiệu lực trong 5 phút.
            </p>
        </div>

        <div class="brand-footer">© 2026 UAV Store. All rights reserved.</div>
    </div>

    {{-- CỘT PHẢI --}}
    <div class="auth-form-panel">
        <div class="auth-box">

            <h2>Quên mật khẩu?</h2>
            <p class="subtitle">Khôi phục qua số điện thoại đã đăng ký</p>

            {{-- STEP INDICATOR --}}
            <div class="step-indicator">
                <div class="step active">
                    <span class="step-num">1</span>
                    <span>Nhập SĐT</span>
                </div>
                <div class="step-divider"></div>
                <div class="step">
                    <span class="step-num">2</span>
                    <span>OTP + Mật khẩu mới</span>
                </div>
            </div>

            {{-- ALERTS --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- BƯỚC 1: GỬI OTP --}}
            <form method="POST" action="{{ url('/forgot-password/send-otp') }}">
                @csrf
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" placeholder="09xxxxxxxx" required>
                </div>
                <button type="submit" class="btn-secondary">Gửi mã OTP →</button>
            </form>

            <div class="divider">Đã có mã OTP?</div>

            {{-- BƯỚC 2: ĐỔI MẬT KHẨU --}}
            <form method="POST" action="{{ url('/forgot-password/verify-otp') }}">
                @csrf

                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" placeholder="09xxxxxxxx" required>
                </div>

                <div class="form-group">
                    <label>Mã OTP (6 số)</label>
                    <input type="text" name="otp_code" placeholder="• • • • • •"
                           maxlength="6" required>
                </div>

                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="new_password"
                           placeholder="Tối thiểu 6 ký tự" required>
                </div>

                <button type="submit" class="btn-primary">Đặt lại mật khẩu</button>
            </form>

            <div class="auth-links">
                <a href="{{ url('/login') }}">← Quay lại đăng nhập</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>