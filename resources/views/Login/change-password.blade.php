<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #e0f7fa;
        }

        .container {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 16px;
            width: 400px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }

        h2 {
            text-align: center;
            color: #00bcd4;
            margin-bottom: 20px;
        }

        input {
            width: 95%;
            padding: 10px;
            margin-bottom: 12px;
            border: none;
            border-radius: 8px;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #00bcd4;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #00e5ff;
        }

        .error {
            color: #ff8a80;
            margin-bottom: 10px;
        }

        .success {
            color: #00e5ff;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Đổi mật khẩu lần đầu</h2>

    {{-- HIỂN THỊ LỖI --}}
    @if ($errors->any())
        <div class="error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('password.change.update') }}">
        @csrf

        <input type="password" name="password" placeholder="Mật khẩu mới" required>

        <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" required>

        <button type="submit">Đổi mật khẩu</button>
    </form>

</div>

</body>
</html>