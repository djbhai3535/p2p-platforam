@extends('layouts.dashboard')

@section('title', 'Post P2P Advertisement')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="glass-card">
            <h3 class="card-title-custom">Post P2P Advertisement</h3>
            <p class="text-muted-custom small mb-4">Set your own trade parameters, price margin, and payment methods to trade USDT with other users.</p>

            <form method="POST" action="{{ route('advertisements.store') }}">
                @csrf

                <div class="row g-3">
                    <!-- Ad Type -->
                    <div class="col-md-6">
                        <label for="type" class="form-label">Ad Type</label>
                        <select id="type" name="type" class="form-select form-control" required style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e&quot;);">
                            <option value="buy" {{ old('type') === 'buy' ? 'selected' : '' }}>Buy USDT (I want to buy)</option>
                            <option value="sell" {{ old('type') === 'sell' ? 'selected' : '' }}>Sell USDT (I want to sell)</option>
                        </select>
                        <div class="form-text text-muted-custom small">Selling requires having USDT in your available wallet balance.</div>
                    </div>

                    <!-- Target Country / Currency -->
                    <div class="col-md-6">
                        <label for="country_id" class="form-label">Target Currency</label>
                        <select id="country_id" name="country_id" class="form-select form-control" required style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e&quot;);">
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('country_id') == $c->id || (Auth::user()->country_id == $c->id) ? 'selected' : '' }} data-currency="{{ $c->currency_code }}">
                                    {{ $c->currency_code }} ({{ $c->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pricing Type -->
                    <div class="col-md-6">
                        <label for="price_type" class="form-label">Pricing Type</label>
                        <select id="price_type" name="price_type" class="form-select form-control" required style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e&quot;);">
                            <option value="fixed">Fixed Price</option>
                            <option value="margin">Floating Margin</option>
                        </select>
                    </div>

                    <!-- Exchange Rate / Price -->
                    <div class="col-md-6">
                        <label for="rate" class="form-label">Exchange Rate / Price (per USDT)</label>
                        <div class="input-group">
                            <input id="rate" type="number" step="0.01" class="form-control" name="rate" value="{{ old('rate', '278.50') }}" required>
                            <span class="input-group-text bg-secondary text-light border-0 currency-label">PKR</span>
                        </div>
                    </div>

                    <hr class="my-4 border-secondary">

                    <!-- Total Amount USDT -->
                    <div class="col-md-4">
                        <label for="amount" class="form-label">Total Amount (USDT)</label>
                        <input id="amount" type="number" step="0.00000001" class="form-control" name="amount" value="{{ old('amount') }}" required placeholder="e.g. 500">
                    </div>

                    <!-- Min Limit (Fiat) -->
                    <div class="col-md-4">
                        <label for="min_limit" class="form-label">Min Limit</label>
                        <div class="input-group">
                            <input id="min_limit" type="number" class="form-control" name="min_limit" value="{{ old('min_limit') }}" required placeholder="e.g. 1000">
                            <span class="input-group-text bg-secondary text-light border-0 currency-label">PKR</span>
                        </div>
                    </div>

                    <!-- Max Limit (Fiat) -->
                    <div class="col-md-4">
                        <label for="max_limit" class="form-label">Max Limit</label>
                        <div class="input-group">
                            <input id="max_limit" type="number" class="form-control" name="max_limit" value="{{ old('max_limit') }}" required placeholder="e.g. 100000">
                            <span class="input-group-text bg-secondary text-light border-0 currency-label">PKR</span>
                        </div>
                    </div>

                    <hr class="my-4 border-secondary">

                    <!-- Linked Payment Methods Checkbox List -->
                    <div class="col-12">
                        <label class="form-label d-block fw-bold">Select Payment Methods</label>
                        <p class="text-muted-custom small mb-3">You must choose at least one linked payment method for buyers/sellers to settle fiat transfers.</p>
                        
                        @if($linkedMethods->isEmpty())
                            <div class="p-3 rounded border border-warning text-center" style="background-color: rgba(245, 158, 11, 0.05);">
                                <p class="text-warning mb-2 small">You have not linked any payment method profiles yet.</p>
                                <a href="{{ route('profile.payment-methods') }}" class="btn btn-sm btn-outline-warning">Link Payment Account Now</a>
                            </div>
                        @else
                            <div class="row g-2">
                                @foreach($linkedMethods as $lm)
                                    <div class="col-md-6">
                                        <div class="form-check p-3 rounded border border-secondary" style="background-color: rgba(255,255,255,0.01);">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="payment_methods[]" value="{{ $lm->paymentMethod->id }}" id="pm-{{ $lm->id }}">
                                            <label class="form-check-label" for="pm-{{ $lm->id }}">
                                                <strong>{{ $lm->paymentMethod->name }}</strong> ({{ $lm->account_title }})
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="col-12 mt-4">
                        <label for="terms" class="form-label">Terms and Payment Instructions</label>
                        <textarea id="terms" class="form-control" name="terms" rows="4" placeholder="e.g. State your terms: 'Only third-party payments blocked. EasyPaisa transfer fee is paid by buyer.'">{{ old('terms') }}</textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-premium px-5 py-2">Publish Advertisement</button>
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
        const countrySelect = document.getElementById('country_id');
        const currencyLabels = document.querySelectorAll('.currency-label');

        function updateCurrencyLabels() {
            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            const currency = selectedOption.getAttribute('data-currency');
            currencyLabels.forEach(label => {
                label.innerText = currency;
            });
        }

        countrySelect.addEventListener('change', updateCurrencyLabels);
        updateCurrencyLabels(); // Run once on load
    });
</script>
@endsection
