@extends('Admin.layouts.admin')

@section('title', 'Quản lý bình luận')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/interactions.css') }}">
@endpush

@section('content')

<div class="admin-comments-page">

    <h2>Danh sách bình luận</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="comments-table-wrapper">

        <table class="comments-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>Sản phẩm</th>
                    <th>Nội dung</th>
                    <th>Đánh giá</th>
                    <th>Ngày</th>
                    <th width="150">Hành động</th>
                </tr>
            </thead>

            <tbody>

                @foreach($comments as $c)

                <tr>

                    <td>{{ $c->id }}</td>

                    <td>
                        <div class="comment-user">
                            {{ $c->full_name }}
                        </div>
                    </td>

                    <td>
                        <div class="comment-product">
                            {{ $c->product_name }}
                        </div>
                    </td>

                    <td>
                        <div class="comment-content">
                            {{ $c->comment }}
                        </div>
                    </td>

                    <td>
                        <div class="comment-rating">
                            ⭐ {{ $c->rating ?? 0 }}
                        </div>
                    </td>

                    <td>
                        <div class="comment-date">
                            {{ $c->created_at }}
                        </div>
                    </td>

                    <td>

                        <form action="{{ route('admin.interactions.comments.delete', $c->id) }}"
                              method="POST">

                            @csrf
                            @method('DELETE')

                            <button class="delete-btn"
                                    onclick="return confirm('Xóa comment này?')">
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