@extends('Admin.layouts.admin')

@section('title', 'Quản Lý Hoàn Trả UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/Admin/categories/refunds2.css') }}">

<style>

/* AJAX TOAST */
.ajax-toast{

    position:fixed;
    top:30px;
    right:30px;

    min-width:280px;

    padding:16px 22px;

    border-radius:14px;

    color:#fff;

    font-weight:600;

    z-index:99999;

    opacity:0;
    transform:translateY(-20px);

    transition:.3s ease;

    box-shadow:0 10px 30px rgba(0,0,0,.2);

}

.ajax-toast.show{

    opacity:1;
    transform:translateY(0);

}

.ajax-toast.success{

    background:linear-gradient(135deg,#10b981,#059669);

}

.ajax-toast.error{

    background:linear-gradient(135deg,#ef4444,#dc2626);

}

</style>
@endpush

@section('content')

<div class="refund-page">

    {{-- HEADER --}}
    <div class="refund-header">

        <div class="header-left">
            <h1>Quản Lý Hoàn Trả / Bảo Hành</h1>
            <p>Theo dõi và xử lý các yêu cầu hoàn tiền từ khách hàng</p>
        </div>

        <div class="header-right">

            <div class="stat-card">
                <div class="stat-number">
                    {{ $refunds->total() }}
                </div>

                <div class="stat-label">
                    Tổng yêu cầu
                </div>
            </div>

        </div>

    </div>

    {{-- MAIN TABLE CARD --}}
    <div class="refund-card">

        <div class="card-top">

            <div>
                <h3>Danh sách yêu cầu hoàn trả</h3>
                <p>Quản lý hoàn tiền & bảo hành UAV</p>
            </div>

        </div>

        <div class="table-responsive">

            <table class="refund-table">

                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Khách hàng</th>
                        <th>Đơn hàng</th>
                        <th>Lý do hoàn trả</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($refunds as $rf)

                    <tr>

                        {{-- TIME --}}
                        <td>

                            <div class="date-box">

                                <div class="date-time">
                                    {{ \Carbon\Carbon::parse($rf->created_at)->format('H:i') }}
                                </div>

                                <div class="date-day">
                                    {{ \Carbon\Carbon::parse($rf->created_at)->format('d/m/Y') }}
                                </div>

                            </div>

                        </td>

                        {{-- USER --}}
                        <td>

                            <div class="user-box">

                                <div class="avatar">
                                    {{ strtoupper(substr($rf->user_name,0,1)) }}
                                </div>

                                <div>

                                    <div class="user-name">
                                        {{ $rf->user_name }}
                                    </div>

                                    <div class="user-role">
                                        Khách hàng
                                    </div>

                                </div>

                            </div>

                        </td>

                        {{-- ORDER --}}
                        <td>

                            <div class="order-code">
                                #{{ $rf->order_code }}
                            </div>

                            <div class="refund-price">
                                {{ number_format($rf->total) }} ₫
                            </div>

                        </td>

                        {{-- REASON --}}
                        <td>

                            <div class="refund-reason">
                                {{ $rf->reason }}
                            </div>

                            @if($rf->description)

                                <div class="refund-desc">
                                    {{ $rf->description }}
                                </div>

                            @endif

                        </td>

                        {{-- STATUS --}}
                        <td>

                            @if($rf->status == 'pending')

                                <span class="status-badge status-pending">
                                    ⏳ Chờ xử lý
                                </span>

                            @elseif($rf->status == 'approved')

                                <span class="status-badge status-approved">
                                    ✔️ Đã hoàn tiền
                                </span>

                            @else

                                <span class="status-badge status-rejected">
                                    ❌ Đã từ chối
                                </span>

                            @endif

                        </td>

                        {{-- ACTION --}}
                        <td class="text-center">

                            @if($rf->status == 'pending')

                                <div class="action-group">

                                    {{-- APPROVE --}}
                                    <form action="{{ route('admin.refunds.updateStatus',$rf->id) }}"
                                          method="POST"
                                          class="refund-form">

                                        @csrf

                                        <input type="hidden"
                                               name="status"
                                               value="approved">

                                        <button type="submit"
                                                class="btn-action btn-approve">

                                            ✔️ Duyệt
                                        </button>

                                    </form>

                                    {{-- REJECT --}}
                                    <form action="{{ route('admin.refunds.updateStatus',$rf->id) }}"
                                          method="POST"
                                          class="refund-form">

                                        @csrf

                                        <input type="hidden"
                                               name="status"
                                               value="rejected">

                                        <button type="submit"
                                                class="btn-action btn-reject">

                                            ✖ Từ chối
                                        </button>

                                    </form>

                                </div>

                            @else

                                <span class="done-badge">
                                    Đã xử lý
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6">

                            <div class="empty-state">

                                <div class="empty-icon">
                                    📦
                                </div>

                                <div class="empty-title">
                                    Không có yêu cầu hoàn trả
                                </div>

                                <div class="empty-desc">
                                    Hiện chưa có yêu cầu bảo hành hoặc hoàn tiền nào.
                                </div>

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="pagination-box">

            {{ $refunds->onEachSide(1)->links() }}

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

document.querySelectorAll('.refund-form').forEach(form => {

    form.addEventListener('submit', async function(e){

        e.preventDefault();

        const status =
            this.querySelector('input[name="status"]').value;

        const confirmText =
            status === 'approved'
            ? 'Xác nhận hoàn tiền vào ví khách?'
            : 'Từ chối yêu cầu này?';

        if(!confirm(confirmText)) return;

        const formData = new FormData(this);

        try{

            const response = await fetch(this.action,{

                method:'POST',

                headers:{
                    'X-CSRF-TOKEN':'{{ csrf_token() }}',
                    'Accept':'application/json'
                },

                body:formData

            });

            const data = await response.json();

            if(data.success){

                showToast(data.message,'success');

                setTimeout(()=>{
                    location.reload();
                },1000);

            }else{

                showToast('Có lỗi xảy ra','error');

            }

        }catch(error){

            showToast('Lỗi server','error');

        }

    });

});

function showToast(message,type='success'){

    const toast = document.createElement('div');

    toast.className = `ajax-toast ${type}`;

    toast.innerText = message;

    document.body.appendChild(toast);

    setTimeout(()=>{
        toast.classList.add('show');
    },100);

    setTimeout(()=>{

        toast.classList.remove('show');

        setTimeout(()=>{
            toast.remove();
        },300);

    },2500);

}

</script>

@endpush