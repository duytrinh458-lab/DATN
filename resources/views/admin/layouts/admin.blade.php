<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — UAV Store')</title>

    <link rel="stylesheet" href="{{ asset('Css/Admin/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

    <style>
    /* ============================================
       ADMIN LAYOUT — Inline base styles
       (Nhúng trực tiếp để đảm bảo hiển thị đúng)
    ============================================ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --sidebar-w: 240px;
        --sidebar-bg: #0d1117;
        --sidebar-border: rgba(255,255,255,0.06);
        --sidebar-hover: rgba(255,255,255,0.05);
        --sidebar-active-bg: rgba(0,212,255,0.1);
        --sidebar-active-text: #00d4ff;
        --sidebar-text: #8b949e;
        --header-h: 56px;
        --main-bg: #f4f6f9;
        --accent: #00d4ff;
        --font: 'Segoe UI', system-ui, sans-serif;
    }

    body {
        font-family: var(--font);
        background: var(--main-bg);
        display: flex;
        min-height: 100vh;
    }

    /* ---- SIDEBAR ---- */
    .admin-sidebar {
        width: var(--sidebar-w);
        background: var(--sidebar-bg);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0;
        height: 100vh;
        border-right: 1px solid var(--sidebar-border);
        z-index: 100;
        overflow-y: auto;
    }

    .sidebar-brand {
        padding: 20px 20px 16px;
        border-bottom: 1px solid var(--sidebar-border);
    }

    .sidebar-brand .logo-text {
        font-size: 18px;
        font-weight: 700;
        color: var(--accent);
        letter-spacing: 2px;
    }

    .sidebar-brand .logo-sub {
        font-size: 10px;
        color: var(--sidebar-text);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 3px;
    }

    .sidebar-section {
        padding: 16px 12px 4px;
    }

    .sidebar-section-label {
        font-size: 10px;
        color: #484f58;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 0 8px;
        margin-bottom: 4px;
    }

    .sidebar-nav { list-style: none; }

    .sidebar-nav li a,
    .sidebar-nav li .sidebar-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border-radius: 8px;
        color: var(--sidebar-text);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 400;
        transition: background 0.15s, color 0.15s;
        cursor: pointer;
        border: none;
        background: transparent;
        width: 100%;
        text-align: left;
    }

    .sidebar-nav li a:hover,
    .sidebar-nav li .sidebar-btn:hover {
        background: var(--sidebar-hover);
        color: #c9d1d9;
    }

    .sidebar-nav li a.active {
        background: var(--sidebar-active-bg);
        color: var(--sidebar-active-text);
        font-weight: 500;
    }

    .sidebar-nav li a.active i {
        color: var(--sidebar-active-text);
    }

    .sidebar-nav li a i,
    .sidebar-nav li .sidebar-btn i {
        width: 16px;
        font-size: 14px;
        text-align: center;
        flex-shrink: 0;
    }

    .sidebar-footer {
        margin-top: auto;
        padding: 12px;
        border-top: 1px solid var(--sidebar-border);
    }

    /* ---- MAIN AREA ---- */
    .admin-body {
        margin-left: var(--sidebar-w);
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* ---- TOP HEADER ---- */
    .admin-topbar {
        height: var(--header-h);
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        position: sticky;
        top: 0;
        z-index: 50;
    }

    .topbar-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .admin-avatar {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        color: #000;
    }

    .admin-name {
        font-size: 13px;
        color: #374151;
        font-weight: 500;
    }

    /* ---- PAGE CONTENT ---- */
    .admin-main {
        flex: 1;
        padding: 24px;
    }

    /* ---- TOAST ALERT (dùng chung) ---- */
    .alert-toast {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .alert-toast--success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .alert-toast--error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert-toast__icon { font-size: 18px; flex-shrink: 0; }
    </style>

    @stack('styles')
</head>
<body>

{{-- ============ SIDEBAR ============ --}}
<aside class="admin-sidebar">

    <div class="sidebar-brand">
        <div class="logo-text">UAV ADMIN</div>
        <div class="logo-sub">Control Panel</div>
    </div>

    {{-- NHÓM 1: TỔNG QUAN --}}
    <div class="sidebar-section">
        <div class="sidebar-section-label">Tổng quan</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    Dashboard
                </a>
            </li>
        </ul>
    </div>

    {{-- NHÓM 2: QUẢN LÝ --}}
    <div class="sidebar-section">
        <div class="sidebar-section-label">Quản lý</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('admin.products.index') }}"
                   class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-drone"></i>
                    Sản phẩm
                </a>
            </li>
            <li>
                <a href="{{ route('admin.categories.index') }}"
                   class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-tree"></i>
                    Danh mục
                </a>
            </li>
            <li>
                <a href="{{ route('admin.orders.index') }}"
                   class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-box"></i>
                    Đơn hàng
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    Người dùng
                </a>
            </li>
        </ul>
    </div>

    {{-- NHÓM 3: NỘI DUNG --}}
    <div class="sidebar-section">
        <div class="sidebar-section-label">Nội dung</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('admin.news.index') }}"
                   class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-newspaper"></i>
                    Tin tức
                </a>
            </li>
            <li>
                <a href="{{ route('admin.interactions.comments') }}"
                   class="{{ request()->routeIs('admin.interactions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-comments"></i>
                    Bình luận
                </a>
            </li>
        </ul>
    </div>

    {{-- NHÓM 4: TÀI CHÍNH --}}
    <div class="sidebar-section">
        <div class="sidebar-section-label">Tài chính</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('admin.transactions.index') }}"
                   class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i>
                    Giao dịch V-Pay
                </a>
            </li>
            <li>
                <a href="{{ route('admin.refunds.index') }}"
                   class="{{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-rotate-left"></i>
                    Hoàn trả
                </a>
            </li>
            <li>
                <a href="{{ route('admin.qr.index') }}"
                   class="{{ request()->routeIs('admin.qr.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-qrcode"></i>
                    Cài QR Bank
                </a>
            </li>
        </ul>
    </div>

    {{-- FOOTER: ĐĂNG XUẤT --}}
    <div class="sidebar-footer">
        <ul class="sidebar-nav">
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-btn">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Đăng xuất
                    </button>
                </form>
            </li>
        </ul>
    </div>

</aside>

{{-- ============ NỘI DUNG CHÍNH ============ --}}
<div class="admin-body">

    {{-- TOP BAR --}}
    <header class="admin-topbar">
        <div class="topbar-title">@yield('title', 'Dashboard')</div>
        <div class="topbar-right">
            <div class="admin-name">{{ auth()->user()->full_name ?? 'Admin' }}</div>
            <div class="admin-avatar">
                {{ strtoupper(substr(auth()->user()->full_name ?? 'A', 0, 1)) }}
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="admin-main">
        @yield('content')
    </main>

</div>

@stack('scripts')
</body>
</html>