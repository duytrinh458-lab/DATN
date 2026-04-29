@extends('Admin.layouts.admin')

@section('title', 'Hệ thống Quản trị - Vanguard UAV')

@push('styles')
    <link rel="stylesheet" href="{{ asset('Css/admin/users.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
    <div class="user-management-page">
        <header class="admin-header">
            <div class="header-info">
                <h1>Quản lý người dùng</h1>
                <p>Quản lý quyền hạn và trạng thái tài khoản hệ thống</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.users.create') }}" class="btn btn-add-new">
                    <i class="fas fa-user-plus"></i>
                    <span>Thêm người dùng</span>
                </a>
            </div>
        </header>

        @if(session('success'))
            <div class="alert success-alert">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="table-container shadow-premium">
            <div class="table-header-box">
                <h2 class="card-title">Danh sách tài khoản</h2>
            </div>
            
            <div class="table-responsive">
                <table class="uav-table">
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th>Thông tin người dùng</th>
                            <th>Email</th>
                            <th>Liên hệ</th>
                            <th class="center">Vai trò</th>
                            <th class="center">Trạng thái</th>
                            <th class="center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td><span class="user-id">#{{ $user->id }}</span></td>
                            <td>
                                <div class="user-info-cell">
                                    <span class="user-name">{{ $user->full_name }}</span>
                                </div>
                            </td>
                            <td><span class="user-email">{{ $user->email }}</span></td>
                            <td><span class="user-phone">{{ $user->phone }}</span></td>
                            <td class="center">
                                <span class="badge {{ $user->role == 'admin' ? 'badge-admin' : 'badge-user' }}">
                                    {{ strtoupper($user->role) }}
                                </span>
                            </td>
                            <td class="center">
                                <div class="status-wrapper {{ $user->status }}">
                                    <span class="status-dot"></span>
                                    <span class="status-text">{{ ucfirst($user->status) }}</span>
                                </div>
                            </td>
                            <td class="center">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn-view-detail">
                                        Chi tiết
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection