@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<h4 class="text-center mb-3">Email Verification</h4>
<p class="text-center text-muted-custom small mb-4">We've sent a 6-digit OTP code to your email. Please enter it below to verify your account.</p>

<form method="POST" action="{{ route('verification.otp') }}">
    @csrf

    <!-- OTP Code -->
    <div class="mb-4">
        <label for="otp" class="form-label">One-Time Password (OTP)</label>
        <input id="otp" type="text" class="form-control text-center fs-4" name="otp" required autofocus placeholder="000000" maxlength="6">
    </div>

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-premium">Verify Email</button>
    </div>
</form>

<div class="d-flex justify-content-between align-items-center mt-4">
    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Resend OTP</button>
    </form>
    
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none text-danger">Sign Out</button>
    </form>
</div>
@endsection
