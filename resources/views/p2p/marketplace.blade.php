@extends('layouts.dashboard')

@section('title', 'P2P Trading Marketplace')

@section('content')
<div class="glass-card mb-4">
    <!-- P2P Subheading tabs -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 border-bottom border-secondary mb-4">
        <div class="d-flex gap-2">
            <a href="{{ route('marketplace', ['type' => 'buy', 'country_id' => $selectedCountryId]) }}" class="btn {{ $type === 'buy' ? 'btn-premium' : 'btn-outline-secondary' }} px-4 py-2">
                Buy USDT
            </a>
            <a href="{{ route('marketplace', ['type' => 'sell', 'country_id' => $selectedCountryId]) }}" class="btn {{ $type === 'sell' ? 'btn-premium-sell' : 'btn-outline-secondary' }} px-4 py-2" style="{{ $type === 'sell' ? 'background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border: none;' : '' }}">
                Sell USDT
            </a>
        </div>
        <div>
            <a href="{{ route('advertisements.create') }}" class="btn btn-outline-primary px-3 py-2">
                + Post Advertisement
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('marketplace') }}" class="row g-3 align-items-end">
        <input type="hidden" name="type" value="{{ $type }}">

        <!-- Amount Filter -->
        <div class="col-md-3 col-sm-6">
            <label for="amount" class="form-label text-muted-custom small">Enter Amount</label>
            <input id="amount" type="number" class="form-control" name="amount" value="{{ $amount }}" placeholder="e.g. 5000">
        </div>

        <!-- Currency / Country Selector -->
        <div class="col-md-3 col-sm-6">
            <label for="country_id" class="form-label text-muted-custom small">Fiat Currency</label>
            <select id="country_id" name="country_id" class="form-select form-control" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e&quot;);">
                @foreach($countries as $c)
                    <option value="{{ $c->id }}" {{ $selectedCountryId == $c->id ? 'selected' : '' }}>
                        {{ $c->currency_code }} ({{ $c->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Payment Method Selector -->
        <div class="col-md-3 col-sm-6">
            <label for="payment_method_id" class="form-label text-muted-custom small">Payment Method</label>
            <select id="payment_method_id" name="payment_method_id" class="form-select form-control" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e&quot;);">
                <option value="">All Payments</option>
                @foreach($paymentMethods as $pm)
                    <option value="{{ $pm->id }}" {{ $paymentMethodId == $pm->id ? 'selected' : '' }}>
                        {{ $pm->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Search Button -->
        <div class="col-md-3 col-sm-6 d-grid">
            <button type="submit" class="btn btn-premium px-4 py-2">Search Offers</button>
        </div>
    </form>
</div>

<!-- Offers List -->
<div class="glass-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle border-0 mb-0">
            <thead>
                <tr class="text-muted-custom small">
                    <th>Merchant / Trader</th>
                    <th>Price / Rate</th>
                    <th>Available USDT / Trade Limits</th>
                    <th>Payment Options</th>
                    <th class="text-end">Trade Action</th>
                </tr>
            </thead>
            <tbody>
                @if($ads->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted-custom">
                            No advertisements active matching your filters.
                        </td>
                    </tr>
                @else
                    @foreach($ads as $ad)
                        <tr>
                            <!-- Advertiser Name -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm text-center fw-bold">{{ substr($ad->user->name, 0, 1) }}</div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $ad->user->name }}</h6>
                                        <small class="text-muted-custom font-10">Verification Approved</small>
                                    </div>
                                </div>
                            </td>

                            <!-- Exchange Rate -->
                            <td>
                                <h5 class="mb-0 text-success fw-bold">
                                    {{ number_format($ad->rate, 2) }}
                                    <span class="small font-11 text-muted-custom">{{ $ad->country->currency_code }}</span>
                                </h5>
                            </td>

                            <!-- Available / Limits -->
                            <td>
                                <div class="mb-1 small">
                                    <span class="text-muted-custom">Available:</span> {{ number_format($ad->amount, 2) }} USDT
                                </div>
                                <div class="small text-muted-custom">
                                    <span>Limits:</span> {{ number_format($ad->min_limit, 2) }} - {{ number_format($ad->max_limit, 2) }} {{ $ad->country->currency_code }}
                                </div>
                            </td>

                            <!-- Payment Methods list -->
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($ad->paymentMethods as $pm)
                                        <span class="badge bg-secondary font-11">{{ $pm->name }}</span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Trade Button -->
                            <td class="text-end">
                                @if(Auth::id() === $ad->user_id)
                                    <span class="badge bg-dark border border-secondary text-muted-custom py-2 px-3">Your Ad</span>
                                @else
                                    <a href="{{ route('orders.create', $ad->id) }}" class="btn {{ $type === 'buy' ? 'btn-premium' : 'btn-premium-sell' }} btn-sm px-4 py-2" style="{{ $type === 'sell' ? 'background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border: none;' : '' }}">
                                        {{ $type === 'buy' ? 'Buy USDT' : 'Sell USDT' }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $ads->appends(request()->query())->links() }}
    </div>
</div>
@endsection
