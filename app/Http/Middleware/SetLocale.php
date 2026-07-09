<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Resolve Language Locale
        if (session()->has('locale')) {
            app()->setLocale(session()->get('locale'));
        } elseif ($user = $request->user()) {
            if ($user->language) {
                app()->setLocale($user->language->code);
                session()->put('locale', $user->language->code);
            }
        } else {
            // Check default setting or fallback
            $defaultLang = SettingsService::get('default_language', 'en');
            app()->setLocale($defaultLang);
            session()->put('locale', $defaultLang);
        }

        // 2. Resolve Active Country Context
        if (! session()->has('country_id')) {
            if ($user = $request->user()) {
                session()->put('country_id', $user->country_id);
            } else {
                $defaultCountry = SettingsService::get('default_country');
                if ($defaultCountry) {
                    session()->put('country_id', $defaultCountry);
                }
            }
        }

        return $next($request);
    }
}
