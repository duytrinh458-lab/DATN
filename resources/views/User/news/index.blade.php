@extends('User.layouts.app')

@section('title', 'Trung tâm Tin tức | Vanguard')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('Css/User/user-news.css') }}">
@endpush

@section('content')
<div class="news-viewport">
    <div class="news-page-container">

        <div class="news-page-header">
            <h1 class="news-title-main">BẢN TIN <span class="highlight">VANGUARD</span></h1>
            <p class="news-subtitle">CẬP NHẬT CÔNG NGHỆ UAV & TÌNH HÌNH CHIẾN SỰ MỚI NHẤT</p>
        </div>

        <div class="search-row">

            <label class="search-title">
                <span class="material-symbols-outlined"
                    style="font-size:18px;vertical-align:middle;margin-right:4px;">
                    search
                </span>
                TÌM KIẾM TIN TỨC
            </label>

            <form action="{{ route('user.news.index') }}"
                method="GET"
                class="search-form">

                <input type="text"
                    name="search"
                    placeholder="Nhập tiêu đề tin tức..."
                    value="{{ request('search') }}">

                <button type="submit" class="btn-scan">
                    Tìm Kiếm
                </button>

            </form>

        </div>


        <div class="news-grid">
            @forelse($news as $item)
                <div class="news-card">
                    <a href="{{ route('user.news.show', $item->id) }}" class="news-link">

                        <div class="news-image">
    @php
        $thumbnail = $item->thumbnail;

        // fallback nếu DB chỉ lưu "news01.jpg"
        $path = file_exists(storage_path('app/public/' . $thumbnail))
            ? asset('storage/' . $thumbnail)
            : asset('storage/news/' . $thumbnail);
    @endphp

    @if(!empty($thumbnail))
        <img src="{{ $path }}"
             alt="{{ $item->title }}">
    @else
        <div class="news-image-placeholder">
            <span class="material-symbols-outlined">satellite_alt</span>
        </div>
    @endif

    <div class="news-image-overlay"></div>
</div>

                        <div class="news-content">
                            <h3 class="news-title" title="{{ $item->title }}">
                                {{ $item->title }}
                            </h3>

                            <p class="news-excerpt">
                                {{ \Illuminate\Support\Str::limit(strip_tags($item->content), 100) }}
                            </p>

                            <div class="news-meta-footer">
                                <div class="news-date">
                                    <span class="material-symbols-outlined icon-sm">schedule</span>
                                    {{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') : 'Đang giải mã' }}
                                </div>
                                
                                <div class="news-readmore">
                                    ĐỌC TIẾP <span class="material-symbols-outlined">arrow_forward_ios</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="news-empty">
                    <span class="material-symbols-outlined">radar</span>
                    <p>RADAR CHƯA QUÉT THẤY TÍN HIỆU TIN TỨC NÀO.</p>
                </div>
            @endforelse
        </div>
        
        <div class="pagination-wrapper">
            @if(method_exists($news, 'links') && $news->lastPage() > 1)
                {{ $news->onEachSide(1)->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
            @endif
        </div>

    </div>
</div>
@endsection