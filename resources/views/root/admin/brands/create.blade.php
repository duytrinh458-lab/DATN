@extends('admin.layouts.admin')

@section('title', 'Thêm thương hiệu')

@push('styles')
<link rel="stylesheet"
      href="{{ asset('Css/Admin/admin-brands.css') }}">
@endpush 

@section('content')

<div class="brand-page">

    <div class="brand-header">

        <h2>Thêm thương hiệu</h2>

    </div>

    <div class="brand-card">

        <form action="{{ route('admin.brands.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="brand-form">

            @csrf

            <div class="form-group">

                <label>Tên thương hiệu</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       required>

            </div>

            <div class="form-group">

                <label>Logo thương hiệu</label>

                <input type="file"
                       name="logo"
                       class="form-control">

            </div>

            <div class="brand-form-actions">

                <button type="submit"
                        class="brand-btn brand-btn-success">
                    <i class="fa-solid fa-floppy-disk"></i>
                    &nbsp;Lưu thương hiệu
                </button>

                <a href="{{ route('admin.brands.index') }}"
                   class="brand-btn brand-btn-danger">
                    <i class="fa-solid fa-xmark"></i>
                    &nbsp;Về trang danh sách
                </a>

            </div>

        </form>

    </div>

</div>

@endsection