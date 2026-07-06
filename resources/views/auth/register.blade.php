@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<h4 class="text-center mb-4">Create Account</h4>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <!-- Name -->
    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe">
    </div>

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="name@example.com">
    </div>

    <!-- Country -->
    <div class="mb-3">
        <label for="country_id" class="form-label">Country / Region</label>
        <select id="country_id" name="country_id" class="form-select form-control" required style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e&quot;);">
            <option value="" disabled selected>Select your country</option>
            @foreach(App\Models\Country::where('is_active', true)->get() as $country)
                <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                    {{ $country->name }} ({{ $country->currency_code }})
                </option>
            @endforeach
        </select>
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" class="form-control" name="password" required placeholder="••••••••">
    </div>

    <!-- Confirm Password -->
    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required placeholder="••••••••">
    </div>

    <!-- reCAPTCHA Placeholder (If enabled) -->
    @if(App\Services\SettingsService::get('recaptcha_enabled') === 'true')
        <div class="mb-4 d-flex justify-content-center">
            <div class="g-recaptcha" data-sitekey="{{ App\Services\SettingsService::get('recaptcha_site_key') }}"></div>
        </div>
    @endif

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-premium">Register</button>
    </div>
</form>

<div class="text-center mt-4">
    <p class="text-muted-custom small mb-0">Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
</div>
@endsection

@section('scripts')
@if(App\Services\SettingsService::get('recaptcha_enabled') === 'true')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@endsection
