<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo tài khoản</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
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
            width: 420px;
        }
        h2 { text-align: center; color: #00bcd4; }
        h3 { color: #4dd0e1; margin-top: 20px; }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #00bcd4;
            border: none;
            color: white;
            cursor: pointer;
        }
        .alert-success { color: #00e5ff; }
        .alert-error { color: #ff8a80; }
    </style>
</head>
<body>

<div class="container">

    <h2>Đăng ký</h2>

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- ERROR --}}
    @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    {{-- ERROR validate step 2 --}}
    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ================= STEP 1 ================= --}}
    <h3>1. Nhập số điện thoại</h3>
    <form method="POST" action="{{ url('/send-otp-register') }}">
        @csrf
        <input type="text" name="phone"
               value="{{ old('phone', session('phone_step1')) }}"
               placeholder="Số điện thoại" required>

        <button type="submit">Gửi OTP</button>
    </form>

    {{-- ================= STEP 2 ================= --}}
    <h3>2. Nhập thông tin + OTP</h3>
    <form method="POST" action="{{ url('/verify-otp-register') }}">
        @csrf

        <input type="text" name="otp_code" placeholder="Nhập OTP" required>

        <input type="text" name="full_name"
               value="{{ old('full_name') }}"
               placeholder="Họ tên" required>

        <input type="email" name="email"
               value="{{ old('email') }}"
               placeholder="Email" required>

        <input type="password" name="password" placeholder="Mật khẩu" required>

        <button type="submit">Đăng ký</button>
    </form>

    <p style="text-align:center">
        Đã có tài khoản? <a href="{{ url('/login') }}">Đăng nhập</a>
    </p>

</div>

</body>
</html>