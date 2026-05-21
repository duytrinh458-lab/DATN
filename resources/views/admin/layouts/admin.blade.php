<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — UAV Store')</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('Css/Admin/admin-layout.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('Css/Admin/admin.css') }}"> -->

    {{-- Font Awesome --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
</head>
<body>

<aside class="admin-sidebar">

    {{-- LOGO --}}
    <div class="sidebar-brand">
        <div class="logo-text">UAV ADMIN</div>
        <div class="logo-sub">Control Panel</div>
    </div>

    {{-- MENU --}}
    <div class="sidebar-menu">

        {{-- TỔNG QUAN --}}
        <div class="sidebar-dropdown">

            <button class="dropdown-toggle active">
                <span>
                    <i class="fa-solid fa-chart-line"></i>
                    Tổng quan
                </span>

                <i class="fa-solid fa-chevron-down dropdown-icon"></i>
            </button>

            <div class="dropdown-menu show">

                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    Dashboard
                </a>

            </div>
        </div>

        {{-- QUẢN LÝ --}}
        <div class="sidebar-dropdown">

            <button class="dropdown-toggle">
                <span>
                    <i class="fa-solid fa-layer-group"></i>
                    Quản lý
                </span>

                <i class="fa-solid fa-chevron-down dropdown-icon"></i>
            </button>

            <div class="dropdown-menu">

                <a href="{{ route('admin.products.index') }}"
                   class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-drone"></i>
                    Sản phẩm
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-tree"></i>
                    Danh mục
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-box"></i>
                    Đơn hàng
                </a>

                <a href="{{ route('admin.refunds.index') }}"
                   class="{{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-check"></i>
                    Đơn đã hoàn
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    Người dùng
                </a>

            </div>
        </div>

        {{-- NỘI DUNG --}}
        <div class="sidebar-dropdown">

            <button class="dropdown-toggle">
                <span>
                    <i class="fa-solid fa-newspaper"></i>
                    Nội dung
                </span>

                <i class="fa-solid fa-chevron-down dropdown-icon"></i>
            </button>

            <div class="dropdown-menu">

                <a href="{{ route('admin.news.index') }}"
                   class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-newspaper"></i>
                    Tin tức
                </a>

                <a href="{{ route('admin.interactions.comments') }}"
                   class="{{ request()->routeIs('admin.interactions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-comments"></i>
                    Bình luận
                </a>

            </div>
        </div>

        {{-- TÀI CHÍNH --}}
        <div class="sidebar-dropdown">

            <button class="dropdown-toggle">
                <span>
                    <i class="fa-solid fa-wallet"></i>
                    Tài chính
                </span>

                <i class="fa-solid fa-chevron-down dropdown-icon"></i>
            </button>

            <div class="dropdown-menu">

                <a href="{{ route('admin.transactions.index') }}"
                   class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i>
                    Giao dịch V-Pay
                </a>

                <a href="{{ route('admin.qr.index') }}"
                   class="{{ request()->routeIs('admin.qr.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-qrcode"></i>
                    Cài QR Bank
                </a>

            </div>
        </div>

    </div>

    {{-- FOOTER --}}
    <div class="sidebar-footer">

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button type="submit" class="sidebar-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                Đăng xuất
            </button>
        </form>

    </div>

</aside>

{{-- ============ MAIN CONTENT ============ --}}
<div class="admin-body">

    {{-- TOPBAR --}}
    <header class="admin-topbar">

        <div class="topbar-title">
            @yield('title', 'Dashboard')
        </div>

        <div class="topbar-right">

            <div class="admin-name">
                {{ auth()->user()->full_name ?? 'Admin' }}
            </div>

            <div class="admin-avatar">
                {{ strtoupper(substr(auth()->user()->full_name ?? 'A', 0, 1)) }}
            </div>

        </div>

    </header>

    {{-- CONTENT --}}
    <main class="admin-main">
        @yield('content')
    </main>

</div>

@stack('scripts')

</body>

<script>
document.querySelectorAll('.sidebar-dropdown').forEach(dropdown => {

    const toggle = dropdown.querySelector('.dropdown-toggle');
    const menu = dropdown.querySelector('.dropdown-menu');

    // CLICK DROPDOWN
    toggle.addEventListener('click', () => {

        toggle.classList.toggle('active');

        menu.classList.toggle('show');

    });

    // AUTO ACTIVE IF CHILD ACTIVE
    const activeChild = menu.querySelector('a.active');

    if (activeChild) {

        toggle.classList.add('active');

        menu.classList.add('show');

    }

});
</script>

</html>