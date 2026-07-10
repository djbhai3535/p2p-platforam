@extends('layouts.dashboard')

@section('title', 'Place Order')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="glass-card">
            <h3 class="card-title-custom" style="letter-spacing: -0.5px;">Place P2P Order</h3>
            <p class="text-muted mb-4 small">Open a secure escrow trade. USDT will be locked in the escrow contract immediately upon placement.</p>

            <div class="mb-4 p-3 rounded" style="background-color: rgba(243, 156, 18, 0.04); border: 1px solid rgba(243, 156, 18, 0.12); color: var(--text-color);">
                <div class="row g-2">
                    <div class="col-6"><strong>Merchant:</strong> {{ $advertisement->user->name }}</div>
                    <div class="col-6"><strong>Trade Type:</strong> <span class="fw-bold" style="color: var(--accent-orange);">{{ $advertisement->type === 'buy' ? 'SELL USDT (You sell)' : 'BUY USDT (You buy)' }}</span></div>
                    <div class="col-6"><strong>Exchange Rate:</strong> <span class="text-success fw-bold">{{ number_format($advertisement->rate, 2) }} {{ $advertisement->country->currency_code }}/USDT</span></div>
                    <div class="col-6"><strong>Limits:</strong> {{ number_format($advertisement->min_limit, 2) }} - {{ number_format($advertisement->max_limit, 2) }} {{ $advertisement->country->currency_code }}</div>
                </div>
            </div>

            @if($advertisement->terms)
                <div class="mb-4">
                    <h6 class="fw-bold text-warning" style="font-size: 0.9rem;">Terms and Conditions:</h6>
                    <div class="p-3 rounded border border-secondary text-muted small" style="background-color: rgba(255,255,255,0.015); line-height: 1.5;">
                        {!! nl2br(e($advertisement->terms)) !!}
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('orders.store', $advertisement->id) }}">
                @csrf

                <div class="row g-3">
                    <!-- Amount USDT -->
                    <div class="col-md-6">
                        <label for="amount_usdt" class="form-label fw-bold small text-muted">I want to trade (USDT)</label>
                        <input id="amount_usdt" type="number" step="0.00000001" class="form-control" name="amount_usdt" required placeholder="e.g. 100" value="{{ old('amount_usdt') }}">
                    </div>

                    <!-- Calculated Fiat Value -->
                    <div class="col-md-6">
                        <label for="amount_fiat" class="form-label fw-bold small text-muted">I will receive/pay (Fiat)</label>
                        <div class="input-group">
                            <input id="amount_fiat" type="text" class="form-control" disabled placeholder="0.00" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <span class="input-group-text border-secondary bg-dark text-muted" style="border-top-left-radius: 0; border-bottom-left-radius: 0; border-left: 0;">{{ $advertisement->country->currency_code }}</span>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <a href="{{ route('marketplace') }}" class="btn btn-outline-custom px-4 me-2">Cancel</a>
                        <button type="submit" class="btn btn-premium px-5">Place Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const amountUsdtInput = document.getElementById('amount_usdt');
        const amountFiatInput = document.getElementById('amount_fiat');
        const rate = parseFloat('{{ $advertisement->rate }}');

        amountUsdtInput.addEventListener('input', function () {
            const usdt = parseFloat(amountUsdtInput.value);
            if (!isNaN(usdt) && usdt > 0) {
                const fiat = (usdt * rate).toFixed(2);
                amountFiatInput.value = fiat;
            } else {
                amountFiatInput.value = '0.00';
            }
        });
    });
</script>
@endsection
