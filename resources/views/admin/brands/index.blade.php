@extends('admin.layouts.admin')

@section('title', 'Quản lý thương hiệu')

@push('styles')
<link rel="stylesheet"
      href="{{ asset('Css/Admin/admin-brands.css') }}">
@endpush

@section('content')

<div class="brand-page">

    <div class="brand-header">

        <h2>Danh sách thương hiệu</h2>

        <a href="{{ route('admin.brands.create') }}"
           class="brand-btn brand-btn-success">
            <i class="fa-solid fa-plus"></i>
            &nbsp;Thêm thương hiệu
        </a>

    </div>

    <div class="brand-card">

        <table class="brand-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Logo</th>
                    <th>Tên thương hiệu</th>
                    <th width="220">Thao tác</th>
                </tr>
            </thead>

            <tbody>

                @forelse($brands as $brand)

                <tr>

                    <td>{{ $brand->id }}</td>

                    <td>

                        @if($brand->logo)

                            <img src="{{ asset('storage/' . $brand->logo) }}"
                                 class="brand-logo">

                        @endif

                    </td>

                    <td>{{ $brand->name }}</td>

                    <td>

                        <div class="brand-actions">

                            <a href="{{ route('admin.brands.edit', $brand->id) }}"
                               class="brand-btn brand-btn-warning">
                                <i class="fa-solid fa-pen"></i>
                                &nbsp;Sửa
                            </a>

                            <form action="{{ route('admin.brands.destroy', $brand->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa thương hiệu này?')">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="brand-btn brand-btn-danger">
                                    <i class="fa-solid fa-trash"></i>
                                    &nbsp;Xóa
                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="4"
                        style="text-align:center;padding:30px;">
                        Chưa có thương hiệu nào
                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection