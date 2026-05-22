@extends('User.layouts.app')

@section('title', 'Danh mục UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/categories.css') }}">
@endpush

@section('content')
<div class="categories-viewport">

    <h1 class="categories-title">
        <span class="material-symbols-outlined">grid_view</span>
        DATABASE CATEGORY MODULE
    </h1>

    <div class="categories-subtitle">
        SYSTEM_STATUS: ONLINE | CATEGORY_ENGINE_V2.0
    </div>

    <div class="categories-grid">

        @forelse($categories as $cat)
            <a href="{{ route('user.categories.show', $cat->id) }}" class="category-card">

                <div class="category-icon">
                    <span class="material-symbols-outlined">precision_manufacturing</span>
                </div>

                <div class="category-name">
                    {{ $cat->name }}
                </div>

                <div class="category-count">
                    {{ $cat->products->count() }} DEVICES CONNECTED
                </div>

                <div class="access-indicator">
                    >> ACCESS NODE
                </div>

            </a>
        @empty
            <div class="empty-state">
                >> SYSTEM ALERT: NO CATEGORY DATA FOUND
            </div>
        @endforelse

    </div>
</div>
@endsection