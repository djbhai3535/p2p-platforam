<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm(): \Illuminate\Contracts\View\View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link to user.
     */
    public function sendResetLinkEmail(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            $user->notify(new ResetPasswordNotification($token, $request->email));
        }

        // Return generic success message to prevent user enumeration
        return back()->with('status', 'We have emailed your password reset link if the account exists.');
    }

    /**
     * Show reset password form.
     */
    public function showResetForm(Request $request, $token): \Illuminate\Contracts\View\View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset user password.
     */
    public function reset(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if ($record && Hash::check($request->token, $record->token)) {
            // Check expiry (e.g. 60 minutes)
            if (now()->subMinutes(60)->gt($record->created_at)) {
                return back()->withErrors(['email' => 'This password reset link has expired.']);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect()->route('login')->with('status', 'Your password has been reset successfully. Please log in.');
        }

        return back()->withErrors(['email' => 'Invalid password reset token or email address.']);
    }
}
