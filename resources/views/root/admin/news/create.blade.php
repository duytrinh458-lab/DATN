@extends('admin.layouts.admin')

@section('title', 'Thêm tin tức')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/news.css') }}">
@endpush

@section('content')

<div class="news-container">

    <div class="news-header">
        <h2>Thêm tin tức mới</h2>

        <a href="{{ route('admin.news.index') }}" class="btn-add">
            ← Quay lại
        </a>
    </div>

    <div class="news-card">

        {{-- SUCCESS --}}
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- VALIDATION ERROR --}}
        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.news.store') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            {{-- TITLE --}}
            <div class="form-group">

                <label>
                    Tiêu đề
                </label>

                <input type="text"
                       name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}"
                       placeholder="Nhập tiêu đề bài viết..."
                       required>

                @error('title')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- SLUG --}}
            <!-- <div class="form-group">

                <label>
                    Slug (không bắt buộc)
                </label>

                <input type="text"
                       name="slug"
                       class="form-control @error('slug') is-invalid @enderror"
                       value="{{ old('slug') }}"
                       placeholder="vd: drone-military-v2">

                @error('slug')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror

            </div> -->

            {{-- CONTENT --}}
            <div class="form-group">

                <label>
                    Nội dung
                </label>

                <textarea name="content"
                          class="form-control @error('content') is-invalid @enderror"
                          rows="8"
                          placeholder="Nhập nội dung bài viết..."
                          required>{{ old('content') }}</textarea>

                @error('content')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- THUMBNAIL --}}
            <div class="form-group">

                <label>
                    Ảnh thumbnail
                </label>

                <input type="file"
                       name="thumbnail"
                       class="form-control @error('thumbnail') is-invalid @enderror"
                       accept=".jpg,.jpeg,.png,.webp">

                @error('thumbnail')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- STATUS --}}
            <div class="form-group">

                <label>
                    Trạng thái
                </label>

                <select name="status"
                        class="form-control @error('status') is-invalid @enderror">

                    <option value="draft"
                        {{ old('status') == 'draft' ? 'selected' : '' }}>
                        Nháp
                    </option>

                    <option value="published"
                        {{ old('status') == 'published' ? 'selected' : '' }}>
                        Xuất bản
                    </option>

                    <option value="scheduled"
                        {{ old('status') == 'scheduled' ? 'selected' : '' }}>
                        Lên lịch
                    </option>

                    <option value="hidden"
                        {{ old('status') == 'hidden' ? 'selected' : '' }}>
                        Ẩn
                    </option>

                </select>

                @error('status')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- PUBLISHED DATE --}}
            <div class="form-group">

                <label>
                    Ngày đăng
                </label>

                <input type="datetime-local"
                       name="published_at"
                       class="form-control @error('published_at') is-invalid @enderror"
                       value="{{ old('published_at') ? str_replace(' ', 'T', old('published_at')) : '' }}"
                       min="{{ now()->format('Y-m-d\TH:i') }}">

                @error('published_at')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- BUTTON --}}
            <button type="submit" class="btn-add">
                Lưu tin tức
            </button>

        </form>

    </div>

</div>

@endsection