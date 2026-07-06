<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\KycController;
use Illuminate\Support\Facades\Route;

// Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Login
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // 2FA Challenge during login
    Route::get('/login/two-factor', [LoginController::class, 'showTwoFactor'])->name('login.two-factor');
    Route::post('/login/two-factor', [LoginController::class, 'verifyTwoFactor'])->name('login.two-factor.verify');

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email OTP Verification Routes
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::post('/email/verify/otp', [VerificationController::class, 'verifyOtp'])->name('verification.otp');
    Route::post('/email/verify/resend', [VerificationController::class, 'resend'])->name('verification.resend');

    // Routes requiring Email Verification
    Route::middleware('email.verified')->group(function () {
        // Dashboard Placeholder
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        // Security / Two-Factor Settings
        Route::get('/profile/two-factor', [TwoFactorController::class, 'index'])->name('profile.two-factor');
        Route::post('/profile/two-factor/enable', [TwoFactorController::class, 'enable'])->name('profile.two-factor.enable');
        Route::post('/profile/two-factor/disable', [TwoFactorController::class, 'disable'])->name('profile.two-factor.disable');

        // KYC Verification
        Route::get('/profile/kyc', [KycController::class, 'index'])->name('profile.kyc');
        Route::post('/profile/kyc', [KycController::class, 'submit'])->name('profile.kyc.submit');

        // P2P Marketplace / Other Pages placeholders
        Route::get('/marketplace', function () {
            return "P2P Marketplace Page Placeholder";
        })->name('marketplace');

        Route::get('/my-ads', function () {
            return "My Ads Page Placeholder";
        })->name('advertisements.my');

        Route::get('/my-trades', function () {
            return "My Trades Page Placeholder";
        })->name('orders.my');

        Route::get('/wallet', function () {
            return "Wallet Page Placeholder";
        })->name('wallet');
    });
});

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});
