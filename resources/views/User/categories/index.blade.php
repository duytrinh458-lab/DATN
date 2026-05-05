@extends('User.layouts.app')

@section('title', 'Danh mục')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/categories.css') }}">
@endpush

@section('content')
<div class="categories-viewport">
    <div class="categories-container">

        <h1 class="categories-title">DANH MỤC UAV</h1>
        <div class="categories-subtitle">CATEGORY_DATABASE_V1.0</div>

        <div class="categories-grid">
            @forelse($categories as $cat)
                <a href="{{ url('/categories/'.$cat->id) }}" class="category-card category-link">
    
    <div class="category-name">{{ $cat->name }}</div>

    <div class="category-slug">{{ $cat->slug }}</div>

</a>
            @empty
                <div class="empty-state">KHÔNG CÓ DỮ LIỆU</div>
            @endforelse
        </div>

    </div>
</div>
@endsection