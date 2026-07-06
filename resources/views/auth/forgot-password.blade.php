@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<h4 class="text-center mb-3">Reset Password</h4>
<p class="text-center text-muted-custom small mb-4">Enter your email address and we'll send you a password reset link.</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <!-- Email Address -->
    <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
    </div>

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-premium">Send Reset Link</button>
    </div>
</form>

<div class="text-center mt-4">
    <p class="text-muted-custom small mb-0"><a href="{{ route('login') }}">Back to Sign In</a></p>
</div>
@endsection
