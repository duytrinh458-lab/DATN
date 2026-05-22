@extends('Admin.layouts.admin')

@section('title', 'Sửa sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-product.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')

<div class="product-create">

    {{-- ================= HEADER ================= --}}
    <div class="page-header">

        <h1>
            <i class="fas fa-pen"></i>
            Chỉnh sửa sản phẩm UAV
        </h1>

        <div class="header-actions">

            <a href="{{ route('admin.products.index') }}"
               class="btn-back">

                <i class="fas fa-arrow-left"></i>

                Quay lại

            </a>

        </div>

    </div>

    {{-- ================= FORM CONTAINER ================= --}}
    <div class="form-container">

        {{-- ================= ERRORS ================= --}}
        @if ($errors->any())

            <div style="
                background:#fee2e2;
                border:1px solid #ef4444;
                color:#b91c1c;
                padding:15px;
                border-radius:10px;
                margin-bottom:20px;
            ">

                <strong>
                    <i class="fas fa-exclamation-triangle"></i>
                    Có lỗi xảy ra:
                </strong>

                <ul style="margin-top:8px; padding-left:18px;">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        {{-- ================= FORM ================= --}}
        <form action="{{ route('admin.products.update', $product->id) }}"
              method="POST"
              enctype="multipart/form-data"
              class="product-form">

            @csrf
            @method('PUT')

            <div class="form-grid">

                {{-- ================= TÊN ================= --}}
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
                               value="{{ old('name', $product->name) }}"
                               required>

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
                               value="{{ old('sku', $product->sku) }}"
                               required>

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

                            @foreach($categories as $c)

                                <option value="{{ $c->id }}"
                                    {{ $c->id == $product->category_id ? 'selected' : '' }}>

                                    {{ $c->name }}

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
                                    {{ $brand->id == $product->brand_id ? 'selected' : '' }}>

                                    {{ $brand->name }}

                                </option>

                            @endforeach

                        </select>

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
                               value="{{ old('sale_price', $product->sale_price) }}"
                               required>

                    </div>

                </div>

                {{-- ================= GIÁ GỐC ================= --}}
                <div class="form-group">

                    <label for="original_price">
                        Giá gốc (VNĐ)
                    </label>

                    <div class="input-group">

                        <i class="fas fa-tags"></i>

                        <input type="number"
                               id="original_price"
                               name="original_price"
                               class="form-control"
                               value="{{ old('original_price', $product->original_price) }}">

                    </div>

                </div>

                {{-- ================= TỒN KHO ================= --}}
                <div class="form-group">

                    <label for="stock">
                        Tồn kho
                    </label>

                    <div class="input-group">

                        <i class="fas fa-boxes"></i>

                        <input type="number"
                               id="stock"
                               name="stock"
                               class="form-control"
                               value="{{ old('stock', $product->stock) }}">

                    </div>

                </div>

                {{-- ================= FEATURED ================= --}}
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
                                {{ $product->is_featured == 0 ? 'selected' : '' }}>
                                Không
                            </option>

                            <option value="1"
                                {{ $product->is_featured == 1 ? 'selected' : '' }}>
                                Có
                            </option>

                        </select>

                    </div>

                </div>

                {{-- ================= STATUS ================= --}}
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
                                {{ $product->status == 'active' ? 'selected' : '' }}>
                                Đang bán
                            </option>

                            <option value="out_of_stock"
                                {{ $product->status == 'out_of_stock' ? 'selected' : '' }}>
                                Hết hàng
                            </option>

                            <option value="inactive"
                                {{ $product->status == 'inactive' ? 'selected' : '' }}>
                                Ngừng bán
                            </option>

                        </select>

                    </div>

                </div>

                {{-- ================= FLIGHT TIME ================= --}}
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
                               step="0.1"
                               value="{{ old('flight_time', $product->flight_time) }}">

                    </div>

                </div>

                {{-- ================= ALTITUDE ================= --}}
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
                               step="0.1"
                               value="{{ old('max_altitude', $product->max_altitude) }}">

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
                               step="0.1"
                               value="{{ old('camera_mp', $product->camera_mp) }}">

                    </div>

                </div>

                {{-- ================= FREQUENCY ================= --}}
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
                               value="{{ old('frequency', $product->frequency) }}">

                    </div>

                </div>

                {{-- ================= WEIGHT ================= --}}
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
                               step="0.1"
                               value="{{ old('weight', $product->weight) }}">

                    </div>

                </div>

                {{-- ================= ẢNH HIỆN TẠI ================= --}}
                <div class="form-group full-width">

                    <label>
                        Ảnh hiện tại
                    </label>

                    @if($product->images->first())

                        <img src="{{ asset($product->images->first()->image_url) }}"
                             id="preview"
                             style="
                                width:180px;
                                border-radius:14px;
                                border:1px solid #ddd;
                                margin-top:10px;
                             ">

                    @endif

                </div>

                {{-- ================= ẢNH MỚI ================= --}}
                <div class="form-group full-width">

                    <label for="image1">
                        Chọn ảnh mới
                    </label>

                    <div class="file-upload">

                        <input type="file"
                               id="image1"
                               name="image1"
                               class="form-control"
                               accept="image/*"
                               onchange="previewImage(event)">

                    </div>

                </div>

                {{-- ================= DESCRIPTION ================= --}}
                <div class="form-group full-width">

                    <label for="description">
                        Mô tả sản phẩm
                    </label>

                    <textarea id="description"
                              name="description"
                              class="form-control"
                              rows="6"
                              placeholder="Nhập mô tả chi tiết sản phẩm...">{{ old('description', $product->description) }}</textarea>

                </div>

            </div>

            {{-- ================= ACTIONS ================= --}}
            <div class="form-actions">

                <button type="submit" class="btn btn-primary">

                    <i class="fas fa-save"></i>

                    Cập nhật sản phẩm

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
function previewImage(event)
{
    const preview = document.getElementById('preview');

    preview.src = URL.createObjectURL(event.target.files[0]);

    preview.style.display = 'block';
}
</script>

@endsection