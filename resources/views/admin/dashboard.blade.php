@extends('Admin.layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@section('content')
<div class="dashboard">
    <h1>Trang quản trị</h1>
    <div class="stats">
        <div class="card">
            <h3>Sản phẩm</h3>
            <p>120</p>
        </div>
        <div class="card">
            <h3>Đơn hàng</h3>
            <p>45</p>
        </div>
        <div class="card">
            <h3>Người dùng</h3>
            <p>300</p>
        </div>
    </div>
</div>
@endsection
