@extends('User.layouts.app')

@section('title', $news->title . ' | Vanguard Command')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Space+Grotesk:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('Css/User/user-news-detail.css') }}">
@endpush

@section('content')
<div class="news-detail-viewport">
    <div class="container-article">
        
        {{-- NÚT QUAY LẠI --}}
        <div class="article-actions">
            <a href="{{ route('user.news.index') ?? '#' }}" class="btn-back-hub">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>TRỞ VỀ TRUNG TÂM TIN TỨC</span>
            </a>
        </div>

        {{-- BẢI VIẾT --}}
        <article class="vg-article-card">
            
            {{-- HEADER BÀI VIẾT --}}
            <header class="article-header">
                <div class="article-meta">
                    <span class="meta-tag">
                        <span class="material-symbols-outlined icon-xs">radar</span> TÍN HIỆU VANGUARD
                    </span>
                    <span class="meta-date">
                        <span class="material-symbols-outlined icon-xs">schedule</span>
                        {{ $news->published_at ? \Carbon\Carbon::parse($news->published_at)->format('H:i - d/m/Y') : 'Đang giải mã' }}
                    </span>
                </div>

                <h1 class="article-title">{{ $news->title }}</h1>
            </header>

            {{-- ẢNH THUMBNAIL CHÍNH --}}
            @if($news->thumbnail)
                <div class="article-hero-image">
                    <img src="{{ asset('storage/' . $news->thumbnail) }}" alt="{{ $news->title }}">
                    <div class="image-scanner-line"></div>
                </div>
            @endif

            {{-- NỘI DUNG (RICH TEXT) --}}
            <div class="article-content vg-rich-text">
                {!! $news->content !!}
            </div>

            {{-- FOOTER BÀI VIẾT --}}
            

        </article>
    </div>
</div>
@endsection