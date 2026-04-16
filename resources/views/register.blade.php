<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo tài khoản</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); /* xanh biển đậm */
            display: flex;
            justify-content: center;
            padding: 40px;
            color: #e0f7fa;
        }
        .container {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.6);
            width: 420px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        h2 {
            text-align: center;
            color: #00bcd4; /* xanh biển sáng */
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        p.subtitle {
            text-align: center;
            color: #b2ebf2;
            margin-bottom: 20px;
        }
        h3 {
            color: #4dd0e1;
            margin-top: 20px;
            border-left: 4px solid #00bcd4;
            padding-left: 6px;
        }
        label {
            font-weight: bold;
            margin: 8px 0 4px;
            display: block;
            color: #e0f7fa;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #00bcd4;
            border-radius: 8px;
            margin-bottom: 14px;
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        input::placeholder {
            color: #b2ebf2;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #00bcd4; /* xanh biển sáng */
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        button:hover {
            background: #0097a7; /* xanh biển đậm hơn khi hover */
            box-shadow: 0 0 15px #00bcd4;
        }
        .link {
            text-align: center;
            margin-top: 15px;
        }
        .link a {
            color: #4dd0e1;
            text-decoration: none;
            font-weight: bold;
        }
        .alert-success, .alert-error {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .alert-success { background: rgba(0,188,212,0.2); color: #00e5ff; }
        .alert-error { background: rgba(255,82,82,0.2); color: #ff8a80; }
    </style>
</head>
<body>
<div class="container">
    <h2>Tạo tài khoản</h2>
    <p class="subtitle">Bắt đầu hành trình của bạn với vài bước đơn giản</p>

    {{-- Thông báo --}}
    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Bước 1: Xác thực số điện thoại --}}
    <h3>1. Xác thực số điện thoại</h3>
    <form method="POST" action="{{ url('/register/send-otp') }}">
        @csrf
        <input type="text" name="phone" value="{{ old('phone', session('phone_step1')) }}" placeholder="090..." required>
        <button type="submit">Gửi OTP</button>
    </form>

    {{-- Bước 2: Thông tin cá nhân --}}
    <h3>2. Thông tin cá nhân</h3>
    <form method="POST" action="{{ url('/register/verify-otp') }}">
        @csrf
        <label>Mã xác thực (6 số):</label>
        <input type="text" name="otp_code" placeholder="xxxxxx" required>

        <label>Họ và tên:</label>
        <input type="text" name="full_name" value="{{ old('full_name') }}" required>

        <label>Địa chỉ Email:</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>Mật khẩu:</label>
        <input type="password" name="password" required>

        <button type="submit">Đăng ký ngay</button>
    </form>

    <div class="link">
        Đã có tài khoản? <a href="{{ url('/login') }}">Đăng nhập</a>
    </div>
</div>
</body>
</html>
