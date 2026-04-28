<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UAV Store')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <link rel="stylesheet" href="{{ asset('Css/User/style.css') }}">
    @stack('styles')
</head>
<body class="dark-theme">
    <header class="mission-header">
        <nav class="navbar">
            <div class="logo-group">
                <div class="logo">UAV STORE</div>
                <div class="mission-status">
                    <span class="dot"></span> 
                </div>
            </div>

            <ul class="nav-links">
                <li><a href="{{ url('/home') }}">TRANG CHỦ</a></li>
                <li><a href="{{ url('/products') }}">SẢN PHẨM</a></li>
                <li><a href="{{ url('/services') }}">DỊCH VỤ</a></li>
                <li><a href="{{ url('/news') }}">TIN TỨC</a></li>
            </ul>

            <div class="auth-group">
                <div class="utility-icons">
                    <a href="{{ url('/cart') }}" class="icon-btn">
                        <span class="material-symbols-outlined">shopping_cart</span>
                    </a>
                    <a href="{{ url('/profile') }}" class="icon-btn">
                        <span class="material-symbols-outlined">monitoring</span>
                    </a>
                </div>
                <div class="divider-v"></div>
                <a href="{{ url('/login') }}" class="btn-terminal">ĐĂNG XUẤT</a>
            </div>
        </nav>
        <div class="light-leak"></div>
    </header>

    <main class="content-viewport">
        @yield('content')
    </main>

    <footer class="hud-footer">
        <div class="footer-top-strip">
            <div class="footer-item">
                <span class="label">EXPLORE_SYSTEM:</span>
                <span class="value">Drone Camera // Mini Series // Pro Dev</span>
            </div>
            <div class="footer-item">
                <span class="label">COMMS_CHANNEL:</span>
                <span class="value">UAV.com</span>
            </div>
        </div>
        <div class="footer-bottom-bar">
            <div class="copyright">© 2026 UAV STORE // Rất Vui Được Phục Vụ Bạn</div>
            <div class="coordinates">Mọi Thắc Mắc Liên Hệ Qua SĐT | 19001508</div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>