@extends('Admin.layouts.admin')

@section('title', 'Quản lý bình luận')

@section('content')

<h2>Danh sách bình luận</h2>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table border="1" width="100%" cellpadding="10">
    <thead>
        <tr>
            <th>ID</th>
            <th>Người dùng</th>
            <th>Sản phẩm</th>
            <th>Nội dung</th>
            <th>Ngày</th>
            <th>Hành động</th>
        </tr>
    </thead>

    <tbody>
        @foreach($comments as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->full_name }}</td>
            <td>{{ $c->product_name }}</td>
            <td>{{ $c->comment }}</td>
            <td>{{ $c->created_at }}</td>
            <td>
                <form action="{{ route('admin.interactions.comments.delete', $c->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('Xóa comment này?')" class="btn btn-delete">
                        Xóa
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection