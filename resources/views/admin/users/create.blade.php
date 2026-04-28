<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm người dùng</title>

    <style>
    body {
        background: #0f172a;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }

    .wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        padding: 20px;
    }

    .title {
        color: #00e5ff;
        margin-bottom: 20px;
        font-size: 22px;
        font-weight: 600;
        text-align: center;
    }

    .error-box {
        background: rgba(255, 82, 82, 0.15);
        color: #ff5252;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
        width: 100%;
        max-width: 380px;
    }

    .form-box {
        width: 100%;
        max-width: 380px;
        background: #1e293b;
        padding: 20px;
        border-radius: 12px;
        color: #ffffff;
        box-shadow: 0 0 15px rgba(0,0,0,0.4);
    }

    .form-box label {
        display: block;
        margin-top: 10px;
        margin-bottom: 5px;
        color: #e2e8f0;
    }

    .form-box input {
        width: 95%;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #334155;
        background: #0f172a;
        color: #ffffff;
        font-size: 13px;
    }

    .form-box select {
        width: 100%;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #334155;
        background: #0f172a;
        color: #ffffff;
        font-size: 13px;
    }

    .form-box select option {
        background: #1e293b;
        color: #ffffff;
    }

    .form-box button {
        width: 100%;
        margin-top: 20px;
        background: #00bcd4;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    .form-box button:hover {
        background: #00e5ff;
    }
    </style>

</head>

<body>

<div class="wrapper">

    <h2 class="title">Thêm người dùng</h2>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}" class="form-box">
        @csrf

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Tên đầy đủ</label>
        <input type="text" name="full_name">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>SĐT</label>
        <input type="text" name="phone" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="customer">User</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Thêm user</button>
    </form>

</div>

</body>
</html>