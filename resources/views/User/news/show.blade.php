@extends('User.layouts.app')

@section('title', $news->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/user-news.css') }}">
@endpush

@section('content')

<div class="news-detail">

    <!-- HEADER -->
    <div class="news-detail-header">

        <h1>
            {{ $news->title }}
        </h1>

        <small>
            📅
            {{ $news->published_at
                ? \Carbon\Carbon::parse($news->published_at)->format('d/m/Y H:i')
                : 'Chưa cập nhật' }}
        </small>

    </div>

    <!-- THUMBNAIL -->
    @if($news->thumbnail)
        <div class="news-detail-image">

            <img src="{{ asset('storage/' . $news->thumbnail) }}"
                 alt="{{ $news->title }}">

        </div>
    @endif

    <!-- CONTENT -->
    <div class="content">
        {!! $news->content !!}
    </div>

</div>

@endsection