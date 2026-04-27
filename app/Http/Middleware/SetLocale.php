<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['fr', 'en'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('locale')
            ?? $request->getPreferredLanguage(self::SUPPORTED_LOCALES)
            ?? config('app.locale');

        if (in_array($locale, self::SUPPORTED_LOCALES)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
