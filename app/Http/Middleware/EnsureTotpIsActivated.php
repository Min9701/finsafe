<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTotpIsActivated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->totp_secret === null || $user->totp_verified_at === null) {
            return redirect()->route('totp.activation.show');
        }

        if ($request->session()->get('auth.mfa_verified_user_id') !== $user->id) {
            return redirect()->route('totp.challenge.show');
        }

        return $next($request);
    }
}
