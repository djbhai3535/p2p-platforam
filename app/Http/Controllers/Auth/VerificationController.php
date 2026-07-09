<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\EmailOtpNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VerificationController extends Controller
{
    /**
     * Show the OTP email verification notice.
     */
    public function show(Request $request): RedirectResponse|View
    {
        return $request->user()->email_verified_at
            ? redirect()->route('dashboard')
            : view('auth.otp');
    }

    /**
     * Verify the email OTP code.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        $cachedOtp = Cache::get("otp.{$user->id}");

        if ($cachedOtp && $cachedOtp === $request->otp) {
            $user->email_verified_at = now();
            $user->save();

            Cache::forget("otp.{$user->id}");

            return redirect()->route('dashboard')->with('status', 'Email verified successfully.');
        }

        return back()->withErrors(['otp' => 'The entered OTP code is invalid or has expired.']);
    }

    /**
     * Resend the verification OTP.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        // Rate limit OTP resend (e.g. once every 60 seconds)
        $lockKey = "otp.resend.lock.{$user->id}";
        if (Cache::has($lockKey)) {
            return back()->with('status', 'Please wait before requesting a new OTP.');
        }

        $otp = (string) rand(100000, 999999);
        Cache::put("otp.{$user->id}", $otp, now()->addMinutes(15));
        Cache::put($lockKey, true, now()->addSeconds(60));

        $user->notify(new EmailOtpNotification($otp));

        return back()->with('status', 'A new verification OTP code has been sent to your email.');
    }
}
