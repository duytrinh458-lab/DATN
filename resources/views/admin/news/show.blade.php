@extends('admin.layouts.admin')

@section('title', 'Chi tiết tin tức')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/news.css') }}">
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
            <span>
                {{ $news->status == 1 ? 'Đã đăng' : 'Nháp' }}
            </span>
        </p>

        <p>
            <b>Ngày đăng:</b>
            {{ optional($news->published_at ?? $news->created_at)->format('d/m/Y H:i') }}
        </p>

        @if(!empty($news->thumbnail))
    <img src="{{ asset('storage/news/' . $news->thumbnail) }}"
         style="max-width:500px; border-radius:10px; display: block; margin: 0 auto;">
@endif

        <hr>

        <div>
            {!! nl2br(e($news->content)) !!}
        </div>

    </div>

</div>

@endsection