<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin - Vanguard UAV')</title>
    <link rel="stylesheet" href="{{ asset('Css/admin/admin.css') }}">
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="logo">Cửa Hàng UAV</div>
            <ul class="sidebar-links">
                <li><a href="{{ url('/admin') }}">Trang Chủ</a></li>
                <li><a href="{{ url('/admin/products') }}">Sản phẩm</a></li>
                <li><a href="{{ url('/admin/orders') }}">Đơn hàng</a></li>
                <li><a href="{{ url('/admin/users') }}">Người dùng</a></li>
            </ul>
        </aside>

        <!-- Nội dung chính -->
        <main class="admin-main">
            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer class="admin-footer">
        <p>© 2026 Của Hàng UAV Admin Panel</p>
    </footer>
</body>


</html>


