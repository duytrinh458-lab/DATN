<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — UAV Store</title>
    <link rel="stylesheet" href="{{ asset('Css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper">

    {{-- CỘT TRÁI: BRAND --}}
    <div class="auth-brand">
        <div>
            <div class="brand-logo">UAV<span>Store</span></div>
            <div class="brand-tagline">Unmanned Aerial Vehicle Platform</div>
        </div>

        <div class="brand-center">
            <h1 class="brand-title">Công nghệ<br><em>không giới hạn</em></h1>
            <p class="brand-desc">
                Nền tảng thương mại điện tử chuyên biệt cho máy bay không người lái.
                Hiệu suất, độ chính xác, đẳng cấp.
            </p>
        </div>

        <div class="brand-footer">© 2026 UAV Store. All rights reserved.</div>
    </div>

    {{-- CỘT PHẢI: FORM --}}
    <div class="auth-form-panel">
        <div class="auth-box">

            <h2>Chào mừng trở lại</h2>
            <p class="subtitle">Đăng nhập để tiếp tục khám phá</p>

            {{-- ALERT LỖI --}}
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="example@email.com"
                           value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-primary">Đăng nhập</button>
            </form>

            <div class="auth-links" style="margin-top: 14px;">
                <a href="{{ url('/forgot') }}">Quên mật khẩu?</a>
            </div>

            <div class="divider">hoặc</div>

            <div class="auth-links">
                Chưa có tài khoản? <a href="{{ url('/register') }}">Đăng ký ngay</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>