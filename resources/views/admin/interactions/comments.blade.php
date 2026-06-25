@extends('Admin.layouts.admin')

@section('title', 'Quản lý bình luận')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/interactions.css') }}">
@endpush

@section('content')

<div class="admin-comments-page">

    <h2>Danh sách bình luận</h2>

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

                @forelse($comments as $c)

                <tr>

                    <td>#{{ $c->id }}</td>

                    <!-- USER + AVATAR -->
                    <td>
                        <div class="comment-user">
                            
                            <div class="comment-avatar">
                                @if(!empty($c->avatar) && file_exists(public_path($c->avatar)))
                                    <img src="{{ asset($c->avatar) }}" alt="avatar">
                                @else
                                    <img src="{{ asset('uploads/avatars/avatar-default.jpg') }}" alt="avatar">
                                @endif
                            </div>

                            <div class="comment-user-info">
                                <div class="name">{{ $c->full_name }}</div>
                            </div>

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

        @php
            $rating = $c->rating ?? 0;
        @endphp

        @for($i = 1; $i <= 5; $i++)

            @if($i <= $rating)

                <span style="color: gold;">★</span>

            @else

                <span style="color: #666;">★</span>

            @endif

        @endfor

        <span>
            ({{ $rating }}/5)
        </span>

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

                @empty

                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:#6b7280;">
                        Chưa có bình luận nào
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

        <!-- PAGINATION -->
        <div class="pagination-wrapper">
    @if(method_exists($comments, 'links'))
        {{ $comments->onEachSide(0)->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
    @endif
</div>

    </div>

</div>

@endsection