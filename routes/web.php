<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\P2PController;
use App\Http\Controllers\UserPaymentMethodController;
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
        Route::get('/profile/kyc/document/{kycVerification}/{type}', [KycController::class, 'viewDocument'])->name('profile.kyc.document');

        // Linked Payment Methods
        Route::get('/profile/payment-methods', [UserPaymentMethodController::class, 'index'])->name('profile.payment-methods');
        Route::post('/profile/payment-methods', [UserPaymentMethodController::class, 'store'])->name('profile.payment-methods.store');
        Route::delete('/profile/payment-methods/{paymentMethod}', [UserPaymentMethodController::class, 'destroy'])->name('profile.payment-methods.destroy');

        // P2P Marketplace General Views
        Route::get('/marketplace', [P2PController::class, 'marketplace'])->name('marketplace');
        Route::get('/my-ads', [P2PController::class, 'myAdvertisements'])->name('advertisements.my');

        Route::get('/my-trades', [OrderController::class, 'myTrades'])->name('orders.my');

        // KYC Gated P2P Actions
        Route::middleware('kyc.verified')->group(function () {
            Route::get('/advertisements/create', [P2PController::class, 'createAdvertisement'])->name('advertisements.create');
            Route::post('/advertisements', [P2PController::class, 'storeAdvertisement'])->name('advertisements.store');
            Route::post('/advertisements/{advertisement}/toggle', [P2PController::class, 'toggleAdvertisement'])->name('advertisements.toggle');

            // Order trading room & action flows
            Route::get('/orders/create/{advertisement}', [OrderController::class, 'create'])->name('orders.create');
            Route::post('/orders/store/{advertisement}', [OrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/paid', [OrderController::class, 'markAsPaid'])->name('orders.paid');
            Route::post('/orders/{order}/release', [OrderController::class, 'release'])->name('orders.release');
            Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::post('/orders/{order}/dispute', [OrderController::class, 'openDispute'])->name('orders.dispute');
        });

        Route::get('/wallet', function () {
            return "Wallet Page Placeholder";
        })->name('wallet');
    });
});

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});
