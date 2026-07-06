<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function show(): \Illuminate\Contracts\View\View
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'country_id' => ['required', 'exists:countries,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // reCAPTCHA verification
        if (SettingsService::get('recaptcha_enabled') === 'true') {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            if (!$recaptchaResponse) {
                return back()->withErrors(['g-recaptcha-response' => 'Please complete the reCAPTCHA challenge.'])->withInput();
            }

            $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => SettingsService::get('recaptcha_secret_key'),
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);

            if (!$verify->json('success')) {
                return back()->withErrors(['g-recaptcha-response' => 'reCAPTCHA verification failed.'])->withInput();
            }
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country_id' => $request->country_id,
            'language_id' => 1, // Default English
            'is_admin' => false,
            'is_active' => true,
        ]);

        // Generate and send Email OTP
        $otp = (string) rand(100000, 999999);
        Cache::put("otp.{$user->id}", $otp, now()->addMinutes(15));
        
        $user->notify(new EmailOtpNotification($otp));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
