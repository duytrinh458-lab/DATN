<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>

    <style>
        body { font-family: Arial; background:#f5f6fa; padding:30px; }
        form {
            background:white;
            padding:20px;
            width:400px;
            margin:auto;
            box-shadow:0 2px 8px rgba(0,0,0,0.1);
        }
        input, select {
            width:100%;
            padding:10px;
            margin:10px 0;
        }
        button {
            background:#3498db;
            color:white;
            padding:10px;
            border:none;
            width:100%;
        }
    </style>
</head>
<body>

<h2>Sửa sản phẩm</h2>

<form action="{{ route('products.update', $product->id) }}" method="POST">
    @csrf
    @method('PUT')

    <input type="text" name="name" value="{{ $product->name }}" required>

    <input type="number" name="sale_price" value="{{ $product->sale_price }}" required>

    <select name="category_id">
        @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ $c->id == $product->category_id ? 'selected' : '' }}>
                {{ $c->name }}
            </option>
        @endforeach
    </select>

    <button>Cập nhật</button>
</form>

</body>
</html>