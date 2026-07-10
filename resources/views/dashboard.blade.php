@extends('layouts.dashboard')

@section('title', 'User Dashboard')

@section('content')
<div class="row g-4">
    <!-- Welcome Header Card -->
    <div class="col-12">
        <div class="glass-card d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-extrabold mb-1" style="letter-spacing: -0.5px;">Welcome Back, {{ Auth::user()->name }}!</h2>
                <p class="text-muted mb-0 small">Overview of your TradeFlow P2P wallet balances and active trade transactions.</p>
            </div>
            <div>
                @if(Auth::user()->isKycVerified())
                    <span class="badge py-2 px-3 bg-success d-inline-flex align-items-center gap-1">
                        🛡️ KYC Verified
                    </span>
                @else
                    <a href="{{ route('profile.kyc') }}" class="badge py-2 px-3 bg-warning text-dark d-inline-flex align-items-center gap-1" style="text-decoration: none;">
                        ⚠️ Complete KYC Verification
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Available Balance Card -->
    <div class="col-md-4">
        <div class="glass-card position-relative overflow-hidden" style="min-height: 180px;">
            <div class="position-absolute top-0 end-0 p-3 opacity-25" style="font-size: 3rem;">💰</div>
            <span class="text-muted small d-block mb-2">Available Balance</span>
            <h1 class="fw-bold text-success mb-2" style="font-size: 2.25rem;">
                {{ number_format(Auth::user()->wallet->available_balance, 8) }} <span class="fs-6 text-muted">USDT</span>
            </h1>
            <p class="small text-muted mb-0">Ready for instant P2P marketplace sells & withdrawals.</p>
            <div class="mt-3">
                <a href="{{ route('marketplace') }}" class="btn btn-sm btn-outline-custom">Sell USDT</a>
            </div>
        </div>
    </div>

    <!-- Locked Balance Card -->
    <div class="col-md-4">
        <div class="glass-card position-relative overflow-hidden" style="min-height: 180px;">
            <div class="position-absolute top-0 end-0 p-3 opacity-25" style="font-size: 3rem;">🔒</div>
            <span class="text-muted small d-block mb-2">Locked in Escrow</span>
            <h1 class="fw-bold text-warning mb-2" style="font-size: 2.25rem;">
                {{ number_format(Auth::user()->wallet->locked_balance, 8) }} <span class="fs-6 text-muted">USDT</span>
            </h1>
            <p class="small text-muted mb-0">Funds safely locked for buyer checkout and release.</p>
            <div class="mt-3">
                <a href="{{ route('orders.my') }}" class="btn btn-sm btn-outline-custom">View Escrows</a>
            </div>
        </div>
    </div>

    <!-- Total Balance Card -->
    <div class="col-md-4">
        <div class="glass-card position-relative overflow-hidden" style="min-height: 180px;">
            <div class="position-absolute top-0 end-0 p-3 opacity-25" style="font-size: 3rem;">📊</div>
            <span class="text-muted small d-block mb-2">Total Account Value</span>
            <h1 class="fw-bold text-primary mb-2" style="font-size: 2.25rem; color: var(--accent-orange) !important;">
                {{ number_format(Auth::user()->wallet->total_balance, 8) }} <span class="fs-6 text-muted">USDT</span>
            </h1>
            <p class="small text-muted mb-0">Combined valuation of your available and locked asset balance.</p>
            <div class="mt-3">
                <span class="badge bg-secondary py-1.5 px-3">Live valuation</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions Workspace -->
    <div class="col-12">
        <div class="glass-card">
            <h4 class="card-title-custom mb-4" style="font-size: 1.25rem;">Quick Operations Panel</h4>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('marketplace') }}" class="btn btn-premium px-4">🛒 Browse Marketplace</a>
                <a href="{{ route('advertisements.create') }}" class="btn btn-outline-custom px-4">📢 Post Sell Ad</a>
                <a href="{{ route('orders.my') }}" class="btn btn-outline-custom px-4">🤝 Active P2P Trades</a>
                <a href="{{ route('profile.two-factor') }}" class="btn btn-outline-custom px-4">🔑 2FA Security Settings</a>
                <a href="{{ route('profile.kyc') }}" class="btn btn-outline-custom px-4">📁 KYC Document Hub</a>
            </div>
        </div>
    </div>
</div>
@endsection
