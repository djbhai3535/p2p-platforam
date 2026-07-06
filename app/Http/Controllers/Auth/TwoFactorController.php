<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    /**
     * Show 2FA settings page or details.
     */
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $request->user();
        $qrCodeSvg = null;
        $secret = null;

        if (!$user->two_factor_confirmed_at) {
            $google2fa = app('pragmarx.google2fa');
            
            // If user doesn't have a secret key yet, generate one
            if (!$user->two_factor_secret) {
                $user->two_factor_secret = $google2fa->generateSecretKey();
                $user->save();
            }

            $secret = $user->two_factor_secret;
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name', 'TradeFlow P2P'),
                $user->email,
                $secret
            );

            // Generate QR Code SVG natively
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrCodeUrl);
        }

        return view('profile.two-factor', compact('user', 'qrCodeSvg', 'secret'));
    }

    /**
     * Enable and confirm 2FA for the user.
     */
    public function enable(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:6'],
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The provided password does not match our records.']);
        }

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $user->two_factor_confirmed_at = now();
            $user->save();

            // Create Audit Log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => '2FA_ENABLE',
                'description' => 'Enabled Two-Factor Authentication (2FA) successfully.',
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);

            return redirect()->route('profile.two-factor')->with('status', 'Two-Factor Authentication has been enabled successfully.');
        }

        return back()->withErrors(['code' => 'Invalid verification code. Please scan the QR code again.']);
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The provided password does not match our records.']);
        }

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $user->two_factor_secret = null;
            $user->two_factor_confirmed_at = null;
            $user->two_factor_recovery_codes = null;
            $user->save();

            // Create Audit Log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => '2FA_DISABLE',
                'description' => 'Disabled Two-Factor Authentication (2FA).',
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);

            return redirect()->route('profile.two-factor')->with('status', 'Two-Factor Authentication has been disabled.');
        }

        return back()->withErrors(['code' => 'Invalid authentication code.']);
    }
}
