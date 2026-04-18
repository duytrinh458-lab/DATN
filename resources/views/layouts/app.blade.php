<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">Cửa Hàng Máy Bay Không Người Lái</div>
            <ul>
                <li><a href="{{ url('/') }}">Trang chủ</a>http://localhost:8000/home</li>
                <li><a href="{{ url('/products') }}">Sản phẩm</a></li>
                <li><a href="{{ url('/services') }}">Dịch vụ</a></li>
                <li><a href="{{ url('/news') }}">Tin tức</a></li>
                <li><a href="{{ url('/contact') }}">Liên hệ</a></li>
            </ul>
            <div class="login-btn">
                <a href="{{ url('/login') }}">Đăng nhập</a>
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>© 2026 AERIAL VANGUARD - Công nghệ UAV tiên phong</p>
    </footer>
</body>
</html>
