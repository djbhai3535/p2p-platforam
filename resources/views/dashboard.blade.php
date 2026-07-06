@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4">
    <!-- Welcome card -->
    <div class="col-12">
        <div class="glass-card">
            <h2 class="fw-bold mb-2">Welcome, {{ Auth::user()->name }}!</h2>
            <p class="text-muted mb-0">Here is a quick summary of your TradeFlow P2P account.</p>
        </div>
    </div>

    <!-- Available Balance Card -->
    <div class="col-md-4">
        <div class="glass-card text-center py-4">
            <h5 class="text-muted-custom mb-3">Available Balance</h5>
            <h2 class="fw-bold text-success mb-2">{{ number_format(Auth::user()->wallet->available_balance, 2) }} USDT</h2>
            <p class="small text-muted-custom mb-0">Ready for P2P trading & instant withdrawals</p>
        </div>
    </div>

    <!-- Locked Balance Card -->
    <div class="col-md-4">
        <div class="glass-card text-center py-4">
            <h5 class="text-muted-custom mb-3">Locked in Escrow</h5>
            <h2 class="fw-bold text-warning mb-2">{{ number_format(Auth::user()->wallet->locked_balance, 2) }} USDT</h2>
            <p class="small text-muted-custom mb-0">Secured funds in active trade negotiations</p>
        </div>
    </div>

    <!-- Total Balance Card -->
    <div class="col-md-4">
        <div class="glass-card text-center py-4">
            <h5 class="text-muted-custom mb-3">Total Account Value</h5>
            <h2 class="fw-bold text-primary mb-2">{{ number_format(Auth::user()->wallet->total_balance, 2) }} USDT</h2>
            <p class="small text-muted-custom mb-0">Combined asset valuation</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12">
        <div class="glass-card">
            <h4 class="card-title-custom">Quick Actions</h4>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('marketplace') }}" class="btn btn-premium px-4">Browse Marketplace</a>
                <a href="{{ route('advertisements.my') }}" class="btn btn-outline-custom px-4">Create Advertisement</a>
                <a href="{{ route('orders.my') }}" class="btn btn-outline-custom px-4">Active Orders</a>
                <a href="{{ route('profile.two-factor') }}" class="btn btn-outline-custom px-4">Setup 2FA Security</a>
                <a href="{{ route('profile.kyc') }}" class="btn btn-outline-custom px-4">Submit KYC Document</a>
            </div>
        </div>
    </div>
</div>
@endsection
