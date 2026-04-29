@extends('User.layouts.app')

@section('title', 'Ví của tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('Css/User/wallet.css') }}">
@endpush

@section('content')

<div class="wallet-viewport">

    {{-- MAIN --}}
    <div class="wallet-main">
        <h1>WALLET</h1>

        {{-- SỐ DƯ --}}
        <div class="wallet-module">
            <span class="module-title">Số dư</span>
            <div class="balance">
                {{ number_format($balance, 0, ',', '.') }}₫
            </div>
        </div>

        {{-- NẠP TIỀN --}}
        <div class="wallet-module">
            <span class="module-title">Nạp tiền</span>
            <form action="{{ route('user.wallet.deposit') }}" method="POST">
                @csrf
                <input type="number" name="amount" class="wallet-input" placeholder="Nhập số tiền" required>
                <button type="submit" class="btn-confirm">Nạp tiền</button>
            </form>
        </div>

        {{-- RÚT TIỀN --}}
        <div class="wallet-module">
            <span class="module-title">Rút tiền</span>
            <form action="{{ route('user.wallet.withdraw') }}" method="POST">
                @csrf
                <input type="number" name="amount" class="wallet-input" placeholder="Nhập số tiền" required>
                <button type="submit" class="btn-confirm">Rút tiền</button>
            </form>
        </div>
    </div>

    {{-- SIDEBAR --}}
    <aside class="wallet-sidebar">

        <span class="module-title">Lịch sử giao dịch</span>

        @foreach($transactions as $item)
        <div class="transaction-item">
            <div>
                <strong>{{ $item->type }}</strong>
                <br>
                <small>{{ $item->created_at }}</small>
            </div>

            <div class="{{ $item->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                {{ number_format($item->amount, 0, ',', '.') }}₫
            </div>
        </div>
        @endforeach

        {{-- PAGINATION --}}
        {{ $transactions->links() }}

    </aside>

</div>

@endsection