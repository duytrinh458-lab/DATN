<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            margin: 0;
            padding: 30px;
            color: #e0f7fa;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }

        h2 {
            color: #00e5ff;
            margin-bottom: 15px;
        }

        .status {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .status b {
            color: #00e5ff;
        }

        select {
            padding: 8px;
            border-radius: 6px;
            border: none;
            outline: none;
        }

        button {
            padding: 8px 15px;
            background: #00bcd4;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-left: 10px;
            transition: 0.3s;
        }

        button:hover {
            background: #00e5ff;
        }

        hr {
            border: 1px solid rgba(255,255,255,0.1);
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 10px;
        }

        th {
            background: rgba(0, 188, 212, 0.3);
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        tr:hover {
            background: rgba(255,255,255,0.05);
        }

        .back {
            display: inline-block;
            margin-bottom: 15px;
            color: #4caf50;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
            color: #66bb6a;
        }
    </style>
</head>
<body>

<div class="container">

    <a href="{{ route('admin.orders') }}" class="back">← Quay lại</a>

    <h2>Chi tiết đơn #{{ $order->id }}</h2>

    <p class="status">Trạng thái: <b>{{ $order->status }}</b></p>

    <form method="POST" action="{{ route('admin.orders.update', $order->id) }}">
        @csrf

        <select name="status">
            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
            <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Đang giao</option>
            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Đã huỷ</option>
        </select>

        <button type="submit">Cập nhật</button>
    </form>

    <hr>

    <table>
        <tr>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Giá</th>
        </tr>

        @foreach($items as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->price) }}đ</td>
        </tr>
        @endforeach
    </table>

</div>

</body>
</html>