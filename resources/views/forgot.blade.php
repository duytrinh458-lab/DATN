<!DOCTYPE html>
<html>
<head>
    <title>Quên mật khẩu</title>
</head>
<body>
    <h2>Quên mật khẩu</h2>
    <form method="POST" action="/forgot-password/send-otp">
        @csrf
        <label>Số điện thoại:</label>
        <input type="text" name="phone" required>
        <button type="submit">Gửi OTP</button>
    </form>

    <form method="POST" action="/forgot-password/verify-otp">
        @csrf
        <label>Số điện thoại:</label>
        <input type="text" name="phone" required>
        <br>
        <label>OTP:</label>
        <input type="text" name="otp_code" required>
        <br>
        <label>Email:</label>
        <input type="email" name="email" required>
        <br>
        <label>Mật khẩu mới:</label>
        <input type="password" name="new_password" required>
        <br>
        <button type="submit">Xác thực & Đổi mật khẩu</button>
    </form>
</body>
</html>
