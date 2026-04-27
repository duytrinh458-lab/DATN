@extends('User.layouts.app')

@section('title', 'Đơn hàng của tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/orders.css') }}">
@endpush

@section('content')
<div class="container py-5">
    <h2>Lịch sử đơn hàng</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Mã đơn hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            {{-- Vòng lặp @foreach ($orders as $order) --}}
            <tr>
                <td>#ORD-12345</td>
                <td>27/04/2026</td>
                <td>15,000,000đ</td>
                <td><span class="badge bg-warning">Đang xử lý</span></td>
                <td><a href="#" class="btn btn-sm btn-info">Xem chi tiết</a></td>
            </tr>
            {{-- @endforeach --}}
        </tbody>
    </table>
</div>
@endsection