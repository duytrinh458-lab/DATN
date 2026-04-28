<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết user</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            margin: 0;
            padding: 0;
        }

        .user-card {
            max-width: 500px;
            margin: 60px auto;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            color: #e0f7fa;
        }

        h2 {
            text-align: center;
            color: #00e5ff;
            margin-bottom: 15px;
        }

        p {
            margin: 8px 0;
        }

        hr {
            border: 1px solid rgba(255,255,255,0.1);
            margin: 15px 0;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        select {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: none;
            outline: none;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-update {
            background: #00bcd4;
        }

        .btn-update:hover {
            background: #00e5ff;
        }

        .btn-delete {
            background: #ff5252;
            margin-top: 10px;
        }

        .btn-delete:hover {
            background: #ff1744;
        }

        /* NÚT QUAY LẠI */
        .btn-back {
            display: block;
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.1);
            color: #1bff60;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: rgba(255, 0, 0, 0.2);
        }
    </style>
</head>

<body>

<div class="user-card">

    <h2>Chi tiết user #{{ $user->id }}</h2>

    <!-- NÚT QUAY LẠI -->
    <a href="{{ route('admin.users.index') }}" class="btn-back">
        ← Quay lại danh sách
    </a>

    <p><b>Tên:</b> {{ $user->full_name }}</p>
    <p><b>Email:</b> {{ $user->email }}</p>
    <p><b>SĐT:</b> {{ $user->phone }}</p>

    <hr>

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
        @csrf

        <label>Role:</label>
        <select name="role">
            <option value="customer" {{ $user->role=='customer'?'selected':'' }}>User</option>
            <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
        </select>

        <label>Trạng thái:</label>
        <select name="status">
            <option value="active" {{ $user->status=='active'?'selected':'' }}>Active</option>
            <option value="inactive" {{ $user->status=='inactive'?'selected':'' }}>Khoá</option>
        </select>

        <button type="submit" class="btn btn-update">Cập nhật</button>
    </form>

    <form method="POST" action="{{ route('admin.users.delete', $user->id) }}">
        @csrf
        <button onclick="return confirm('Xoá user?')" class="btn btn-delete">
            Xoá user
        </button>
    </form>

</div>

</body>
</html>