<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
</head>
<body>
    <h2>Đăng nhập</h2>
    <form method="POST" action="/login">
        @csrf
        <label>Email:</label>
        <input type="email" name="email" required>
        <br>
        <label>Mật khẩu:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Đăng nhập</button>
    </form>
</body>
</html>
