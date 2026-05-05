@extends('Admin.layouts.admin')

@section('title', 'Quản lý danh mục - Vanguard UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/categories/index.css') }}">
@endpush


@section('content')

<div class="category-page">

    <div class="admin-header">
        <h1>Danh sách danh mục</h1>

        <a href="{{ route('admin.categories.create') }}" class="btn-add">
            + Thêm danh mục
        </a>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Slug</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->id }}</td>
                    <td>{{ $cat->name }}</td>
                    <td>{{ $cat->slug }}</td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $cat->id) }}" class="btn-edit">
                            Sửa
                        </a>

                        <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button class="btn-delete" onclick="return confirm('Xóa danh mục này?')">
                                Xóa
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>

@endsection