<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternalPanelAccess
{
    /**
     * Allow only internal panel roles for now.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role_system, ['admin', 'superadmin'], true)) {
            abort(403, 'You are not allowed to access this panel.');
        }

        return $next($request);
    }
}
