<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets an admin API request through only when its bearer token belongs to an
 * active admin. Anything else gets a 401, which signs the React admin out.
 *
 * The guard then becomes the default for the rest of the request, so
 * $request->user() and rules such as current_password resolve the admin.
 */
class AuthenticateAdmin
{
    public const GUARD = 'admin';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard(self::GUARD)->user();

        if (! $admin instanceof Admin) {
            throw new AuthenticationException(guards: [self::GUARD]);
        }

        if (! $admin->is_active) {
            // Switched off after signing in: end every session they have.
            $admin->tokens()->delete();

            throw new AuthenticationException('Your account has been deactivated, so you were signed out.', [self::GUARD]);
        }

        Auth::shouldUse(self::GUARD);

        return $next($request);
    }
}
