@extends('Admin.layouts.admin')

@section('title', 'Danh sách sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/index.css') }}">
@endpush

@section('content')
<div class="product-list">
    <h1>Danh sách sản phẩm UAV</h1>
    <div class="actions">
        <a href="{{ route('admin.products.create') }}" class="btn-add">+ Thêm sản phẩm</a>
    </div>

    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên máy bay</th>
                <th>Giá bán</th>
                <th>Ảnh</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            {{-- Vòng lặp này chỉ nằm ở file index, vì Controller index() mới gửi biến $products qua --}}
            @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ number_format($product->sale_price, 0, ',', '.') }}₫</td>
                <td>
                    @if($product->images && $product->images->image_url1)
                        <img src="{{ asset($product->images->image_url1) }}" style="width:60px;">
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.products.edit', $product->id) }}">Sửa</a>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Xóa máy bay này?')">Xóa</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5">Chưa có sản phẩm nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection