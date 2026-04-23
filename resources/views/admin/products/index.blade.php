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
            @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ number_format($product->sale_price, 0, ',', '.') }}₫</td>

                <!-- 🔥 FIX KHÔNG BAO GIỜ LỖI -->
                <td>
                    <img 
                        src="{{ asset(optional($product->images->first())->image_url ?? 'images/uav1.jpg') }}"
                        style="width:60px; height:60px; object-fit:cover; border-radius:6px;"
                    >
                </td>

                <td>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-edit">Sửa</a>

                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline;">
                        @csrf 
                        @method('DELETE')
                        <button class="btn-delete" type="submit"
                            onclick="return confirm('Xóa máy bay này?')">
                            Xóa
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;">Chưa có sản phẩm nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection