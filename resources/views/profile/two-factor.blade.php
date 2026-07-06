@extends('layouts.dashboard')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="glass-card">
            <h3 class="card-title-custom">Two-Factor Authentication (2FA)</h3>
            <p class="text-muted mb-4">Protect your account with an extra layer of security. Once enabled, you must provide a 6-digit verification code from your authenticator app during login.</p>

            @if($user->two_factor_confirmed_at)
                <!-- 2FA Active -->
                <div class="p-4 mb-4 rounded-3 bg-opacity-10 border border-success" style="background-color: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-success fs-2">✓</div>
                        <div>
                            <h5 class="mb-1 text-success fw-bold">2FA is Enabled</h5>
                            <p class="mb-0 text-muted-custom small">Your account is secured with Google Authenticator.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h5 class="fw-bold mb-3">Disable 2FA</h5>
                    <p class="text-muted-custom small mb-4">To disable 2FA, enter your current password and a 6-digit authentication code from your app.</p>

                    <form method="POST" action="{{ route('profile.two-factor.disable') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" class="form-control" name="password" required placeholder="Enter password">
                            </div>
                            <div class="col-md-6">
                                <label for="code" class="form-label">Authenticator Code</label>
                                <input id="code" type="text" class="form-control text-center" name="code" required placeholder="000 000" maxlength="6">
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-danger px-4">Disable 2FA</button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <!-- 2FA Setup -->
                <div class="p-4 mb-4 rounded-3 border border-warning" style="background-color: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-warning fs-2">⚠</div>
                        <div>
                            <h5 class="mb-1 text-warning fw-bold">2FA is Not Configured</h5>
                            <p class="mb-0 text-muted-custom small">Please complete the setup below to secure your transactions and withdrawals.</p>
                        </div>
                    </div>
                </div>

                <div class="row g-5 align-items-center mt-2">
                    <div class="col-md-5 text-center">
                        <div class="p-3 bg-white rounded-3 inline-block" style="display: inline-block;">
                            {!! $qrCodeSvg !!}
                        </div>
                        <p class="text-dark small mt-2 mb-0 fw-bold">Scan with Google Authenticator</p>
                    </div>
                    <div class="col-md-7">
                        <h5 class="fw-bold mb-3">Setup Instructions</h5>
                        <ol class="text-muted-custom small mb-4 ps-3">
                            <li class="mb-2">Download Google Authenticator or Microsoft Authenticator from App Store or Play Store.</li>
                            <li class="mb-2">Scan the QR code on the left, or manually enter the key: <code class="text-light bg-dark px-2 py-1 rounded">{{ $secret }}</code></li>
                            <li class="mb-2">Enter your login password and the 6-digit code below to confirm and enable 2FA.</li>
                        </ol>

                        <form method="POST" action="{{ route('profile.two-factor.enable') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input id="password" type="password" class="form-control" name="password" required placeholder="Enter password">
                                </div>
                                <div class="col-md-6">
                                    <label for="code" class="form-label">Authenticator Code</label>
                                    <input id="code" type="text" class="form-control text-center" name="code" required placeholder="000000" maxlength="6">
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-premium px-4">Enable 2FA</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
