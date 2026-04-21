<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f6fa;
            padding: 30px;
        }

        h1 {
            margin-bottom: 20px;
        }

        a {
            text-decoration: none;
        }

        .btn-add {
            display: inline-block;
            padding: 10px 15px;
            background: #2ecc71;
            color: white;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .btn-edit {
            color: #2980b9;
            margin-right: 10px;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 10px;
        }

        td {
            padding: 10px;
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:hover {
            background: #ecf0f1;
        }
    </style>
</head>
<body>

<h1>Quản lý sản phẩm</h1>

<a href="{{ route('products.create') }}" class="btn-add">+ Thêm sản phẩm</a>

<table>
    <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Giá</th>
        <th>Hành động</th>
    </tr>

    @foreach($products as $p)
    <tr>
        <td>{{ $p->id }}</td>
        <td>{{ $p->name }}</td>
        <td>{{ number_format($p->sale_price) }} đ</td>
        <td>
            <a href="{{ route('products.edit', $p->id) }}" class="btn-edit">Sửa</a>

            <form action="{{ route('products.destroy', $p->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button class="btn-delete" onclick="return confirm('Xoá sản phẩm?')">
                    Xoá
                </button>
            </form>
        </td>
    </tr>
    @endforeach
</table>

</body>
</html>