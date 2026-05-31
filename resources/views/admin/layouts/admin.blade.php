<!DOCTYPE html>
<html lang="vi">
<head>

    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin — UAV Store')</title>

    <link rel="stylesheet"
          href="{{ asset('Css/Admin/admin-layout.css') }}">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')

</head>

<body>

{{-- SIDEBAR --}}
<aside class="admin-sidebar">

    {{-- BRAND --}}
    <div class="sidebar-brand">

        <div class="logo-text">
            UAV ADMIN
        </div>

        <div class="logo-sub">
            CONTROL PANEL
        </div>

    </div>

    {{-- MENU --}}
    <div class="sidebar-menu">

        {{-- DASHBOARD --}}
        <div class="sidebar-dropdown">

            <button class="dropdown-toggle">

                <span>
                    <i class="fa-solid fa-chart-line"></i>
                    Tổng quan
                </span>

                <i class="fa-solid fa-chevron-down dropdown-icon"></i>

            </button>

            <div class="dropdown-menu">

                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">

                    <div class="menu-left">

                        <i class="fa-solid fa-gauge-high"></i>

                        <span>Dashboard</span>

                    </div>

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

                {{-- SẢN PHẨM --}}
                <a href="{{ route('admin.products.index') }}"
                   class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">

                    <div class="menu-left">

                        <i class="fa-solid fa-box-open"></i>

                        <span>Sản phẩm</span>

                    </div>

                    <span class="menu-badge"
                          id="badge-products"></span>

                </a>


                {{-- ĐƠN HÀNG --}}
                <a href="{{ route('admin.orders.index') }}"
                   class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">

                    <div class="menu-left">

                        <i class="fa-solid fa-box"></i>

                        <span>Đơn hàng</span>

                    </div>

                    <span class="menu-badge"
                          id="badge-orders"></span>

                </a>


                {{-- HOÀN TRẢ --}}
                <a href="{{ route('admin.refunds.index') }}"
                   class="{{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}">

                    <div class="menu-left">

                        <i class="fa-solid fa-rotate-left"></i>

                        <span>Đơn hoàn trả</span>

                    </div>

                    <span class="menu-badge"
                          id="badge-refunds"></span>

                </a>


                {{-- USERS --}}
                <a href="{{ route('admin.users.index') }}"
                   class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">

                    <div class="menu-left">

                        <i class="fa-solid fa-users"></i>

                        <span>Người dùng</span>

                    </div>

                </a>

                {{-- THƯƠNG HIỆU --}}
<a href="{{ route('admin.brands.index') }}"
   class="{{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">

    <div class="menu-left">

        <i class="fa-solid fa-tags"></i>

        <span>Thương hiệu</span>

    </div>

</a>

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

                    <div class="menu-left">

                        <i class="fa-solid fa-newspaper"></i>

                        <span>Tin tức</span>

                    </div>

                    <span class="menu-badge"
                          id="badge-news"></span>

                </a>

                <a href="{{ route('admin.interactions.comments') }}"
                   class="{{ request()->routeIs('admin.interactions.*') ? 'active' : '' }}">

                    <div class="menu-left">

                        <i class="fa-solid fa-comments"></i>

                        <span>Bình luận</span>

                    </div>

                    <span class="menu-badge"
                          id="badge-comments"></span>

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

                {{-- QR BANK --}}
                <a href="{{ route('admin.qr.index') }}"
                   class="{{ request()->routeIs('admin.qr.*') ? 'active' : '' }}">

                    <div class="menu-left">

                        <i class="fa-solid fa-qrcode"></i>

                        <span>Cài QR Bank</span>

                    </div>

                </a>

                {{-- TRANSACTIONS --}}
                <a href="{{ route('admin.transactions.index') }}"
                   class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">

                    <div class="menu-left">

                        <i class="fa-solid fa-wallet"></i>

                        <span>Giao dịch V-Pay</span>

                    </div>

                    <span class="menu-badge"
                          id="badge-transactions"></span>

                </a>

            </div>

        </div>

    </div>


    {{-- LOGOUT --}}
    <div class="sidebar-footer">

        <form action="{{ route('logout') }}"
              method="POST">

            @csrf

            <button type="submit"
                    class="sidebar-logout-btn">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Đăng xuất</span>

            </button>

        </form>

    </div>

</aside>


