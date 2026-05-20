{{--
    PARTIAL: partials/alert.blade.php
    Dùng chung cho tất cả trang: @include('partials.alert')
    Tự động hiển thị nếu có session hoặc validation error
--}}

@if(session('success'))
    <div class="alert-toast alert-toast--success" role="alert">
        <span class="alert-toast__icon material-symbols-outlined">check_circle</span>
        <span class="alert-toast__msg">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert-toast alert-toast--error" role="alert">
        <span class="alert-toast__icon material-symbols-outlined">warning</span>
        <span class="alert-toast__msg">{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert-toast alert-toast--error" role="alert">
        <span class="alert-toast__icon material-symbols-outlined">error</span>
        <div>
            @foreach($errors->all() as $error)
                <div class="alert-toast__msg">{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif