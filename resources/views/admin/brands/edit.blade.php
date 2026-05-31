@extends('admin.layouts.admin')

@section('title', 'Cập nhật thương hiệu')

@push('styles')
<link rel="stylesheet"
      href="{{ asset('Css/Admin/admin-brands.css') }}">
@endpush

@section('content')

<div class="brand-page">

    <div class="brand-header">

        <h2>Cập nhật thương hiệu</h2>

        <a href="{{ route('admin.brands.index') }}"
           class="brand-btn brand-btn-info">
            <i class="fa-solid fa-arrow-left"></i>
            &nbsp;Danh sách thương hiệu
        </a>

    </div>

    <div class="brand-card">

        <form action="{{ route('admin.brands.update', $brand->id) }}"
              method="POST"
              enctype="multipart/form-data"
              class="brand-form">

            @csrf
            @method('PUT')

            <div class="form-group">

                <label>Tên thương hiệu</label>

                <input type="text"
                       name="name"
                       value="{{ $brand->name }}"
                       class="form-control"
                       required>

            </div>

            <div class="form-group">

                <label>Logo thương hiệu</label>

                <input type="file"
                       name="logo"
                       class="form-control">

                @if($brand->logo)

                    <img src="{{ asset('storage/' . $brand->logo) }}"
                         class="brand-preview">

                @endif

            </div>

            <div class="brand-form-actions">

                <button type="submit"
                        class="brand-btn brand-btn-success">
                    <i class="fa-solid fa-pen-to-square"></i>
                    &nbsp;Cập nhật thương hiệu
                </button>

                <a href="{{ route('admin.brands.index') }}"
                   class="brand-btn brand-btn-danger">
                    <i class="fa-solid fa-xmark"></i>
                    &nbsp;Hủy bỏ
                </a>

            </div>

        </form>

    </div>

</div>

@endsection