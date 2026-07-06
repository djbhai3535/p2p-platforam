@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<h4 class="text-center mb-4">Set New Password</h4>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $token }}">

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control" name="email" value="{{ $email ?? old('email') }}" required autofocus readonly>
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <input id="password" type="password" class="form-control" name="password" required placeholder="••••••••">
    </div>

    <!-- Confirm Password -->
    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required placeholder="••••••••">
    </div>

    <!-- Submit Button -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-premium">Update Password</button>
    </div>
</form>
@endsection
