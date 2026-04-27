<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Cửa Hàng UAV')</title>
    <!-- CSS chung -->
    <link rel="stylesheet" href="{{ asset('Css/User/style.css') }}">
    <!-- CSS riêng từng trang -->
    @stack('styles')
</head>
<body class="dark-theme">
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">Cửa Hàng UAV</div>
            <ul class="nav-links">
                <li><a href="{{ url('/home') }}">Trang chủ</a></li>
                <li><a href="{{ url('/products') }}">Sản phẩm</a></li>
                <li><a href="{{ url('/services') }}">Dịch vụ</a></li>
                <li><a href="{{ url('/news') }}">Tin tức</a></li>
                <li><a href="{{ url('/contact') }}">Liên hệ</a></li>
                <li><a href="{{ url('/profile') }}">Thông tin tài khoản</a></li>
            </ul>
            <div class="login-btn">
                <a href="{{ url('/login') }}">Đăng Xuất</a>
            </div>
        </nav>
    </header>

    <!-- Nội dung riêng -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-section">
            <h4>Khám phá</h4>
            <p>Drone Camera, Mini Series, Pro Developer</p>
        </div>
        <div class="footer-section">
            <h4>Hỗ trợ</h4>
            <p>Email: support@aerialvanguard.com</p>
        </div>
        <div class="footer-section">
            <h4>Tin tức</h4>
            <p>Cập nhật công nghệ hàng không mới nhất</p>
        </div>
        <p class="copy">© 2026 Cửa Hàng UAV - Công nghệ UAV tiên phong</p>
    </footer>

    @stack('scripts')
</body>
</html>
