<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>

    <style>
        body { font-family: Arial; background:#f5f6fa; padding:30px; }
        form {
            background:white;
            padding:20px;
            width:400px;
            margin:auto;
            box-shadow:0 2px 8px rgba(0,0,0,0.1);
        }
        input {
            width:95%;
            padding:10px;
            margin:10px 0;
        }

        select {
            width:101%;
            padding:10px;
            margin:10px 0;
        }

        button {
            background:#2ecc71;
            color:white;
            padding:10px;
            border:none;
            width:100%;
        }
    </style>
</head>
<body>

<h2>Thêm sản phẩm</h2>

<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <input type="text" name="name" placeholder="Tên sản phẩm" required>

    <input type="number" name="sale_price" placeholder="Giá" required>

    <select name="category_id" required>
        <option value="">-- Chọn danh mục --</option>
        @foreach($categories as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
        @endforeach
    </select>

    <input type="file" name="image1">

    <button>Thêm sản phẩm</button>
</form>

</body>
</html>