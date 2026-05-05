@extends('Admin.layouts.admin')

@section('title', 'Thêm danh mục')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/categories/index.css') }}">
@endpush

@section('content')

<div class="category-page">

    <!-- HEADER -->
    <div class="admin-header">
        <h1>Thêm danh mục</h1>

        <a href="{{ route('admin.categories.index') }}" class="btn-add">
            ← Quay lại
        </a>
    </div>

    <!-- CARD -->
    <div class="card">
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf

            <!-- INPUT -->
            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nhập tên danh mục...">

                {{-- lỗi validate --}}
                @error('name')
                    <p style="color:red; margin-top:5px;">{{ $message }}</p>
                @enderror
            </div>

            <!-- BUTTON -->
            <div class="form-footer">
                <button type="submit" class="btn-submit">
                    + Thêm danh mục
                </button>
            </div>
        </form>
    </div>

</div>

@endsection