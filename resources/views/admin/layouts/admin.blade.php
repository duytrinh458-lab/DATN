<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — UAV Store')</title>

    <link rel="stylesheet" href="{{ asset('Css/Admin/test-layout.css') }}">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
</head>

<body>

<aside class="admin-sidebar">

    <div class="sidebar-brand">
        <div class="logo-text">UAV ADMIN</div>
        <div class="logo-sub">Control Panel</div>
    </div>

    <div class="sidebar-menu">

        {{-- DASHBOARD --}}
        <div class="sidebar-dropdown">
            <button class="dropdown-toggle active">
                <span><i class="fa-solid fa-chart-line"></i> Tổng quan</span>
                <i class="fa-solid fa-chevron-down dropdown-icon"></i>
            </button>

            <div class="dropdown-menu show">
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
                <span><i class="fa-solid fa-layer-group"></i> Quản lý</span>
                <i class="fa-solid fa-chevron-down dropdown-icon"></i>
            </button>

            <div class="dropdown-menu">

                <a href="{{ route('admin.products.index') }}">
                    <div class="menu-left">
                        <i class="fa-solid fa-box-open"></i>
                        <span>Sản phẩm</span>
                    </div>
                </a>

                <a href="{{ route('admin.orders.index') }}">
                    <div class="menu-left">
                        <i class="fa-solid fa-box"></i>
                        <span>Đơn hàng</span>
                    </div>

                    <span class="menu-badge" id="badge-orders"></span>
                </a>

                <a href="{{ route('admin.refunds.index') }}">
                    <div class="menu-left">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Đơn hoàn trả</span>
                    </div>

                    <span class="menu-badge" id="badge-refunds"></span>
                </a>

                <a href="{{ route('admin.users.index') }}">
                    <div class="menu-left">
                        <i class="fa-solid fa-users"></i>
                        <span>Người dùng</span>
                    </div>
                </a>

            </div>
        </div>

        {{-- NỘI DUNG --}}
        <div class="sidebar-dropdown">
            <button class="dropdown-toggle">
                <span><i class="fa-solid fa-newspaper"></i> Nội dung</span>
            </button>

            <div class="dropdown-menu">

                <a href="{{ route('admin.news.index') }}">
                    <div class="menu-left">
                        <i class="fa-solid fa-newspaper"></i>
                        <span>Tin tức</span>
                    </div>

                    <span class="menu-badge" id="badge-news"></span>
                </a>

                <a href="{{ route('admin.interactions.comments') }}">
                    <div class="menu-left">
                        <i class="fa-solid fa-comments"></i>
                        <span>Bình luận</span>
                    </div>

                    <span class="menu-badge" id="badge-comments"></span>
                </a>

            </div>
        </div>

        {{-- TÀI CHÍNH --}}
        <div class="sidebar-dropdown">
            <button class="dropdown-toggle">
                <span><i class="fa-solid fa-wallet"></i> Tài chính</span>
            </button>

            <div class="dropdown-menu">

                <a href="{{ route('admin.transactions.index') }}">
                    <div class="menu-left">
                        <i class="fa-solid fa-wallet"></i>
                        <span>Giao dịch V-Pay</span>
                    </div>

                    <span class="menu-badge" id="badge-transactions"></span>
                </a>

            </div>
        </div>

    </div>

</aside>

<div class="admin-body">

    <header class="admin-topbar">
        <div class="topbar-title">@yield('title','Dashboard')</div>

        <div class="topbar-right">
            <div>{{ auth()->user()->full_name ?? 'Admin' }}</div>
        </div>
    </header>

    <main class="admin-main">
        @yield('content')
    </main>

</div>

<script>

/* =========================================
   DROPDOWN SIDEBAR
========================================= */
document.addEventListener('DOMContentLoaded', function () {

    const dropdowns = document.querySelectorAll('.sidebar-dropdown');

    dropdowns.forEach(dropdown => {

        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (!toggle || !menu) return;

        toggle.addEventListener('click', function () {

            // toggle active state
            toggle.classList.toggle('active');

            // toggle menu show/hide
            menu.classList.toggle('show');

        });

    });

});


/* =========================================
   BADGE UPDATE UI
========================================= */
function updateBadge(id, value)
{
    const el = document.getElementById(id);
    if (!el) return;

    if (value > 0) {
        el.style.display = 'inline-flex';
        el.innerText = value > 9 ? '9+' : value;
    } else {
        el.style.display = 'none';
    }
}


/* =========================================
   LOAD BADGES FROM API
========================================= */
function loadBadges()
{
    fetch("{{ url('/admin/badges') }}", {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {

        updateBadge('badge-orders', data.pendingOrders ?? 0);
        updateBadge('badge-refunds', data.pendingRefunds ?? 0);
        updateBadge('badge-comments', data.pendingComments ?? 0);
        updateBadge('badge-transactions', data.pendingTransactions ?? 0);
        updateBadge('badge-news', data.draftNews ?? 0);

    })
    .catch(error => {
        console.log('Badge load error:', error);
    });
}


/* =========================================
   INIT
========================================= */
document.addEventListener('DOMContentLoaded', function () {

    loadBadges();

    // refresh mỗi 5 giây
    setInterval(loadBadges, 5000);

});

</script>

</body>
</html>