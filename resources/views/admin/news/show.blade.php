@extends('admin.layouts.admin')

@section('title', 'Chi tiết tin tức')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/admin-news.css') }}">
@endpush

@section('content')

<div class="news-container">

    <div class="news-header">
        <h2>Chi tiết tin tức</h2>

        <a href="{{ route('admin.news.index') }}" class="btn-add">
            ← Quay lại
        </a>
    </div>


    <div class="news-card">

        <h2>{{ $news->title }}</h2>

        <p>
            <b>Trạng thái:</b>
            {{ $news->status }}
        </p>

        <p>
            <b>Ngày đăng:</b>
            {{ $news->published_at ?? $news->created_at }}
        </p>

        @if($news->thumbnail)
            <img src="{{ asset('storage/' . $news->thumbnail) }}"
                 style="max-width:300px; border-radius:10px;">
        @endif

        <hr>

        <div>
            {!! nl2br(e($news->content)) !!}
        </div>

    </div>

</div>

@endsection