<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireEmailVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->email_verified_at) {
            // Check if dynamic setting requires verification
            $verificationRequired = \App\Services\SettingsService::get('email_verification_required', 'false') === 'true';

            if ($verificationRequired) {
                return $request->expectsJson()
                    ? response()->json(['message' => 'Your email address is not verified.'], 403)
                    : redirect()->route('verification.notice');
            }
        }

        return $next($request);
    }
}
