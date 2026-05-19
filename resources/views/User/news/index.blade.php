@extends('User.layouts.app')

@section('title', 'Tin tức')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/user-news.css') }}">
@endpush

@section('content')

<div class="news-page-container">

    <!-- TITLE -->
    <div class="news-page-header">
        <h1 class="news-title-main">Tin tức mới nhất</h1>
    </div>

    <!-- GRID -->
    <div class="news-grid">

        @forelse($news as $item)

            <div class="news-card">

                <a href="{{ route('user.news.show', $item->id) }}" class="news-link">

                    <!-- IMAGE -->
                    <div class="news-image">
                        @if($item->thumbnail)
                            <img src="{{ asset('storage/' . $item->thumbnail) }}"
                                 alt="{{ $item->title }}">
                        @endif
                    </div>

                    <!-- CONTENT -->
                    <div class="news-content">

                        <h3 class="news-title">
                            {{ $item->title }}
                        </h3>

                        <p class="news-excerpt">
                            {{ \Illuminate\Support\Str::limit(strip_tags($item->content), 120) }}
                        </p>

                        <div class="news-date">
                            📅
                            {{ $item->published_at
                                ? \Carbon\Carbon::parse($item->published_at)->format('d/m/Y')
                                : 'Chưa cập nhật' }}
                        </div>

                    </div>

                </a>

            </div>

        @empty

            <div class="news-empty">
                Không có bài viết nào
            </div>

        @endforelse

    </div>

    <!-- PAGINATION (FIX CHẮC CHẮN HIỂN THỊ) -->
    <div class="pagination-wrapper">

        @if($news->lastPage() > 1)
    {{ $news->links() }}
@else
    <div class="pagination-single">
        Trang 1 / 1
    </div>
@endif

    </div>

</div>

@endsection