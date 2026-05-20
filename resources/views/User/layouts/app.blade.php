<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UAV Store')</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

    <link rel="stylesheet" href="{{ asset('Css/User/style.css') }}">
    @stack('styles')

    <style>
    /* ====================================================
       USER LAYOUT — Inline base (override file style.css)
    ==================================================== */
    *, *::before, *::after { box-sizing: border-box; }

    :root {
        --nav-bg: rgba(10, 14, 26, 0.95);
        --nav-border: rgba(0,212,255,0.15);
        --accent: #00d4ff;
        --text-nav: #c9d1d9;
        --text-muted: #6b7280;
        --body-bg: #0a0e1a;
        --font-head: 'Syne', sans-serif;
        --font-body: 'DM Sans', sans-serif;
    }

    body {
        background: var(--body-bg);
        font-family: var(--font-body);
        margin: 0;
    }

    /* ---- NAVBAR ---- */
    .mission-header {
        position: sticky;
        top: 0;
        z-index: 200;
        background: var(--nav-bg);
        border-bottom: 1px solid var(--nav-border);
        backdrop-filter: blur(12px);
    }

    .navbar {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    /* Logo */
    .logo-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .logo {
        font-family: var(--font-head);
        font-size: 18px;
        font-weight: 800;
        color: var(--accent);
        letter-spacing: 3px;
        text-decoration: none;
        text-transform: uppercase;
    }

    .status-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #00e676;
        box-shadow: 0 0 6px #00e676;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    /* Nav links */
    .nav-links {
        display: flex;
        list-style: none;
        gap: 4px;
        margin: 0;
        padding: 0;
    }

    .nav-links li a {
        display: block;
        padding: 7px 14px;
        color: var(--text-nav);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0.5px;
        border-radius: 6px;
        transition: background 0.15s, color 0.15s;
    }

    .nav-links li a:hover,
    .nav-links li a.active {
        background: rgba(0,212,255,0.1);
        color: var(--accent);
    }

    /* Right group */
    .auth-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .icon-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        color: var(--text-nav);
        text-decoration: none;
        border-radius: 8px;
        font-size: 13px;
        transition: background 0.15s, color 0.15s;
        position: relative;
    }

    .icon-btn:hover {
        background: rgba(255,255,255,0.06);
        color: #fff;
    }

    .icon-btn .material-symbols-outlined {
        font-size: 20px;
    }

    /* Cart badge */
    .cart-badge {
        position: absolute;
        top: 4px; right: 4px;
        width: 16px; height: 16px;
        background: var(--accent);
        color: #000;
        border-radius: 50%;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Wallet */
    .wallet-amount {
        font-family: var(--font-head);
        font-size: 12px;
        font-weight: 600;
        color: var(--accent);
        white-space: nowrap;
    }

    /* Divider */
    .divider-v {
        width: 1px;
        height: 20px;
        background: rgba(255,255,255,0.1);
    }

    /* Logout button */
    .btn-logout {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        background: transparent;
        border: 1px solid rgba(255,82,82,0.3);
        border-radius: 8px;
        color: #ff5252;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s;
        font-family: var(--font-body);
    }

    .btn-logout:hover {
        background: rgba(255,82,82,0.1);
    }

    .logout-form { margin: 0; }

    /* ---- HAMBURGER MOBILE ---- */
    .nav-toggle {
        display: none;
        background: none;
        border: none;
        color: var(--text-nav);
        font-size: 22px;
        cursor: pointer;
        padding: 6px;
    }

    /* ---- MAIN CONTENT ---- */
    .content-viewport {
        min-height: calc(100vh - 60px);
    }

    /* ---- FOOTER ---- */
    .site-footer {
        background: #080c17;
        border-top: 1px solid rgba(255,255,255,0.06);
        padding: 40px 24px 24px;
        margin-top: 60px;
    }

    .footer-inner {
        max-width: 1280px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 32px;
    }

    .footer-col h4 {
        font-family: var(--font-head);
        font-size: 13px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 12px;
    }

    .footer-col p,
    .footer-col a {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 2;
        text-decoration: none;
        display: block;
    }

    .footer-col a:hover { color: var(--text-nav); }

    .footer-bottom {
        max-width: 1280px;
        margin: 24px auto 0;
        padding-top: 16px;
        border-top: 1px solid rgba(255,255,255,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: var(--text-muted);
    }

    /* ---- TOAST NOTIFICATION ---- */
    .vg-toast {
        position: fixed;
        bottom: 24px; right: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        z-index: 9999;
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.3s, transform 0.3s;
        pointer-events: none;
        max-width: 360px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    }

    .vg-toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .vg-toast--success {
        background: #0d2a1a;
        border: 1px solid #166534;
        color: #4ade80;
    }

    .vg-toast--error {
        background: #2a0d0d;
        border: 1px solid #7f1d1d;
        color: #f87171;
    }

    .vg-toast .material-symbols-outlined { font-size: 20px; flex-shrink: 0; }

    /* ---- RESPONSIVE ---- */
    @media (max-width: 900px) {
        .nav-links { display: none; }
        .nav-toggle { display: block; }
        .footer-inner { grid-template-columns: 1fr; gap: 20px; }
    }
    </style>
</head>

<body>

{{-- ===== NAVBAR ===== --}}
<header class="mission-header">
    <nav class="navbar">

        {{-- LOGO --}}
        <div class="logo-group">
            <a href="{{ url('/home') }}" class="logo">UAVStore</a>
            <div class="status-dot" title="Hệ thống hoạt động"></div>
        </div>

        {{-- NAV LINKS --}}
        <ul class="nav-links">
            <li><a href="{{ url('/home') }}"
                   class="{{ request()->is('home') ? 'active' : '' }}">Trang chủ</a></li>
            <li><a href="{{ route('user.products') }}"
                   class="{{ request()->routeIs('user.products') ? 'active' : '' }}">Sản phẩm</a></li>
            <li><a href="{{ route('user.categories') }}"
                   class="{{ request()->routeIs('user.categories*') ? 'active' : '' }}">Danh mục</a></li>
            <li><a href="{{ url('/orders') }}"
                   class="{{ request()->is('orders*') ? 'active' : '' }}">Đơn hàng</a></li>
            <li><a href="{{ route('user.news.index') }}"
                   class="{{ request()->routeIs('user.news*') ? 'active' : '' }}">Tin tức</a></li>
        </ul>

        {{-- RIGHT GROUP --}}
        <div class="auth-group">

            {{-- GIỎ HÀNG --}}
            <a href="{{ url('/cart') }}" class="icon-btn" title="Giỏ hàng">
                <span class="material-symbols-outlined">shopping_cart</span>
            </a>

            {{-- HỒ SƠ --}}
            <a href="{{ url('/profile') }}" class="icon-btn" title="Hồ sơ">
                <span class="material-symbols-outlined">person</span>
            </a>

            {{-- VÍ --}}
            <a href="{{ url('/wallet') }}" class="icon-btn" title="Ví V-Pay">
                <span class="material-symbols-outlined">account_balance_wallet</span>
                <span class="wallet-amount">
                    {{ number_format($walletBalance ?? 0, 0, ',', '.') }}₫
                </span>
            </a>

            <div class="divider-v"></div>

            {{-- ĐĂNG XUẤT — form đúng chuẩn --}}
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="btn-logout">
                    <span class="material-symbols-outlined" style="font-size:16px">logout</span>
                    Đăng xuất
                </button>
            </form>

        </div>

        <button class="nav-toggle" id="navToggle" aria-label="Menu">☰</button>

    </nav>
</header>

{{-- ===== NỘI DUNG ===== --}}
<main class="content-viewport">
    @yield('content')
</main>

{{-- ===== FOOTER ===== --}}
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-col">
            <h4>UAV Store</h4>
            <p>Nền tảng thương mại điện tử<br>chuyên biệt máy bay không người lái.</p>
        </div>
        <div class="footer-col">
            <h4>Điều hướng</h4>
            <a href="{{ url('/home') }}">Trang chủ</a>
            <a href="{{ route('user.products') }}">Sản phẩm</a>
            <a href="{{ url('/orders') }}">Đơn hàng</a>
            <a href="{{ route('user.news.index') }}">Tin tức</a>
        </div>
        <div class="footer-col">
            <h4>Hỗ trợ</h4>
            <p>Hotline: 1900 1508</p>
            <p>Email: support@uavstore.vn</p>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© 2026 UAV Store. All rights reserved.</span>
        <span>Phiên bản 2.0</span>
    </div>
</footer>

{{-- ===== TOAST NOTIFICATION ===== --}}
@if(session('success') || session('error'))
    <div id="vg-toast"
         class="vg-toast {{ session('success') ? 'vg-toast--success' : 'vg-toast--error' }}">
        <span class="material-symbols-outlined">
            {{ session('success') ? 'check_circle' : 'warning' }}
        </span>
        <span>{{ session('success') ?? session('error') }}</span>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const t = document.getElementById('vg-toast');
        if (t) {
            setTimeout(() => t.classList.add('show'), 100);
            setTimeout(() => t.classList.remove('show'), 4500);
        }
    });
    </script>
@endif

{{-- MOBILE NAV TOGGLE --}}
<script>
document.getElementById('navToggle')?.addEventListener('click', function() {
    const nav = document.querySelector('.nav-links');
    nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
});
</script>

@stack('scripts')
</body>
</html>