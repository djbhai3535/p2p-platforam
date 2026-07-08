@extends('layouts.dashboard')

@section('title', 'My P2P Trades')

@section('content')
<div class="glass-card">
    <div class="mb-4">
        <h3 class="card-title-custom mb-0">My P2P Trades</h3>
        <p class="text-muted-custom small mb-0">Track all your active and historical buy/sell trades on the platform.</p>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle border-0 mb-0">
            <thead>
                <tr class="text-muted-custom small">
                    <th>Order ID</th>
                    <th>Type</th>
                    <th>Merchant / Trader</th>
                    <th>Amount USDT</th>
                    <th>Fiat Total</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @if($trades->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted-custom">
                            You have no trades matching your records.
                        </td>
                    </tr>
                @else
                    @foreach($trades as $trade)
                        @php
                            $isBuyer = (Auth::id() === $trade->buyer_id);
                        @endphp
                        <tr>
                            <td>#{{ substr($trade->id, 0, 8) }}</td>
                            <td>
                                <span class="badge {{ $isBuyer ? 'bg-success' : 'bg-info' }}">
                                    {{ $isBuyer ? 'BUY' : 'SELL' }}
                                </span>
                            </td>
                            <td>
                                {{ $isBuyer ? $trade->seller->name : $trade->buyer->name }}
                            </td>
                            <td>{{ number_format($trade->amount_usdt, 8) }} USDT</td>
                            <td>
                                <span class="fw-bold">{{ number_format($trade->amount_fiat, 2) }}</span> {{ $trade->advertisement->country->currency_code }}
                            </td>
                            <td>
                                <span class="badge @if($trade->status === 'completed') bg-success @elseif($trade->status === 'paid') bg-info @elseif($trade->status === 'disputed') bg-danger @elseif($trade->status === 'cancelled') bg-secondary @else bg-warning @endif">
                                    {{ ucfirst($trade->status) }}
                                </span>
                            </td>
                            <td>{{ $trade->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('orders.show', $trade->id) }}" class="btn btn-sm btn-outline-primary px-3">Enter Room</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $trades->links() }}
    </div>
</div>
@endsection
