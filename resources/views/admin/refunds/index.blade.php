@extends('Admin.layouts.admin')

@section('title', 'Quản Lý Hoàn Trả UAV')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/admin/refunds.css') }}">
@endpush

@section('content')
<div class="refund-wrapper">
    <h1>Yêu Cầu Hoàn Trả / Bảo Hành</h1>

    <div class="rf-card">
        @if(session('success'))
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                ✔️ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <table class="table-tech">
            <thead>
                <tr>
                    <th>Ngày Gửi</th>
                    <th>Khách Hàng</th>
                    <th>Mã Đơn / Số Tiền</th>
                    <th style="width: 30%">Lý Do & Ghi Chú</th>
                    <th>Trạng Thái</th>
                    <th>Xử Lý</th>
                </tr>
            </thead>
            <tbody>
                @forelse($refunds as $rf)
                <tr>
                    <td style="font-size: 14px;">{{ \Carbon\Carbon::parse($rf->created_at)->format('H:i d/m/Y') }}</td>
                    <td style="font-weight: bold;">{{ $rf->user_name }}</td>
                    <td>
                        <div style="color: #2563eb; font-weight: bold;">{{ $rf->order_code }}</div>
                        <div style="color: #ef4444; font-weight: bold; font-size: 15px;">{{ number_format($rf->total) }} ₫</div>
                    </td>
                    <td>
                        <strong style="color: #374151;">{{ $rf->reason }}</strong>
                        <p style="font-size: 13px; color: #6b7280; margin-top: 5px;">{{ $rf->description }}</p>
                    </td>
                    <td>
                        @if($rf->status == 'pending') <span class="status-badge st-pending">⏳ Chờ xử lý</span>
                        @elseif($rf->status == 'approved') <span class="status-badge st-approved">✔️ Đã hoàn tiền</span>
                        @else <span class="status-badge st-rejected">❌ Từ chối</span> @endif
                    </td>
                    <td>
                        @if($rf->status == 'pending')
                            <form action="{{ route('admin.refunds.updateStatus', $rf->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn-action btn-approve" onclick="return confirm('Xác nhận hoàn lại {{ number_format($rf->total) }}đ vào ví khách?')">Duyệt Hoàn Tiền</button>
                            </form>

                            <form action="{{ route('admin.refunds.updateStatus', $rf->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn-action btn-reject" onclick="return confirm('Từ chối yêu cầu này?')">Từ chối</button>
                            </form>
                        @else
                            <span style="color: #9ca3af; font-size: 13px; font-style: italic;">Đã chốt</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">Hiện không có yêu cầu hoàn hàng nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            {{ $refunds->links() }}
        </div>
    </div>
</div>
@endsection