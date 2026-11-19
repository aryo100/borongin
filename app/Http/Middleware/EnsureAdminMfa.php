<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user?->role === 'admin', 403);

        if (! $user->two_factor_secret || ! $user->two_factor_confirmed_at
            || $request->session()->get('admin_mfa_user') !== $user->getKey()) {
            return redirect()->route('security');
        }

        return $next($request);
    }
}
