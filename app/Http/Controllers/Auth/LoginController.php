<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function show(): View
    {
        return view('auth.login');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // reCAPTCHA verification
        if (SettingsService::get('recaptcha_enabled') === 'true') {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            if (! $recaptchaResponse) {
                return back()->withErrors(['g-recaptcha-response' => 'Please complete the reCAPTCHA challenge.'])->withInput();
            }

            $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => SettingsService::get('recaptcha_secret_key'),
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);

            if (! $verify->json('success')) {
                return back()->withErrors(['g-recaptcha-response' => 'reCAPTCHA verification failed.'])->withInput();
            }
        }

        // Attempt authentication (without logging in yet if 2FA is enabled)
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Auth::validate($credentials)) {
            // Check if user is active
            if (! $user->is_active) {
                return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
            }

            // Check if Two-Factor Authentication is enabled and confirmed
            if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
                // Store user id in session temporarily
                Session::put('login.id', $user->id);
                Session::put('login.remember', $request->boolean('remember'));

                return redirect()->route('login.two-factor');
            }

            // Normal login
            Auth::login($user, $request->boolean('remember'));

            // Trigger login event to log device history & audits
            event(new Login('web', $user, $request->boolean('remember')));

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the 2FA input challenge page.
     */
    public function showTwoFactor(): RedirectResponse|View
    {
        if (! Session::has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    /**
     * Verify the 2FA code during login.
     */
    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        if (! Session::has('login.id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $userId = Session::get('login.id');
        $user = User::findOrFail($userId);

        // Verify OTP using pragmarx/google2fa
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            Auth::login($user, Session::get('login.remember', false));

            // Trigger login event
            event(new Login('web', $user, Session::get('login.remember', false)));

            Session::forget(['login.id', 'login.remember']);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['code' => 'Invalid two-factor authentication code.']);
    }

    /**
     * Log user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
