<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireKyc
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isKycVerified()) {
            return $request->expectsJson()
                ? response()->json(['error' => 'KYC verification is required to perform P2P trades.'], 403)
                : redirect()->route('profile.kyc')->withErrors(['message' => 'You must complete KYC verification before placing trades or creating advertisements.']);
        }

        return $next($request);
    }
}
