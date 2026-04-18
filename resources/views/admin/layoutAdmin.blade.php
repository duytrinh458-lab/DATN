<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin UAV</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            display: flex;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #1e293b;
            color: white;
            padding: 20px;
        }
        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            display: block;
            color: #cbd5e1;
            padding: 10px;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background: #334155;
            color: white;
        }
        .main {
            flex: 1;
            background: #f1f5f9;
        }
        .header {
            background: white;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4>🚁 ADMIN UAV</h4>
    <a href="/admin">Dashboard</a>
    <a href="/admin/products">Quản lý UAV</a>
    <a href="#">Đơn hàng</a>
    <a href="#">Người dùng</a>
</div>

<!-- Main -->
<div class="main">
    <div class="header">
        <strong>Trang quản trị</strong>
    </div>

    <div class="content">
        @yield('content')
    </div>
</div>

</body>
</html>