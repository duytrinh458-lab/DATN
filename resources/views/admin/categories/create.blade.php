@extends('Admin.layouts.admin')

@section('title', 'Thêm danh mục')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/categories/categorieslist.css') }}">
@endpush

@section('content')

<div class="category-page">

    {{-- HEADER --}}
    <div class="admin-header">

        <div class="header-left">
            <h1>Thêm danh mục</h1>
            <p>Tạo danh mục sản phẩm mới</p>
        </div>

        <div class="header-right">
            <a href="{{ route('admin.categories.index') }}"
               class="btn-back">
                ← Quay lại
            </a>
        </div>

    </div>

    {{-- CARD --}}
    <div class="category-card">

        <form method="POST"
              action="{{ route('admin.categories.store') }}"
              class="category-form">

            @csrf

            {{-- INPUT --}}
            <div class="form-group">

                <label>Tên danh mục</label>

                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Nhập tên danh mục...">

                @error('name')
                    <p class="form-error">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- FOOTER --}}
            <div class="form-footer">

                <button type="submit"
                        class="btn-submit">
                    + Thêm danh mục
                </button>

            </div>

        </form>

    </div>

</div>

@endsection