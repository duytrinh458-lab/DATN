@extends('Admin.layouts.admin')

@section('title', 'Quản lý người dùng')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-user3.css') }}">
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')

<div class="user-management-page">

    {{-- HEADER --}}
    <div class="admin-header">

        <div class="header-info">
            <h1>Quản lý người dùng</h1>
            <p>Quản lý tài khoản hệ thống UAV</p>
        </div>

        <div class="header-actions">

            <a href="{{ route('admin.users.create') }}"
               class="btn-add-new">

                <i class="fas fa-user-plus"></i>

                <span>Thêm người dùng</span>

            </a>

        </div>

    </div>

    {{-- THÔNG BÁO --}}
    @if(session('success'))

        <div class="success-alert">

            <i class="fas fa-check-circle"></i>

            <span>{{ session('success') }}</span>

        </div>

    @endif

    {{-- BẢNG NGƯỜI DÙNG --}}
    <div class="table-container shadow-premium">

        <div class="table-header-box">

            <div>

                <h2 class="card-title">
                    Danh sách tài khoản
                </h2>

                <p class="card-subtitle">
                    Tổng cộng {{ $users->total() }} tài khoản
                </p>

            </div>

        </div>

        <div class="table-responsive">

            <table class="uav-table">

                <thead>

                    <tr>

                        <th width="80">ID</th>

                        <th>Người dùng</th>

                        <th>Email</th>

                        <th>Số điện thoại</th>

                        <th class="center">Vai trò</th>

                        <th class="center">Trạng thái</th>

                        <th class="center">Hành động</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($users as $user)

                        <tr>

                            {{-- ID --}}
                            <td>
                                <span class="user-id">
                                    #{{ $user->id }}
                                </span>
                            </td>

                            {{-- THÔNG TIN NGƯỜI DÙNG --}}
                            <td>

                                <div class="user-info-cell">

                                    {{-- AVATAR --}}
                                    <div class="avatar-wrapper">

                                        @if(!empty($user->avatar) && file_exists(public_path($user->avatar)))

                                            <img src="{{ asset($user->avatar) }}"
                                                 alt="{{ $user->full_name }}"
                                                 class="user-avatar">

                                        @else

                                            <div class="avatar-circle">
                                                {{ strtoupper(substr($user->full_name,0,1)) }}
                                            </div>

                                        @endif

                                    </div>

                                    {{-- TÊN --}}
                                    <div>

                                        <div class="user-name">
                                            {{ $user->full_name }}
                                        </div>

                                        <div class="user-sub">
                                            Thành viên hệ thống
                                        </div>

                                    </div>

                                </div>

                            </td>

                            {{-- EMAIL --}}
                            <td>
                                {{ $user->email }}
                            </td>

                            {{-- PHONE --}}
                            <td>
                                {{ $user->phone }}
                            </td>

                            {{-- ROLE --}}
                            <td class="center">

                                <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">

                                    {{ $user->role === 'admin' ? 'QUẢN TRỊ' : 'NGƯỜI DÙNG' }}

                                </span>

                            </td>

                            {{-- STATUS --}}
                            <td class="center">

                                <div class="status-wrapper {{ $user->status == 'active' ? 'active' : 'inactive' }}">

                                    <span class="status-dot"></span>

                                    <span>
                                        {{ $user->status == 'active' ? 'Hoạt động' : 'Tạm khóa' }}
                                    </span>

                                </div>

                            </td>

                            {{-- ACTION --}}
                            <td class="center">

                                <a href="{{ route('admin.users.show',$user->id) }}"
                                   class="btn-view-detail">

                                    <i class="fas fa-eye"></i>

                                    Chi tiết

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7">

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        👤
                                    </div>

                                    <div class="empty-title">
                                        Chưa có người dùng nào
                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PHÂN TRANG --}}
        <div class="pagination-wrapper">
            {{ $users->onEachSide(1)->links() }}
        </div>

    </div>

</div>

@endsection