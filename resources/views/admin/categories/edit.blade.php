@extends('Admin.layouts.admin')

@section('title', 'Sửa danh mục')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/categories/categorieslist.css') }}">
@endpush

@section('content')

<div class="category-page">

    {{-- HEADER --}}
    <div class="admin-header">

        <div class="header-left">
            <h1>Sửa danh mục</h1>
            <p>Cập nhật thông tin danh mục</p>
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
              action="{{ route('admin.categories.update', $category->id) }}"
              class="category-form">

            @csrf
            @method('PUT')

            {{-- INPUT --}}
            <div class="form-group">

                <label>Tên danh mục</label>

                <input type="text"
                       name="name"
                       value="{{ old('name', $category->name) }}"
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
                    ✓ Cập nhật
                </button>

            </div>

        </form>

    </div>

</div>

@endsection