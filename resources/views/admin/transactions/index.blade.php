@extends('Admin.layouts.admin')

@section('title', 'Quản Lý Dòng Tiền V-Pay')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/admin/transactions.css') }}">
@endpush

@section('content')
<div class="transaction-wrapper">
    <h1>Quản Lý Giao Dịch V-Pay</h1>

    <div class="tx-card">
        @if(session('success'))
            <div class="alert-custom alert-success">✔️ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-custom alert-error">⚠️ {{ session('error') }}</div>
        @endif

        <table class="table-tech">
            <thead>
                <tr>
                    <th>Mã GD</th>
                    <th>Khách hàng</th>
                    <th>Loại GD</th>
                    <th>Số tiền</th>
                    <th>Thời gian</th>
                    <th>Trạng thái & Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td style="font-weight: 600; color: #6b7280;">#{{ $tx->id }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $tx->user_name }}</div>
                        <div style="font-size: 12px; color: #6b7280;">{{ $tx->email }}</div>
                    </td>
                    <td>
                        @if($tx->type == 'deposit') <span class="badge-type type-deposit">Nạp Tiền</span>
                        @elseif($tx->type == 'withdraw') <span class="badge-type type-withdraw">Rút Tiền</span>
                        @else <span class="badge-type type-payment">Thanh Toán</span> @endif
                    </td>
                    <td style="font-weight: bold; font-size: 16px; color: {{ in_array($tx->type, ['payment', 'withdraw']) ? '#dc2626' : '#16a34a' }}">
                        {{ in_array($tx->type, ['payment', 'withdraw']) ? '-' : '+' }}{{ number_format($tx->amount, 0, ',', '.') }} ₫
                    </td>
                    <td style="font-size: 14px;">{{ \Carbon\Carbon::parse($tx->created_at)->format('H:i d/m/Y') }}</td>
                    
                    <td>
                        <form action="{{ route('admin.transactions.updateStatus', $tx->id) }}" method="POST" style="display: flex; align-items: center;">
                            @csrf
                            <select name="status" class="status-select" 
                                style="color: {{ $tx->status == 'pending' ? '#d97706' : ($tx->status == 'success' ? '#16a34a' : '#dc2626') }}">
                                <option value="pending" {{ $tx->status == 'pending' ? 'selected' : '' }}>⏳ Chờ duyệt</option>
                                <option value="success" {{ $tx->status == 'success' ? 'selected' : '' }}>✔️ Thành công</option>
                                <option value="failed" {{ $tx->status == 'failed' ? 'selected' : '' }}>❌ Thất bại</option>
                            </select>
                            <button type="submit" class="btn-update-tx">Lưu</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">Chưa có giao dịch nào trong hệ thống.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection