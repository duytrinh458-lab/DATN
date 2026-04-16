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
            letter-spacing: 2px;
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
            background: #00bcd4;
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
            background: #0097a7;
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
    </style>
</head>
<body>
<div class="container">
    <h2>Quên mật khẩu</h2>

    {{-- Bước 1: Gửi OTP --}}
    <h3>1. Xác thực số điện thoại</h3>
    <form method="POST" action="{{ url('/forgot-password/send-otp') }}">
        @csrf
        <label>Số điện thoại:</label>
        <input type="text" name="phone" placeholder="090..." required>
        <button type="submit">Gửi OTP</button>
    </form>

    {{-- Bước 2: Xác thực OTP và đổi mật khẩu --}}
    <h3>2. Xác thực & Đổi mật khẩu</h3>
    <form method="POST" action="{{ url('/forgot-password/verify-otp') }}">
        @csrf
        <label>Số điện thoại:</label>
        <input type="text" name="phone" required>

        <label>Mã OTP:</label>
        <input type="text" name="otp_code" placeholder="xxxxxx" required>

        <label>Email:</label>
        <input type="email" name="email" placeholder="example@email.com" required>

        <label>Mật khẩu mới:</label>
        <input type="password" name="new_password" placeholder="••••••••" required>

        <button type="submit">Xác thực & Đổi mật khẩu</button>
    </form>

    <div class="link">
        Nhớ mật khẩu rồi? <a href="{{ url('/login') }}">Đăng nhập</a>
    </div>
</div>
</body>
</html>
// sửa lại và cập nhập mới 