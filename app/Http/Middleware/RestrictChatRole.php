<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictChatRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->role === 'chat') {
            $path = $request->path();

            // Autoriser uniquement : live-chat, login, logout, livewire
            $allowed = [
                'admin/live-chat',
                'admin/login',
                'admin/logout',
                'livewire',
            ];

            $isAllowed = false;
            foreach ($allowed as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $isAllowed = true;
                    break;
                }
            }

            // Rediriger vers le chat si page non autorisée
            if (! $isAllowed && str_starts_with($path, 'admin')) {
                return redirect('/admin/live-chat');
            }
        }

        return $next($request);
    }
}
