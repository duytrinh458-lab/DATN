@extends('admin.layouts.admin')

@section('title', 'Sửa tin tức')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/news.css') }}">
@endpush

@section('content')

<div class="news-container">

    <div class="news-header">
        <h2>Sửa tin tức</h2>

        <a href="{{ route('admin.news.index') }}" class="btn-add">
            ← Quay lại
        </a>
    </div>


    <div class="news-card">

        {{-- 🔥 VALIDATION ERROR --}}
        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form action="{{ route('admin.news.update', $news->id) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')


            {{-- TITLE --}}
            <div class="form-group">
                <label>Tiêu đề</label>
                <input type="text"
                       name="title"
                       class="form-control"
                       value="{{ old('title', $news->title) }}"
                       required>
            </div>


            {{-- CONTENT --}}
            <div class="form-group">
                <label>Nội dung</label>
                <textarea name="content"
                          class="form-control"
                          rows="6"
                          required>{{ old('content', $news->content) }}</textarea>
            </div>


            {{-- THUMBNAIL --}}
            <div class="form-group">
                <label>Ảnh</label>

                {{-- HIỂN THỊ ẢNH CŨ --}}
               @if($news->thumbnail)
    <div style="margin-bottom:10px;">
        <img src="{{ asset('storage/news/' . $news->thumbnail) }}"
             width="120"
             style="border-radius:8px;">
    </div>
@endif

                <input type="file"
                       name="thumbnail"
                       class="form-control">
            </div>


            {{-- STATUS --}}
            <div class="form-group">
                <label>Trạng thái</label>

                <select name="status" class="form-control">

                    <option value="draft"
                        {{ old('status', $news->status) == 'draft' ? 'selected' : '' }}>
                        Nháp
                    </option>

                    <option value="published"
                        {{ old('status', $news->status) == 'published' ? 'selected' : '' }}>
                        Xuất bản
                    </option>

                    <option value="hidden"
                        {{ old('status', $news->status) == 'hidden' ? 'selected' : '' }}>
                        Ẩn
                    </option>

                </select>

            </div>


            {{-- PUBLISHED DATE --}}
            <div class="form-group">
                <label>Ngày đăng</label>

                <input type="datetime-local"
                       name="published_at"
                       class="form-control"
                       value="{{ old('published_at', $news->published_at ? date('Y-m-d\TH:i', strtotime($news->published_at)) : '') }}">
            </div>


            {{-- BUTTON --}}
            <button type="submit" class="btn-add">
                Cập nhật
            </button>

        </form>

    </div>

</div>

@endsection