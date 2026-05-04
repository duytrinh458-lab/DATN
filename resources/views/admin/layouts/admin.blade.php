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
        <a href="{{ url('/admin') }}"
           class="{{ request()->is('admin') ? 'active' : '' }}">
            Trang Chủ
        </a>
    </li>

    <li>
        <a href="{{ url('/admin/products') }}"
           class="{{ request()->is('admin/products*') ? 'active' : '' }}">
            Sản phẩm
        </a>
    </li>

    <li>
        <a href="{{ url('/admin/orders') }}"
           class="{{ request()->is('admin/orders*') ? 'active' : '' }}">
            Đơn hàng
        </a>
    </li>

    <li>
        <a href="{{ url('/admin/users') }}"
           class="{{ request()->is('admin/users*') ? 'active' : '' }}">
            Người dùng
        </a>
    </li>

    <li>
        <a href="{{ url('/admin/categories') }}"
           class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
            Danh Mục
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