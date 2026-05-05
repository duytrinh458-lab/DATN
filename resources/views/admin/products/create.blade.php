@extends('Admin.layouts.admin')

@section('title', 'Thêm sản phẩm mới')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/create.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<div class="product-create">
    <div class="page-header">
        <h1><i class="fas fa-drone"></i> Thêm sản phẩm UAV mới</h1>
        <div class="header-actions">
            <a href="{{ route('admin.products.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Danh sách
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
                <div class="form-group">
                    <label for="name">Tên máy bay <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-helicopter"></i>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="{{ old('name') }}" required placeholder="VD: DJI Mavic 3 Pro">
                    </div>
                </div>

                <div class="form-group">
                    <label for="category_id">Danh mục <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-list"></i>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sale_price">Giá bán (VNĐ) <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-tag"></i>
                        <input type="number" id="sale_price" name="sale_price" 
                               class="form-control" 
                               value="{{ old('sale_price') }}" required min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="original_price">Giá gốc (VNĐ)</label>
                    <div class="input-group">
                        <i class="fas fa-tags"></i>
                        <input type="number" id="original_price" name="original_price" 
                               class="form-control" 
                               value="{{ old('original_price') }}" min="1">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="image1">Ảnh chính <span class="required">*</span></label>
                    <div class="file-upload">
                        <input type="file" id="image1" name="image1" 
                               class="form-control" required accept="image/*">
                        <div class="file-preview">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Chọn ảnh JPG, PNG (Tối đa 2MB)</p>
                        </div>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Mô tả ngắn</label>
                    <textarea name="description" class="form-control" 
                              rows="4" placeholder="Mô tả ngắn gọn về sản phẩm...">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="stock">Số lượng tồn kho</label>
                    <div class="input-group">
                        <i class="fas fa-boxes"></i>
                        <input type="number" id="stock" name="stock" class="form-control" 
                               value="{{ old('stock', 1) }}" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="flight_time">Thời gian bay (phút)</label>
                    <div class="input-group">
                        <i class="fas fa-clock"></i>
                        <input type="number" id="flight_time" name="flight_time" class="form-control" 
                               value="{{ old('flight_time') }}" min="0" step="0.5">
                    </div>
                </div>

                <div class="form-group">
                    <label for="camera_mp">Độ phân giải camera (MP)</label>
                    <div class="input-group">
                        <i class="fas fa-camera"></i>
                        <input type="number" id="camera_mp" name="camera_mp" class="form-control" 
                               value="{{ old('camera_mp') }}" min="0" step="0.1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="sku">Mã SKU <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-barcode"></i>
                        <input type="text" id="sku" name="sku" class="form-control" 
                               value="{{ old('sku') }}" required placeholder="VD: DJI-MAVIC3-001">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu sản phẩm
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
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