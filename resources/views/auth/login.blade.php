@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<h4 class="text-center mb-4">Welcome Back</h4>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
    </div>

    <!-- Password -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label for="password" class="form-label mb-0">Password</label>
            <a href="{{ route('password.request') }}" class="small">Forgot Password?</a>
        </div>
        <input id="password" type="password" class="form-control" name="password" required placeholder="••••••••">
    </div>

    <!-- Remember Me -->
    <div class="mb-4 form-check">
        <input class="form-check-input" type="checkbox" name="remember" id="remember">
        <label class="form-check-label text-muted-custom small" for="remember">
            Remember this device
        </label>
    </div>

    <!-- reCAPTCHA Placeholder (If enabled) -->
    @if(App\Services\SettingsService::get('recaptcha_enabled') === 'true')
        <div class="mb-4 d-flex justify-content-center">
            <div class="g-recaptcha" data-sitekey="{{ App\Services\SettingsService::get('recaptcha_site_key') }}"></div>
        </div>
    @endif

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-premium">Sign In</button>
    </div>
</form>

<div class="text-center mt-4">
    <p class="text-muted-custom small mb-0">Don't have an account? <a href="{{ route('register') }}">Sign Up</a></p>
</div>
@endsection

@section('scripts')
@if(App\Services\SettingsService::get('recaptcha_enabled') === 'true')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@endsection
