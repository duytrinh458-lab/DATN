<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>

    <style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f4f6f9;
        padding: 30px;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #2c3e50;
    }

    form {
        background: #fff;
        padding: 25px;
        width: 700px;
        margin: auto;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* 🔥 CHIA 2 CỘT */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    label {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    input, select {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }

    .full {
        grid-column: span 2;
    }

    .preview-img {
        width: 120px;
        margin-top: 10px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    button {
        flex: 1;
        background: #3498db;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    .btn-cancel {
        flex: 1;
        text-align: center;
        background: #e74c3c;
        color: white;
        padding: 12px;
        border-radius: 6px;
        text-decoration: none;
    }

    .error {
        color: red;
        margin-bottom: 10px;
    }
</style>
</head>
<body>

<h2>Sửa sản phẩm</h2>

@if ($errors->any())
    <div class="error">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-grid">

        <div class="form-group">
            <label>Tên sản phẩm</label>
            <input type="text" name="name" value="{{ $product->name }}" required>
        </div>

        <div class="form-group">
            <label>Mã SKU</label>
            <input type="text" name="sku" value="{{ $product->sku }}" required>
        </div>

        <div class="form-group">
            <label>Giá bán</label>
            <input type="number" name="sale_price" value="{{ $product->sale_price }}" required>
        </div>

        <div class="form-group">
            <label>Giá gốc</label>
            <input type="number" name="original_price" value="{{ $product->original_price }}">
        </div>

        <div class="form-group">
            <label>Danh mục</label>
            <select name="category_id" required>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ $c->id == $product->category_id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Tồn kho</label>
            <input type="number" name="stock" value="{{ $product->stock }}">
        </div>

        <!-- ẢNH -->
        <div class="form-group full">
            <label>Ảnh hiện tại</label>
            @if($product->images->first())
                <img src="{{ asset($product->images->first()->image_url) }}" class="preview-img">
            @endif
        </div>

        <div class="form-group full">
            <label>Chọn ảnh mới</label>
            <input type="file" name="image1" onchange="previewImage(event)">
            <img id="preview" class="preview-img" style="display:none;">
        </div>

    </div>

    <div class="btn-group">
        <button type="submit">Cập nhật</button>

        <a href="{{ route('admin.products.index') }}" 
           class="btn-cancel"
           onclick="return confirm('Bạn có chắc muốn hủy chỉnh sửa không?');">
            Hủy
        </a>
    </div>

</form>

<script>
function previewImage(event) {
    const preview = document.getElementById('preview');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.style.display = 'block';
}
</script>

</body>
</html>