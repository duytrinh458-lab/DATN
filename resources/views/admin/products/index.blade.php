@extends('Admin.layouts.admin')

@section('title', 'Danh sách sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-product.css') }}">
@endpush

@section('content')

<div class="product-list">

    {{-- HEADER --}}
    <div class="page-header">

        <div>

            <h1>Danh sách sản phẩm UAV</h1>

            {{-- TOTAL PRODUCTS --}}
            <div class="product-total">

                Tổng sản phẩm:

                <span>
                    {{ $products->total() }}
                </span>

            </div>

        </div>

        <div class="actions">

            <a href="{{ route('admin.products.create') }}"
               class="btn-add">

                + Thêm sản phẩm

            </a>

        </div>

    </div>


    {{-- SUCCESS ALERT --}}
    @if(session('success'))

        <div class="alert-success">
            {{ session('success') }}
        </div>

    @endif


    {{-- ERROR ALERT --}}
    @if ($errors->any())

        <div class="alert-error">

            <ul>

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- TABLE WRAPPER --}}
    <div class="table-wrapper">

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

                    {{-- ID --}}
                    <td>
                        {{ $product->id }}
                    </td>


                    {{-- PRODUCT NAME --}}
                    <td>
                        {{ $product->name }}
                    </td>


                    {{-- PRICE --}}
                    <td>

                        {{ number_format($product->sale_price, 0, ',', '.') }}₫

                    </td>


                    {{-- IMAGE --}}
                    <td>

                        <img
                            src="{{ asset(optional($product->images->first())->image_url ?? 'images/uav1.jpg') }}"
                            class="product-image"
                        >

                    </td>


                    {{-- ACTION --}}
                    <td>

                        <div class="action-buttons">

                            {{-- EDIT --}}
                            <a href="{{ route('admin.products.edit', $product->id) }}"
                               class="btn-edit">

                                Sửa

                            </a>


                            {{-- DELETE --}}
                            <form action="{{ route('admin.products.destroy', $product->id) }}"
                                  method="POST"
                                  style="display:inline;">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="btn-delete"
                                        onclick="return confirm('Xóa máy bay này?')">

                                    Xóa

                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="5"
                        class="empty-table">

                        Chưa có sản phẩm nào.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- PAGINATION --}}
    <div class="pagination-wrapper">

        @if($products->hasPages())

            {{ $products->onEachSide(1)->links() }}

        @else

            {{-- luôn hiện pagination dù chỉ có 1 trang --}}
            <nav role="navigation">

                <ul>

                    <li class="active">

                        <span>
                            1
                        </span>

                    </li>

                </ul>

            </nav>

        @endif

    </div>

</div>

@endsection