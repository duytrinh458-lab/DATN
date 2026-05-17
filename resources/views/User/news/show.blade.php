@extends('User.layouts.app')

@section('title', $news->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/news.css') }}">
@endpush

@section('content')

<div class="news-detail-container">

    <!-- HEADER -->
    <div class="news-detail-header">

        <h1 class="news-title">
            {{ $news->title }}
        </h1>

        <div class="news-meta">
            <span>
                📅
                {{ $news->published_at
                    ? \Carbon\Carbon::parse($news->published_at)->format('d/m/Y H:i')
                    : 'Chưa cập nhật' }}
            </span>
        </div>

    </div>

    <!-- THUMBNAIL -->
    @if($news->thumbnail)
        <div class="news-thumbnail">
            <img src="{{ asset('storage/' . $news->thumbnail) }}"
                 alt="{{ $news->title }}">
        </div>
    @endif

    <!-- CONTENT -->
    <div class="news-content">
        {!! $news->content !!}
    </div>

</div>

@endsection