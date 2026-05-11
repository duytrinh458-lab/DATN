@extends('Admin.layouts.admin')

@section('title', 'Cấu Hình QR Nạp Tiền')

@push('styles')
<style>
    .qr-page-wrapper {
        padding: 20px;
        max-width: 900px;
    }
    .qr-page-wrapper h1 {
        color: #111827; 
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 25px;
    }
    .qr-card {
        background: #ffffff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }
    .qr-display {
        background: #f3f4f6;
        padding: 20px;
        border-radius: 8px;
        border: 1px dashed #d1d5db;
        display: inline-block;
        margin-bottom: 20px;
    }
    .qr-display img {
        max-width: 200px;
        border-radius: 6px;
    }
    .btn-submit-qr {
        background-color: #2563eb;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 15px;
    }
    .btn-submit-qr:hover {
        background-color: #1d4ed8;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
    }
    .alert-success {
        background-color: #dcfce7;
        color: #166534;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #bbf7d0;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="qr-page-wrapper">
    <h1>Quản lý Mã QR Nạp Tiền (V-Pay)</h1>

    <div class="qr-card">
        @if(session('success'))
            <div class="alert-success">
                ✔️ {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-5 text-center">
                <h4 style="font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 15px;">Mã QR Đang Sử Dụng</h4>
                <div class="qr-display">
                    <img src="{{ asset('images/qr-demo.png') }}?v={{ time() }}" alt="QR Hiện Tại">
                </div>
            </div>

            <div class="col-md-7">
                <h4 style="font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 15px;">Tải lên QR Mới</h4>
                
                <form action="{{ route('admin.qr.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 14px; color: #6b7280; margin-bottom: 8px; display: block;">Chọn file ảnh từ máy tính (PNG, JPG):</label>
                        <input type="file" name="qr_code" class="form-control" accept="image/png, image/jpeg" required>
                    </div>

                    <p style="font-size: 13px; color: #ef4444; margin-bottom: 20px;">
                        * Lưu ý: Ảnh mới sẽ ghi đè ngay lập tức lên toàn bộ ví của khách hàng.
                    </p>

                    <button type="submit" class="btn-submit-qr">
                        Cập Nhật Mã QR Hệ Thống
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection