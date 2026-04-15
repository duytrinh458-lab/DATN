<!DOCTYPE html>
<html>
<head>
    <title>Đăng ký bằng OTP</title>
</head>
<body>
    <h2>Đăng ký tài khoản</h2>

    {{-- Hiển thị thông báo thành công --}}
    @if (session('success'))
        <div style="color: green; margin-bottom: 10px;">{{ session('success') }}</div>
    @endif

    {{-- Hiển thị lỗi --}}
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Bước 1: Gửi OTP --}}
    <form method="POST" action="/register/send-otp">
        @csrf
        <label>Nhập số điện thoại để nhận mã:</label>
        <input type="text" name="phone" value="{{ old('phone', session('phone_step1')) }}" placeholder="090..." required>
        <button type="submit">Gửi OTP</button>
    </form>

    <hr>

    {{-- Bước 2: Xác thực và hoàn tất thông tin --}}
    <form method="POST" action="/register/verify-otp">
        @csrf
        <h3>Xác nhận thông tin</h3>
        <label>Số điện thoại đã nhận mã:</label>
        <input type="text" name="phone" value="{{ old('phone', session('phone_step1')) }}" readonly style="background-color: #f0f0f0;">
        <br>
        <label>Mã OTP (6 chữ số):</label>
        <input type="text" name="otp_code" placeholder="xxxxxx" required>
        <br>
        <label>Họ tên:</label>
        <input type="text" name="full_name" value="{{ old('full_name') }}" required>
        <br>
        <label>Email:</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
        <br>
        <label>Mật khẩu mới:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Xác thực & Đăng ký</button>
    </form>
</body>
</html>
