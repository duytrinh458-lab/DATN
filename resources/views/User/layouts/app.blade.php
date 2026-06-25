<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Vanguard')</title>


    {{-- Preconnect để giảm latency --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

{{-- Fonts --}}
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

{{-- Icons --}}
<link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <!-- {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" /> -->

    {{-- Main CSS --}}
    <link rel="stylesheet" href="{{ asset('Css/User/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    @stack('styles')
</head>

<body>

<header class="mission-header">
    <nav class="navbar">
        <div class="logo-group">
            <a href="{{ url('/home') }}" class="logo">
                Vanguard 
            </a>
            <div class="status-dot"></div>
        </div>

        <ul class="nav-links" id="navLinks">
            <li><a href="{{ url('/home') }}" class="{{ request()->is('home') ? 'active' : '' }}">Trang chủ</a></li>
            <li><a href="{{ route('user.products') }}" class="{{ request()->routeIs('user.products') ? 'active' : '' }}">Sản phẩm</a></li>
            <li><a href="{{ route('user.categories') }}" class="{{ request()->routeIs('user.categories*') ? 'active' : '' }}">Danh mục</a></li>
            <li><a href="{{ url('/orders') }}" class="{{ request()->is('orders*') ? 'active' : '' }}">Đơn hàng</a></li>
            <li><a href="{{ route('user.news.index') }}" class="{{ request()->routeIs('user.news*') ? 'active' : '' }}">Tin tức</a></li>

            <li class="nav-mobile-logout">
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn">
                        <span class="material-symbols-outlined">logout</span>
                        Đăng xuất
                    </button>
                </form>
            </li>
        </ul>

        <div class="auth-group">
            <a href="{{ url('/cart') }}" class="icon-btn">
                <span class="material-symbols-outlined">shopping_cart</span>
            </a>

            <a href="{{ url('/profile') }}" class="icon-btn">
                <span class="material-symbols-outlined">person</span>
            </a>

            <a href="{{ url('/wallet') }}" class="icon-btn wallet-btn">
                <span class="material-symbols-outlined">account_balance_wallet</span>
                <span class="wallet-amount">
                    {{ number_format($walletBalance ?? 0, 0, ',', '.') }}VND
                </span>
            </a>

            <div class="divider-v"></div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn-logout">
                    <span class="material-symbols-outlined">logout</span>
                    Đăng xuất
                </button>
            </form>
        </div>

        <button class="nav-toggle" id="navToggle">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </nav>
</header>

<main class="content-viewport">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-col">
            <h4>Vanguard</h4>
            <p>Hệ thống thương mại điện tử Vanguard hiện đại thế hệ mới.</p>
        </div>

        <div class="footer-col">
            <h4>Điều hướng</h4>
            <a href="{{ url('/home') }}">Trang chủ</a>
            <a href="{{ route('user.products') }}">Sản phẩm</a>
            <a href="{{ route('user.news.index') }}">Tin tức</a>
            <a href="{{ url('/orders') }}">Đơn hàng</a>
        </div>

        <div class="footer-col">
            <h4>Hỗ trợ</h4>
            <p>Hotline: 0342626836</p>
            <p>Email: trinhduy@gmail.com</p>
        </div>
    </div>

    <div class="footer-bottom">
        <span>© 2026 Vanguard</span>
        <span>Version 3.0</span>
    </div>
</footer>

{{-- ================= TOAST SYSTEM ================= --}}
@include('partials.alert')

{{-- ================= SCRIPTS ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // BƯỚC 4: Tối ưu hóa xử lý đóng/mở Hamburger Menu an toàn hơn
    const navToggle = document.getElementById('navToggle');
    const navLinks  = document.getElementById('navLinks');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('show');
        });
    }
});
</script>

@stack('scripts')

<div class="zalo-floating-btn">
    <a href="https://zalo.me/0342626836" target="_blank" rel="noopener noreferrer">
        <div class="zalo-icon-wrapper">
            <img src="{{ asset('images/zalo-icon.png') }}" alt="Chat Zalo">
        </div>
    </a>
</div>


</body>
</html>