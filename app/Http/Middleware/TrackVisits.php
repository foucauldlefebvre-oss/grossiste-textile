<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;

class TrackVisits
{
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'baiduspider', 'yandex',
        'googlebot', 'bingbot', 'facebookexternalhit', 'twitterbot',
        'linkedinbot', 'semrush', 'ahref', 'mj12bot', 'dotbot',
        'petalbot', 'bytespider', 'gptbot', 'claudebot',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Ne pas tracker les requetes admin, API, assets
        $path = $request->path();
        if (str_starts_with($path, 'admin')
            || str_starts_with($path, 'livewire')
            || str_starts_with($path, 'api')
            || str_starts_with($path, '_')
            || $request->isMethod('POST')
            || $request->ajax()
        ) {
            return $response;
        }

        try {
            $ua = strtolower($request->userAgent() ?? '');
            $isBot = false;
            foreach (self::BOT_PATTERNS as $pattern) {
                if (str_contains($ua, $pattern)) {
                    $isBot = true;
                    break;
                }
            }

            Visit::create([
                'path' => '/' . ltrim($path, '/'),
                'ip_hash' => hash('sha256', $request->ip() . date('Y-m')),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'referer' => substr($request->header('referer') ?? '', 0, 500) ?: null,
                'is_bot' => $isBot,
                'visited_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ne jamais bloquer la requete pour un probleme de tracking
        }

        return $response;
    }
}
