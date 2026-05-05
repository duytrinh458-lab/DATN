@extends('Admin.layouts.admin')

@section('title', 'Chi tiết người dùng #' . $user->id)

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/Admin/users.css') }}">
@endpush

@section('content')
<div class="user-detail-page">
    <header class="admin-header">
        <div class="header-info">
            <h1>Hồ sơ người dùng</h1>
            <p>Mã hệ thống: <span class="user-id">#{{ $user->id }}</span></p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.index') }}" class="btn-back-text">
                <i class="fas fa-chevron-left"></i> Quay lại danh sách
            </a>
        </div>
    </header>

    <div class="detail-grid">
        <div class="card shadow-premium info-card">
            <div class="user-avatar-section">
                <div class="avatar-placeholder">
                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                </div>
                <h2 class="user-display-name">{{ $user->full_name }}</h2>
                <span class="badge {{ $user->role == 'admin' ? 'badge-admin' : 'badge-user' }}">
                    {{ strtoupper($user->role) }}
                </span>
            </div>

            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Điện thoại:</span>
                    <span class="info-value">{{ $user->phone }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Ngày tham gia:</span>
                    <span class="info-value">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="card shadow-premium action-card">
            <div class="card-header">
                <h3 class="card-title">Cấu hình tài khoản</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="uav-form">
                @csrf
                <div class="form-group">
                    <label>Phân quyền truy cập</label>
                    <select name="role">
                        <option value="customer" {{ $user->role == 'customer' ? 'selected' : '' }}>Người dùng (User)</option>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Trạng thái hoạt động</label>
                    <select name="status">
                        <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Đang hoạt động (Active)</option>
                        <option value="inactive" {{ $user->status == 'inactive' ? 'selected' : '' }}>Tạm khóa (Inactive)</option>
                    </select>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-update">
                        <i class="fas fa-sync-alt"></i> Lưu thay đổi
                    </button>
                </div>
            </form>

            <div class="danger-zone">
                <h4>Vùng nguy hiểm</h4>
                <p>Hành động này không thể hoàn tác. Vui lòng cẩn thận.</p>
                <form method="POST" action="{{ route('admin.users.delete', $user->id) }}">
                    @csrf
                    <button onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản này?')" class="btn-delete-link">
                        Xóa tài khoản này
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection