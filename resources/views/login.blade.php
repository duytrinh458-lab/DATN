<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #e0f7fa;
        }
        .container {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.6);
            width: 380px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        h2 {
            text-align: center;
            color: #00bcd4;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        label {
            font-weight: bold;
            margin: 8px 0 4px;
            display: block;
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
        }
        button:hover {
            background: #0097a7;
            box-shadow: 0 0 15px #00bcd4;
        }
        .link {
            text-align: center;
            margin-top: 12px;
        }
        .link a {
            color: #4dd0e1;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .link a:hover {
            color: #00e5ff;
            text-shadow: 0 0 5px #00e5ff;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Đăng nhập</h2>

    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <label>Email:</label>
        <input type="email" name="email" placeholder="example@email.com" required>

        <label>Mật khẩu:</label>
        <input type="password" name="password" placeholder="••••••••" required>

        <button type="submit">Đăng nhập</button>
    </form>

    <!-- Quên mật khẩu -->
    <div class="link">
        <a href="{{ url('/forgot') }}">Quên mật khẩu?</a>
    </div>

    <!-- Đăng ký -->
    <div class="link">
        Chưa có tài khoản? <a href="{{ url('/register') }}">Đăng ký ngay</a>
    </div>
</div>

</body>
</html>