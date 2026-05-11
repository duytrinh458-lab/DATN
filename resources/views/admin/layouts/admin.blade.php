<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin - UAV')</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('Css/Admin/admin.css') }}">

    @stack('styles')
</head>

<body>
<div class="admin-wrapper">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="logo">UAV ADMIN</div>

        <ul class="sidebar-links">

            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    Trang Chủ
                </a>
            </li>

            <li>
                <a href="{{ route('admin.products.index') }}"
                   class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    Sản phẩm
                </a>
            </li>

            <li>
                <a href="{{ route('admin.orders.index') }}"
                   class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    Đơn hàng
                </a>
            </li>

            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    Người dùng
                </a>
            </li>

            <!-- ✅ INTERACTIONS FIX -->
            <li>
                <a href="{{ route('admin.interactions.comments') }}"
                   class="{{ request()->routeIs('admin.interactions.*') ? 'active' : '' }}">
                    Tương tác
                </a>
            </li>

            <li>
                <a href="{{ route('admin.transactions.index') }}"
                   class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                    Quản Lý Giao Dịch
                </a>
            </li>

            <li>
                <a href="{{ route('admin.categories.index') }}"
                   class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    Danh mục
                </a>
            </li>

            <!-- 🔥 LOGOUT -->
            <li>
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="sidebar-links-btn logout-btn">
                        Đăng xuất
                    </button>
                </form>
            </li>

        </ul>
    </aside>

    <!-- MAIN -->
    <main class="admin-main">
        @yield('content')
    </main>

</div>
</body>
</html>