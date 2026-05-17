@extends('admin.layouts.admin')

@section('title', 'Quản lý tin tức')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-news.css') }}">
@endpush

@section('content')

<div class="news-container">

    <div class="news-header">

        <h2>Quản lý tin tức</h2>

        <a href="{{ route('admin.news.create') }}" class="btn-add">
            + Thêm tin tức
        </a>

    </div>


    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif


    <div class="news-card">

        <table class="news-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tiêu đề</th>
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
                                <img src="{{ asset('storage/' . $item->thumbnail) }}"
                                     class="news-image">
                            @else
                                <span class="empty-image">Không có ảnh</span>
                            @endif

                        </td>


                        {{-- TITLE --}}
                        <td class="news-title">
                            {{ $item->title }}
                        </td>


                        {{-- CONTENT --}}
                        <td>
                            {{ \Illuminate\Support\Str::limit($item->content, 50) }}
                        </td>


                        {{-- STATUS (FIX THEO DB) --}}
                        <td>

                            @if($item->status == 'published')
                                <span class="status active">Đã đăng</span>

                            @elseif($item->status == 'draft')
                                <span class="status inactive">Nháp</span>

                            @else
                                <span class="status inactive">Ẩn</span>
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

                                <a href="{{ route('admin.news.show', $item->id) }}"
                                   class="btn-view">
                                    Xem
                                </a>

                                <a href="{{ route('admin.news.edit', $item->id) }}"
                                   class="btn-edit">
                                    Sửa
                                </a>

                                <form action="{{ route('admin.news.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-delete">
                                        Xóa
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="empty-data">
                            Không có dữ liệu
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- PAGINATION --}}
    <div class="pagination-wrapper">
        {{ $news->links() }}
    </div>

</div>

@endsection