{{-- MAIN --}}
<div class="admin-body">

    {{-- TOPBAR --}}
    <header class="admin-topbar">

        <div class="topbar-title">

            @yield('title', 'Dashboard')

        </div>

        <div class="topbar-right">

            <div class="admin-user">

                {{ auth()->user()->full_name ?? 'Admin' }}

            </div>

        </div>

    </header>

    {{-- CONTENT --}}
    <main class="admin-main">

        @yield('content')

    </main>

</div>


<script>

/* =========================================
   ADMIN SIDEBAR
========================================= */
document.addEventListener('DOMContentLoaded', function () {

    const dropdowns =
        document.querySelectorAll('.sidebar-dropdown');

    dropdowns.forEach(function (dropdown) {

        const toggle =
            dropdown.querySelector('.dropdown-toggle');

        const menu =
            dropdown.querySelector('.dropdown-menu');

        if (!toggle || !menu) return;

        // tự mở nếu có menu active
        const activeLink =
            menu.querySelector('a.active');

        if (activeLink) {

            toggle.classList.add('active');
            menu.classList.add('show');

        }

        // click dropdown
        toggle.addEventListener('click', function () {

            const isOpen =
                menu.classList.contains('show');

            // đóng dropdown khác
            dropdowns.forEach(function (otherDropdown) {

                if (otherDropdown === dropdown) return;

                const otherToggle =
                    otherDropdown.querySelector('.dropdown-toggle');

                const otherMenu =
                    otherDropdown.querySelector('.dropdown-menu');

                if (otherToggle && otherMenu) {

                    otherToggle.classList.remove('active');
                    otherMenu.classList.remove('show');

                }

            });

            // toggle current
            if (isOpen) {

                toggle.classList.remove('active');
                menu.classList.remove('show');

            } else {

                toggle.classList.add('active');
                menu.classList.add('show');

            }

        });

    });


    /* =========================================
       CREATE PARENT BADGE
    ========================================= */
    function createParentBadge(toggle)
    {
        let badge =
            toggle.querySelector('.parent-badge');

        if (!badge) {

            badge =
                document.createElement('span');

            badge.className =
                'menu-badge parent-badge';

            toggle.appendChild(badge);

        }

        return badge;
    }


    /* =========================================
       UPDATE BADGE
    ========================================= */
    function updateBadge(id, value)
    {
        const el =
            document.getElementById(id);

        if (!el) return;

        value = parseInt(value) || 0;

        if (value > 0) {

            el.style.display = 'inline-flex';

            el.innerText =
                value > 9 ? '9+' : value;

        } else {

            el.style.display = 'none';

            el.innerText = '';

        }
    }


    /* =========================================
       UPDATE PARENT BADGES
    ========================================= */
    function updateParentBadges()
    {
        dropdowns.forEach(function (dropdown) {

            const toggle =
                dropdown.querySelector('.dropdown-toggle');

            const badges =
                dropdown.querySelectorAll('.dropdown-menu .menu-badge');

            let total = 0;

            badges.forEach(function (badge) {

                const value =
                    parseInt(badge.innerText) || 0;

                total += value;

            });

            const parentBadge =
                createParentBadge(toggle);

            if (total > 0) {

                parentBadge.style.display =
                    'inline-flex';

                parentBadge.innerText =
                    total > 99 ? '99+' : total;

            } else {

                parentBadge.style.display =
                    'none';

                parentBadge.innerText = '';

            }

        });
    }


    /* =========================================
       LOAD BADGES
    ========================================= */
    function loadBadges()
    {
        fetch("{{ url('/admin/badges') }}", {

            method: 'GET',

            headers: {

                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'

            }

        })
        .then(function (response) {

            if (!response.ok) {
                throw new Error('HTTP error');
            }

            return response.json();

        })
        .then(function (data) {

            updateBadge(
                'badge-products',
                data.lowStockProducts
            );

            updateBadge(
                'badge-orders',
                data.pendingOrders
            );

            updateBadge(
                'badge-refunds',
                data.pendingRefunds
            );

            updateBadge(
                'badge-comments',
                data.pendingComments
            );

            updateBadge(
                'badge-transactions',
                data.pendingTransactions
            );

            updateBadge(
                'badge-news',
                data.draftNews
            );

            // update badge menu cha
            updateParentBadges();

        })
        .catch(function (error) {

            console.log(
                'Badge load error:',
                error
            );

        });
    }


    /* =========================================
       INIT
    ========================================= */
    loadBadges();

    setInterval(loadBadges, 5000);

});

</script>

</body>
</html>