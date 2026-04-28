<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý người dùng</title>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        margin: 0;
        padding: 20px;
        color: white;
    }

    h2 {
        color: #00e5ff;
        margin-bottom: 20px;
    }

    .success {
        color: #00e676;
        margin-bottom: 15px;
        font-weight: bold;
    }

    .table-container {
        background: rgba(0,0,0,0.4);
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #00bcd4;
    }

    th {
        padding: 12px;
        text-align: left;
        color: white;
    }

    td {
        padding: 10px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        color: #ffffff;
    }

    tr:hover {
        background: rgba(255,255,255,0.08);
    }

    .center {
        text-align: center;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 6px;
        color: white;
        font-size: 13px;
    }

    .role-admin {
        background: #ff5252;
    }

    .role-user {
        background: #00c853;
    }

    .status-active {
        background: #00c853;
    }

    .status-inactive {
        background: #9e9e9e;
    }

    .btn {
        display: inline-block;
        background: #00bcd4;
        padding: 6px 12px;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        transition: 0.3s;
    }

    .btn:hover {
        background: #00e5ff;
    }

    /* 🔥 Nút thêm user góc phải */
    .btn-add {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #00c853;
        padding: 12px 18px;
        border-radius: 50px;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .btn-add:hover {
        background: #00e676;
    }
</style>
</head>

<body>

<h2>Quản lý người dùng</h2>

@if(session('success'))
    <p class="success">{{ session('success') }}</p>
@endif

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Email</th>
                <th>SĐT</th>
                <th class="center">Role</th>
                <th class="center">Trạng thái</th>
                <th class="center">Action</th>
            </tr>
        </thead>

        <tbody>
        @foreach($users as $user)
            <tr>
                <td>#{{ $user->id }}</td>
                <td>{{ $user->full_name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone }}</td>

                <td class="center">
                    <span class="badge {{ $user->role == 'admin' ? 'role-admin' : 'role-user' }}">
                        {{ $user->role }}
                    </span>
                </td>

                <td class="center">
                    <span class="badge {{ $user->status == 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ $user->status }}
                    </span>
                </td>

                <td class="center">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn">
                        Xem
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!--  Nút thêm user -->
<a href="{{ route('admin.users.create') }}" class="btn btn-add">
    + Thêm user
</a>

</body>
</html>