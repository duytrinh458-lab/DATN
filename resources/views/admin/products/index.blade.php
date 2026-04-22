@extends('Admin.layouts.admin')

@section('title', 'Danh sách sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/products.css') }}">
@endpush

@section('content')
<div class="product-list">
    <h1>Danh sách sản phẩm</h1>
    <div class="actions">
        <a href="{{ route('admin.products.create') }}" class="btn-add">+ Thêm sản phẩm</a>
    </div>

    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên máy bay</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thời gian bay</th>
                <th>Camera MP</th>
                <th>Ảnh</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ number_format($product->price, 0, ',', '.') }}₫</td>
                <td>{{ $product->quantity }}</td>
                <td>{{ $product->flight_time }}</td>
                <td>{{ $product->camera_mp }}</td>
                <td>
                    @if($product->image1)
                        <img src="{{ asset('uploads/products/' . $product->image1) }}" class="thumb" alt="">
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-edit">Sửa</a>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete" onclick="return confirm('Xóa sản phẩm này?')">Xóa</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Chưa có sản phẩm nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
