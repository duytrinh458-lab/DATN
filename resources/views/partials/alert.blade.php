{{-- =========================================================
   UAV STORE — GLOBAL ALERT TOAST
========================================================= --}}

@php

    $toastType = null;
    $toastTitle = null;
    $toastMessage = null;

    // SUCCESS
    if(session('success')){

        $toastType = 'success';

        $toastTitle = 'Thành công';

        $toastMessage = session('success');
    }

    // SESSION ERROR
    elseif(session('error')){

        $toastType = 'error';

        $toastTitle = 'Lỗi hệ thống';

        $toastMessage = session('error');
    }

    // VALIDATION ERROR
    elseif($errors->any()){

        $toastType = 'error';

        $toastTitle = 'Dữ liệu chưa hợp lệ';
    }

@endphp


@if(session('success') || session('error') || $errors->any())

    <div class="uav-toast uav-toast--{{ $toastType }} show-toast" role="alert">

        <div class="uav-toast__glow"></div>

        <div class="uav-toast__content">

            <div class="uav-toast__title">
                {{ $toastTitle }}
            </div>

            @if($errors->any())

                @foreach($errors->all() as $error)

                    <div class="uav-toast__msg">
                        • {{ $error }}
                    </div>

                @endforeach

            @else

                <div class="uav-toast__msg">
                    {{ $toastMessage }}
                </div>

            @endif

        </div>

        <div class="uav-toast__progress"></div>

    </div>

@endif


<style>
/* =========================================================
   UAV TOAST SYSTEM
========================================================= */

.uav-toast{

    position:fixed;

    top:28px;
    right:28px;

    width:380px;

    min-height:88px;

    padding:18px 20px;

    border-radius:24px;

    overflow:hidden;

    z-index:999999;

    backdrop-filter:blur(22px);

    border:1px solid rgba(255,255,255,0.08);

    box-shadow:
        0 20px 50px rgba(0,0,0,0.35);

    transform:
        translateX(420px)
        scale(0.95);

    opacity:0;

    animation:
        toastSlide 0.6s cubic-bezier(.22,1,.36,1)
        forwards;

    font-family:"Times New Roman", Times, serif;
}

/* =========================================================
   SHOW ANIMATION
========================================================= */

@keyframes toastSlide{

    to{

        transform:
            translateX(0)
            scale(1);

        opacity:1;
    }
}

/* =========================================================
   SUCCESS
========================================================= */

.uav-toast--success{

    background:
        linear-gradient(
            135deg,
            rgba(0,230,118,0.14),
            rgba(255,255,255,0.05)
        );

    border-color:
        rgba(0,230,118,0.22);
}

/* =========================================================
   ERROR
========================================================= */

.uav-toast--error{

    background:
        linear-gradient(
            135deg,
            rgba(255,107,129,0.14),
            rgba(255,255,255,0.05)
        );

    border-color:
        rgba(255,107,129,0.22);
}

/* =========================================================
   GLOW
========================================================= */

.uav-toast__glow{

    position:absolute;

    inset:0;

    background:
        radial-gradient(
            circle at top right,
            rgba(255,255,255,0.10),
            transparent 50%
        );

    pointer-events:none;
}

/* =========================================================
   CONTENT
========================================================= */

.uav-toast__content{

    position:relative;

    z-index:2;
}

.uav-toast__title{

    font-size:18px;

    font-weight:800;

    color:white;

    margin-bottom:6px;

    letter-spacing:0.3px;
}

.uav-toast__msg{

    font-size:15px;

    line-height:1.7;

    color:#d7e2ef;
}

/* =========================================================
   PROGRESS BAR
========================================================= */

.uav-toast__progress{

    position:absolute;

    left:0;
    bottom:0;

    height:4px;

    width:100%;

    background:
        linear-gradient(
            90deg,
            #35d8ff,
            #7ef9d8
        );

    animation:
        toastProgress 5s linear forwards;
}

@keyframes toastProgress{

    from{
        width:100%;
    }

    to{
        width:0%;
    }
}

/* =========================================================
   MOBILE
========================================================= */

@media(max-width:600px){

    .uav-toast{

        width:calc(100% - 24px);

        right:12px;

        top:12px;

        border-radius:20px;

        padding:16px;
    }

    .uav-toast__title{

        font-size:16px;
    }

    .uav-toast__msg{

        font-size:14px;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const toasts = document.querySelectorAll(".show-toast");

    toasts.forEach((toast) => {

        setTimeout(() => {

            toast.style.transition = "all 0.5s ease";

            toast.style.opacity = "0";

            toast.style.transform =
                "translateX(420px) scale(0.95)";

            setTimeout(() => {

                toast.remove();

            }, 500);

        }, 5000);

    });

});
</script>