@extends('layouts.dashboard')

@section('title', 'My Wallet')

@section('content')
<div class="row g-4">
    <!-- Left Column: Balance overview & Transaction History -->
    <div class="col-lg-8">
        <!-- Balance Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="glass-card text-center py-4">
                    <span class="text-muted small d-block mb-1">Available Balance</span>
                    <h2 class="fw-bold text-success mb-0">{{ number_format($user->wallet->available_balance, 8) }} <span class="fs-6 text-muted">USDT</span></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center py-4">
                    <span class="text-muted small d-block mb-1">Locked in Escrow</span>
                    <h2 class="fw-bold text-warning mb-0">{{ number_format($user->wallet->locked_balance, 8) }} <span class="fs-6 text-muted">USDT</span></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center py-4">
                    <span class="text-muted small d-block mb-1">Total Account Value</span>
                    <h2 class="fw-bold mb-0" style="color: var(--accent-orange);">{{ number_format($user->wallet->total_balance, 8) }} <span class="fs-6 text-muted">USDT</span></h2>
                </div>
            </div>
        </div>

        <!-- Deposit Result Notice -->
        @if(session('deposit_result'))
            @php $res = session('deposit_result'); @endphp
            <div class="glass-card border-warning mb-4" style="background-color: rgba(243, 156, 18, 0.05);">
                <h5 class="fw-bold text-warning mb-3">⚡ Deposit Invoice Generated</h5>
                <p class="mb-2">Please send exactly <strong class="text-success">{{ number_format($res['pay_amount'], 2) }} USDT</strong> to the following **USDT (TRC-20)** wallet address:</p>
                <div class="p-3 bg-dark rounded border border-secondary mb-3 d-flex justify-content-between align-items-center">
                    <code class="text-warning fs-5 fw-bold" style="word-break: break-all;">{{ $res['pay_address'] }}</code>
                </div>
                @if(isset($res['simulated']) && $res['simulated'])
                    <span class="badge bg-danger mb-0">Simulated Developer Mode: Balance will credit automatically upon webhook simulation.</span>
                @else
                    <p class="small text-muted mb-0">Once the transaction has been confirmed on the TRON network, your balance will credit automatically.</p>
                @endif
            </div>
        @endif

        <!-- Transaction History Card -->
        <div class="glass-card">
            <h4 class="card-title-custom mb-4" style="font-size: 1.25rem;">Transaction Ledger</h4>
            <div class="table-responsive">
                <table class="table table-custom align-middle border-0 mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Address / Details</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($transactions->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No transactions recorded.</td>
                            </tr>
                        @else
                            @foreach($transactions as $tx)
                                <tr>
                                    <td>
                                        <span class="badge {{ $tx->type === 'deposit' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}" style="background-color: rgba(14,203,129,0.1); border: 1px solid rgba(14,203,129,0.2);">
                                            {{ strtoupper($tx->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="{{ $tx->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                            {{ $tx->type === 'deposit' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} USDT
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="small text-muted" style="word-break: break-all;">
                                            {{ $tx->address ?: $tx->payment_id ?: 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge @if($tx->status === 'completed') bg-success @elseif($tx->status === 'pending') bg-warning text-dark @else bg-danger @endif">
                                            {{ strtoupper($tx->status) }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $tx->created_at->format('M d, Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <!-- Right Column: Deposit & Withdrawal Actions -->
    <div class="col-lg-4">
        <!-- Deposit Panel -->
        <div class="glass-card mb-4">
            <h4 class="card-title-custom mb-3" style="font-size: 1.15rem;">Deposit USDT</h4>
            <p class="small text-muted mb-3">Generate a secure deposit address to add funds instantly to your available balance via NOWPayments.</p>
            
            <form method="POST" action="{{ route('wallet.deposit') }}">
                @csrf
                <div class="mb-3">
                    <label for="deposit_amount" class="form-label small fw-bold">Amount to Deposit (USDT)</label>
                    <div class="input-group">
                        <input id="deposit_amount" type="number" step="1" name="amount" min="1" class="form-control" placeholder="Min. 1 USDT" required>
                        <span class="input-group-text border-secondary bg-dark text-muted">USDT</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-premium w-full">Generate Deposit Address</button>
            </form>
        </div>

        <!-- Withdrawal Panel -->
        <div class="glass-card">
            <h4 class="card-title-custom mb-3" style="font-size: 1.15rem;">Withdraw USDT</h4>
            <p class="small text-muted mb-3">Request a transfer of USDT from your available balance to any external TRC-20 wallet. Enforces a flat 2 USDT fee.</p>
            
            <form method="POST" action="{{ route('wallet.withdraw') }}">
                @csrf
                <div class="mb-3">
                    <label for="withdraw_amount" class="form-label small fw-bold">Amount (USDT)</label>
                    <div class="input-group">
                        <input id="withdraw_amount" type="number" step="0.01" name="amount" min="5" class="form-control" placeholder="Min. 5 USDT" required>
                        <span class="input-group-text border-secondary bg-dark text-muted">USDT</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="withdraw_address" class="form-label small fw-bold">USDT Destination Address (TRC-20)</label>
                    <input id="withdraw_address" type="text" name="address" class="form-control" placeholder="Starts with T..." required>
                    <div class="form-text text-muted small mt-1">Must be a valid TRON TRC-20 USDT address.</div>
                </div>
                <button type="submit" class="btn btn-outline-custom w-full">Request Withdrawal</button>
            </form>
        </div>
    </div>
</div>
@endsection
