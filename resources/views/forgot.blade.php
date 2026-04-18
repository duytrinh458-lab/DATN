<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            display: flex;
            justify-content: center;
            align-items: flex-start;
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
            color: #00bcd4;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        h3 {
            color: #4dd0e1;
            margin-top: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 8px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #00bcd4;
            border: none;
            color: white;
            cursor: pointer;
        }
        .alert-success {
            background: #2e7d32;
            padding: 10px;
            margin-bottom: 10px;
        }
        .alert-error {
            background: #c62828;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Quên mật khẩu</h2>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR --}}
    @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    {{-- VALIDATE ERROR --}}
    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ================= STEP 1 ================= --}}
    <h3>1. Nhập số điện thoại</h3>
    <form method="POST" action="{{ url('/forgot-password/send-otp') }}">
        @csrf
        <input type="text" name="phone" placeholder="Số điện thoại" required>
        <button type="submit">Gửi OTP</button>
    </form>

    {{-- ================= STEP 2 ================= --}}
    <h3>2. Nhập OTP & mật khẩu mới</h3>
    <form method="POST" action="{{ url('/forgot-password/verify-otp') }}">
        @csrf

        <input type="text" name="phone" placeholder="Số điện thoại" required>

        <input type="text" name="otp_code" placeholder="Nhập OTP" required>

        <input type="password" name="new_password" placeholder="Mật khẩu mới" required>

        <button type="submit">Đổi mật khẩu</button>
    </form>

    <p style="text-align:center; margin-top:10px;">
        <a href="{{ url('/login') }}">Quay lại đăng nhập</a>
    </p>
</div>

</body>
</html>