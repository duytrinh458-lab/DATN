{{-- Toast chung cho toàn bộ admin --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<style>
    .toastify {
        font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        font-size: 14.5px;
        font-weight: 500;
        padding: 14px 22px;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 380px;
        line-height: 1.4;
    }

    .toast-app-success {
        background: linear-gradient(135deg, #bfd851, #22c55e) !important;
        color: #fff;
        border-left: 4px solid #050505;
    }

    .toast-app-error {
        background: linear-gradient(135deg, #c75151, #f0c243) !important;
        color: #fff;
        border-left: 4px solid #b91c1c;
    }

    .toast-app-icon {
        font-size: 18px;
        flex-shrink: 0;
    }
</style>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            Toastify({
                text: '<span class="toast-app-icon">✅</span><span>' + @json(session('success')) + '</span>',
                duration: 3000,
                gravity: "bottom",
                position: "right",
                close: true,
                escapeMarkup: false,
                className: "toast-app-success",
                stopOnFocus: true,
            }).showToast();
        @endif

        @if(session('error'))
            Toastify({
                text: '<span class="toast-app-icon">⚠️</span><span>' + @json(session('error')) + '</span>',
                duration: 3500,
                gravity: "bottom",
                position: "right",
                close: true,
                escapeMarkup: false,
                className: "toast-app-error",
                stopOnFocus: true,
            }).showToast();
        @endif

        @if($errors->any())
            Toastify({
                text: '<span class="toast-app-icon">⚠️</span><span>' + @json($errors->first()) + '</span>',
                duration: 3500,
                gravity: "bottom",
                position: "right",
                close: true,
                escapeMarkup: false,
                className: "toast-app-error",
                stopOnFocus: true,
            }).showToast();
        @endif
    });
</script>
