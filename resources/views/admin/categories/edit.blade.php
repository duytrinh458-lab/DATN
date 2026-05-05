@extends('Admin.layouts.admin')

@section('title', 'Sửa danh mục')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/categories/index.css') }}">
@endpush

@section('content')

<div class="category-page">

    <div class="admin-header">
        <h1>Sửa danh mục</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="name" value="{{ $category->name }}">
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-submit">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>

</div>

@endsection