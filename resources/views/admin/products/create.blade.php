@extends('Admin.layouts.admin')

@section('title', 'Thêm sản phẩm mới')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-product.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<div class="product-create">
    <div class="page-header">
        <h1><i class="fas fa-drone"></i> Thêm sản phẩm UAV mới</h1>
        <div class="header-actions">
            <a href="{{ route('admin.products.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Về trang danh sách
            </a>
        </div>
    </div>

    <div class="form-container">
        @if ($errors->any())
            <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra:</strong>
                <ul style="margin-top: 5px; list-style-position: inside;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div style="background: #fef2f2; border-left: 5px solid #dc2626; color: #991b1b; padding: 15px; margin-bottom: 20px;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="product-form">
            @csrf

    <div class="form-grid">

        {{-- ================= TÊN SẢN PHẨM ================= --}}
        <div class="form-group">

            <label for="name">
                Tên sản phẩm <span class="required">*</span>
            </label>

            <div class="input-group">

                <i class="fas fa-helicopter"></i>

                <input type="text"
                       id="name"
                       name="name"
                       class="form-control"
                       value="{{ old('name') }}"
                       required
                       placeholder="VD: DJI Mavic 3 Pro">

            </div>

        </div>

        {{-- ================= DANH MỤC ================= --}}
        <div class="form-group">

            <label for="category_id">
                Danh mục <span class="required">*</span>
            </label>

            <div class="input-group">

                <i class="fas fa-list"></i>

                <select id="category_id"
                        name="category_id"
                        class="form-control"
                        required>

                    <option value="">
                        -- Chọn danh mục --
                    </option>

                    @foreach($categories as $category)

                        <option value="{{ $category->id }}"
                            {{ old('category_id') == $category->id ? 'selected' : '' }}>

                            {{ $category->name }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

        {{-- ================= THƯƠNG HIỆU ================= --}}
        <div class="form-group">

            <label for="brand_id">
                Thương hiệu
            </label>

            <div class="input-group">

                <i class="fas fa-copyright"></i>

                <select id="brand_id"
                        name="brand_id"
                        class="form-control">

                    <option value="">
                        -- Chọn thương hiệu --
                    </option>

                    @foreach($brands as $brand)

                        <option value="{{ $brand->id }}"
                            {{ old('brand_id') == $brand->id ? 'selected' : '' }}>

                            {{ $brand->name }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

        {{-- ================= SKU ================= --}}
        <div class="form-group">

            <label for="sku">
                Mã SKU <span class="required">*</span>
            </label>

            <div class="input-group">

                <i class="fas fa-barcode"></i>

                <input type="text"
                       id="sku"
                       name="sku"
                       class="form-control"
                       value="{{ old('sku') }}"
                       required
                       placeholder="VD: DJI-MAVIC3-001">

            </div>

        </div>

        {{-- ================= GIÁ GỐC ================= --}}
        <div class="form-group">

            <label for="original_price">
                Giá gốc (VNĐ) <span class="required">*</span>
            </label>

            <div class="input-group">

                <i class="fas fa-tags"></i>

                <input type="number"
                       id="original_price"
                       name="original_price"
                       class="form-control"
                       value="{{ old('original_price') }}"
                       required
                       min="0">

            </div>

        </div>

        {{-- ================= GIÁ BÁN ================= --}}
        <div class="form-group">

            <label for="sale_price">
                Giá bán (VNĐ) <span class="required">*</span>
            </label>

            <div class="input-group">

                <i class="fas fa-tag"></i>

                <input type="number"
                       id="sale_price"
                       name="sale_price"
                       class="form-control"
                       value="{{ old('sale_price') }}"
                       required
                       min="0">

            </div>

        </div>

        {{-- ================= TỒN KHO ================= --}}
        <div class="form-group">

            <label for="stock">
                Số lượng tồn kho
            </label>

            <div class="input-group">

                <i class="fas fa-boxes"></i>

                <input type="number"
                       id="stock"
                       name="stock"
                       class="form-control"
                       value="{{ old('stock', 1) }}"
                       min="1">

            </div>

        </div>

        {{-- ================= SẢN PHẨM NỔI BẬT ================= --}}
        <div class="form-group">

            <label for="is_featured">
                Sản phẩm nổi bật
            </label>

            <div class="input-group">

                <i class="fas fa-star"></i>

                <select id="is_featured"
                        name="is_featured"
                        class="form-control">

                    <option value="0"
                        {{ old('is_featured') == 0 ? 'selected' : '' }}>
                        Không
                    </option>

                    <option value="1"
                        {{ old('is_featured') == 1 ? 'selected' : '' }}>
                        Có
                    </option>

                </select>

            </div>

        </div>

        {{-- ================= TRẠNG THÁI ================= --}}
        <div class="form-group">

            <label for="status">
                Trạng thái
            </label>

            <div class="input-group">

                <i class="fas fa-signal"></i>

                <select id="status"
                        name="status"
                        class="form-control">

                    <option value="active"
                        {{ old('status') == 'active' ? 'selected' : '' }}>
                        Đang bán
                    </option>

                    <option value="out_of_stock"
                        {{ old('status') == 'out_of_stock' ? 'selected' : '' }}>
                        Hết hàng
                    </option>

                    <option value="inactive"
                        {{ old('status') == 'inactive' ? 'selected' : '' }}>
                        Ngừng bán
                    </option>

                </select>

            </div>

        </div>

        {{-- ================= THỜI GIAN BAY ================= --}}
        <div class="form-group">

            <label for="flight_time">
                Thời gian bay (phút)
            </label>

            <div class="input-group">

                <i class="fas fa-clock"></i>

                <input type="number"
                       id="flight_time"
                       name="flight_time"
                       class="form-control"
                       value="{{ old('flight_time') }}"
                       min="0"
                       step="0.1">

            </div>

        </div>

        {{-- ================= ĐỘ CAO TỐI ĐA ================= --}}
        <div class="form-group">

            <label for="max_altitude">
                Độ cao tối đa (m)
            </label>

            <div class="input-group">

                <i class="fas fa-mountain"></i>

                <input type="number"
                       id="max_altitude"
                       name="max_altitude"
                       class="form-control"
                       value="{{ old('max_altitude') }}"
                       min="0"
                       step="0.1">

            </div>

        </div>

        {{-- ================= CAMERA ================= --}}
        <div class="form-group">

            <label for="camera_mp">
                Camera (MP)
            </label>

            <div class="input-group">

                <i class="fas fa-camera"></i>

                <input type="number"
                       id="camera_mp"
                       name="camera_mp"
                       class="form-control"
                       value="{{ old('camera_mp') }}"
                       min="0"
                       step="0.1">

            </div>

        </div>

        {{-- ================= TẦN SỐ ================= --}}
        <div class="form-group">

            <label for="frequency">
                Tần số điều khiển
            </label>

            <div class="input-group">

                <i class="fas fa-wave-square"></i>

                <input type="text"
                       id="frequency"
                       name="frequency"
                       class="form-control"
                       value="{{ old('frequency') }}"
                       placeholder="VD: 2.4GHz">

            </div>

        </div>

        {{-- ================= TRỌNG LƯỢNG ================= --}}
        <div class="form-group">

            <label for="weight">
                Trọng lượng (kg)
            </label>

            <div class="input-group">

                <i class="fas fa-weight-hanging"></i>

                <input type="number"
                       id="weight"
                       name="weight"
                       class="form-control"
                       value="{{ old('weight') }}"
                       min="0"
                       step="0.1">

            </div>

        </div>

        {{-- ================= ẢNH ================= --}}
        <div class="form-group full-width">

            <label for="image1">
                Ảnh sản phẩm <span class="required">*</span>
            </label>

            <div class="file-upload">

                <input type="file"
                       id="image1"
                       name="image1"
                       class="form-control"
                       required
                       accept="image/*">

                <div class="file-preview">

                    <i class="fas fa-cloud-upload-alt"></i>

                    <p>
                        Chọn ảnh JPG, PNG (Tối đa 2MB)
                    </p>

                </div>

            </div>

        </div>

        {{-- ================= MÔ TẢ ================= --}}
        <div class="form-group full-width">

            <label for="description">
                Mô tả sản phẩm
            </label>

            <textarea id="description"
                      name="description"
                      class="form-control"
                      rows="6"
                      placeholder="Nhập mô tả chi tiết sản phẩm...">{{ old('description') }}</textarea>

        </div>

    </div>

    {{-- ================= ACTIONS ================= --}}
    <div class="form-actions">

        <button type="submit" class="btn btn-primary">

            <i class="fas fa-save"></i>

            Lưu sản phẩm

        </button>

        <a href="{{ route('admin.products.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-times"></i>

            Hủy bỏ

        </a>

    </div>

</form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chỉ giữ lại Preview ảnh
    const imageInput = document.getElementById('image1');
    const filePreview = document.querySelector('.file-preview');
    
    if (imageInput && filePreview) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    filePreview.innerHTML = `<img src="${e.target.result}" style="max-height: 200px; border-radius: 8px;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    // Đã xóa bỏ hoàn toàn đoạn format giá tiền gây lỗi gửi dấu chấm về Server
});
</script>
@endsection