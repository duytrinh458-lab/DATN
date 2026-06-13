@extends('admin.layouts.admin')

@section('title', 'Quản lý tin tức')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/news.css') }}">
@endpush

@section('content')

<div class="news-container">

    {{-- HEADER --}}
    <div class="news-header">

        <div>

            <h2>Quản lý tin tức</h2>

            {{-- TOTAL NEWS --}}
            <div class="news-total">
                Tổng bài viết:
                <span><strong>{{ $news->total() }}</strong></span>
            </div>

        </div>

        <a href="{{ route('admin.news.create') }}" class="btn-add">
            + Thêm tin tức
        </a>

    </div>


    {{-- ALERT SUCCESS --}}
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif


    {{-- CARD --}}
    <div class="news-card">

        <table class="news-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Slug</th>
                    <th>Nội dung</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody>

                @forelse($news as $item)

                    <tr>

                        {{-- ID --}}
                        <td>{{ $item->id }}</td>


                        {{-- THUMBNAIL --}}
                        <td>

                            @if($item->thumbnail)

                                @php
                                    $image = str_contains($item->thumbnail, 'uploads/')
                                        ? asset($item->thumbnail)
                                        : asset('uploads/news/' . $item->thumbnail);
                                @endphp

                                <img src="{{ $image }}" class="news-image">


                            @else

                                <span class="empty-image">
                                    Không có ảnh
                                </span>

                            @endif

                        </td>


                        {{-- TITLE --}}
                        <td class="news-title">
                            {{ $item->title }}
                        </td>


                        {{-- SLUG --}}
                        <td class="news-slug">
                            {{ $item->slug }}
                        </td>


                        {{-- CONTENT --}}
                        <td>
                            {{ \Illuminate\Support\Str::limit($item->content, 50) }}
                        </td>


                        {{-- STATUS --}}
                        <td>

                            @if($item->status == 'published')

                                <span class="status active">
                                    Đã đăng
                                </span>

                            @elseif($item->status == 'scheduled')

                                <span class="status scheduled">
                                    Lên lịch
                                </span>

                            @elseif($item->status == 'draft')

                                <span class="status inactive">
                                    Nháp
                                </span>

                            @else

                                <span class="status inactive">
                                    Ẩn
                                </span>

                            @endif

                        </td>


                        {{-- PUBLISHED DATE --}}
                        <td>

                            @if($item->published_at)

                                {{ \Carbon\Carbon::parse($item->published_at)->format('d/m/Y H:i') }}

                            @else

                                {{ $item->created_at->format('d/m/Y') }}

                            @endif

                        </td>


                        {{-- ACTION --}}
                        <td>

                            <div class="action-group">

                                {{-- VIEW --}}
                                <a href="{{ route('admin.news.show', $item->id) }}"
                                   class="btn-view">
                                    Xem
                                </a>

                                {{-- EDIT --}}
                                <a href="{{ route('admin.news.edit', $item->id) }}"
                                   class="btn-edit">
                                    Sửa
                                </a>

                                {{-- DELETE --}}
                                <form action="{{ route('admin.news.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn-delete">

                                        Xóa

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="empty-data">
                            Không có dữ liệu
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


     {{-- PHÂN TRANG --}}
<div class="pagination-wrapper">
    @if(method_exists($news, 'links'))
        {{ $news->onEachSide(0)->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
    @endif
</div>

</div>

@endsection