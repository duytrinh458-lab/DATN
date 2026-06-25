<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký — Vanguard</title>

    <link rel="stylesheet" href="{{ asset('Css/auth.css') }}">
</head>
<body>

@include('partials.alert')

<div class="auth-wrapper">

    {{-- CỘT TRÁI --}}
    <div class="auth-brand">

        <div>
            <div class="brand-logo">
                Vanguard
            </div>

            <div class="brand-tagline">
                Unmanned Aerial Vehicle Platform
            </div>
        </div>

        <div class="brand-center">

            <h1 class="brand-title">
                Tham gia<br>
                <em>hệ thống</em>
            </h1>

            <p class="brand-desc">
                Tạo tài khoản để mua sắm, theo dõi đơn hàng
                và quản lý ví điện tử của bạn.
            </p>

        </div>

        <div class="brand-footer">
            © 2026 Vanguard. All rights reserved.
        </div>

    </div>

    {{-- CỘT PHẢI --}}
    <div class="auth-form-panel">

        <div class="auth-box">

            <h2>Tạo tài khoản</h2>

            {{-- ===================================================== --}}
            {{-- CHƯA GỬI OTP --}}
            {{-- ===================================================== --}}
            @if(!session('phone_step1'))

                <p class="subtitle">
                    Đăng ký nhanh qua số điện thoại
                </p>

                <div class="step-indicator">

                    <div class="step active">
                        <span class="step-num">1</span>
                        <span>Xác thực SĐT</span>
                    </div>

                    <div class="step-divider"></div>

                    <div class="step">
                        <span class="step-num">2</span>
                        <span>Thông tin + OTP</span>
                    </div>

                </div>

                {{-- FORM GỬI OTP --}}
                <form method="POST" action="{{ url('/send-otp-register') }}">

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

            @endif


            {{-- ===================================================== --}}
            {{-- ĐÃ GỬI OTP --}}
            {{-- ===================================================== --}}
            @if(session('phone_step1'))

                <p class="subtitle">
                    Xác thực OTP để hoàn tất đăng ký
                </p>

                <div class="step-indicator">

                    <div class="step active">
                        <span class="step-num">✓</span>
                        <span>SĐT đã xác thực</span>
                    </div>

                    <div class="step-divider"></div>

                    <div class="step active">
                        <span class="step-num">2</span>
                        <span>Thông tin tài khoản</span>
                    </div>

                </div>

                <div class="divider">
    Nhập OTP để hoàn tất
</div>

<div class="back-step">

    <a href="{{ url('/register/reset-phone') }}">
        ← Đổi số điện thoại
    </a>

</div>

{{-- FORM HOÀN TẤT --}}
<form method="POST" action="{{ url('/verify-otp-register') }}">

                    @csrf

                    <div class="form-group">

                        <label>Mã OTP (6 số)</label>

                        <input type="text"
                               name="otp_code"
                               placeholder="• • • • • •"
                               maxlength="6"
                               required>

                    </div>

                    <div class="form-group">

                        <label>Họ và tên</label>

                        <input type="text"
                               name="full_name"
                               value="{{ old('full_name') }}"
                               placeholder="Nguyễn Văn A"
                               required>

                    </div>

                    <div class="form-group">

                        <label>Email</label>

                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="example@email.com"
                               required>

                    </div>

                    <div class="form-group">

                        <label>Mật khẩu</label>

                        <input type="password"
                               name="password"
                               placeholder="Tối thiểu 6 ký tự"
                               required>

                    </div>

                    <button type="submit" class="btn-primary">
                        Hoàn tất đăng ký
                    </button>

                </form>

            @endif

            <div class="auth-links">

                Đã có tài khoản?
                <a href="{{ url('/login') }}">
                    Đăng nhập
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>